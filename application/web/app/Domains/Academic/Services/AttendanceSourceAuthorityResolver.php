<?php

namespace App\Domains\Academic\Services;

use App\Domains\Academic\Models\AcademicClass;
use App\Domains\Academic\Models\AttendanceSourceCertification;
use App\Domains\Academic\Models\MonthlyAttendanceSummary;
use App\Domains\Academic\Semantics\Enums\AttendanceAuthorityStatus;
use App\Domains\Academic\Semantics\Enums\AttendanceSourceType;
use Illuminate\Support\Carbon;

class AttendanceSourceAuthorityResolver
{
    public function __construct(
        private readonly CanonicalAttendanceSemanticService $liveMetrics,
    ) {}

    public function resolve(string $period, string $scopeType = 'INSTITUTION', string $scopeKey = 'INSTITUTION', string $metric = 'ATTENDANCE'): array
    {
        $legacy = $this->legacyCandidate($period, $scopeType, $scopeKey, $metric);
        $live = $this->liveCandidate($period, $scopeType, $scopeKey, $metric);
        $candidates = collect([$live, $legacy])->filter(fn (?array $candidate): bool => $candidate !== null)->values();
        $certified = $candidates->filter(fn (array $candidate): bool => $candidate['certification_status'] === 'CERTIFIED');

        $authority = AttendanceAuthorityStatus::DATA_NOT_CERTIFIED;
        $selected = null;
        if ($candidates->isEmpty()) {
            $authority = AttendanceAuthorityStatus::DATA_INCOMPLETE;
        } elseif ($certified->isNotEmpty()) {
            $usable = $certified->filter(fn (array $candidate): bool => $candidate['completeness_rate'] === 100.0 && ! $candidate['semantic_decision_required']);
            if ($usable->count() === 1) {
                $authority = AttendanceAuthorityStatus::AUTHORITATIVE;
                $selected = $usable->first();
            } elseif ($usable->count() > 1) {
                $authority = $this->reconciles($usable) ? AttendanceAuthorityStatus::AUTHORITATIVE : AttendanceAuthorityStatus::SOURCE_CONFLICT;
                $selected = $authority === AttendanceAuthorityStatus::AUTHORITATIVE ? $this->selectByConfiguredPrecedence($usable) : null;
            } else {
                $authority = AttendanceAuthorityStatus::DATA_INCOMPLETE;
            }
        } elseif ($live !== null && $live['completeness_rate'] < 100.0) {
            $authority = AttendanceAuthorityStatus::DATA_INCOMPLETE;
        }

        $provenance = [
            'source_type' => $selected['source_type'] ?? null,
            'authority_status' => $authority->value,
            'certification_status' => $selected['certification_status'] ?? 'NOT_CERTIFIED',
            'period' => $period,
            'scope' => ['type' => $scopeType, 'key' => $scopeKey],
            'completeness_rate' => $selected['completeness_rate'] ?? null,
            'validation_state' => $selected['validation_state'] ?? null,
            'data_updated_at' => $selected['data_updated_at'] ?? null,
            'metric_version' => 'attendance-semantic-v1',
            'record_count' => $selected['record_count'] ?? null,
            'source_reference' => $selected['source_reference'] ?? null,
            'candidate_sources' => $candidates->map(fn (array $candidate): array => [
                'source_type' => $candidate['source_type'],
                'certification_status' => $candidate['certification_status'],
                'completeness_rate' => $candidate['completeness_rate'],
                'record_count' => $candidate['record_count'],
                'source_reference' => $candidate['source_reference'],
            ])->all(),
        ];

        return [
            'authority_status' => $authority->value,
            'data_status' => $selected === null ? $authority->value : ($selected['data_status'] ?? 'COMPLETE'),
            'official_attendance_rate' => $authority === AttendanceAuthorityStatus::AUTHORITATIVE ? $selected['official_attendance_rate'] : null,
            'metrics' => $selected['metrics'] ?? null,
            'provenance' => $provenance,
        ];
    }

    private function legacyCandidate(string $period, string $scopeType, string $scopeKey, string $metric): ?array
    {
        $query = MonthlyAttendanceSummary::query()->with('importBatch')->where('period', $period);
        if ($scopeType === 'CLASS') {
            $query->where(function ($query) use ($scopeKey): void {
                $query->where('class_id', $scopeKey)->orWhereHas('academicClass', fn ($class) => $class->where('class_code', $scopeKey));
            });
        }
        $rows = $query->get();
        if ($rows->isEmpty()) {
            return null;
        }
        $eligible = (int) $rows->sum('eligible');
        $resolved = (int) $rows->sum(fn (MonthlyAttendanceSummary $row): int => $row->present + $row->permission + $row->sick + $row->absent);
        $metrics = ['eligible_opportunities' => $eligible, 'resolved_opportunities' => $resolved, 'missing_opportunities' => max(0, $eligible - $resolved), 'present' => (int) $rows->sum('present'), 'permission' => (int) $rows->sum('permission'), 'sick' => (int) $rows->sum('sick'), 'absent' => (int) $rows->sum('absent')];
        $certified = AttendanceSourceCertification::query()->where(['source_type' => AttendanceSourceType::LEGACY_MONTHLY_SNAPSHOT->value, 'period' => $period, 'scope_type' => $scopeType, 'scope_key' => $scopeKey, 'metric' => $metric, 'certification_status' => 'CERTIFIED'])->exists();

        return $this->candidate(AttendanceSourceType::LEGACY_MONTHLY_SNAPSHOT->value, $certified, $metrics, $rows->every(fn ($row): bool => $row->status === 'PUBLISHED'), false, $rows->pluck('importBatch.batch_code')->filter()->unique()->values()->implode(','), $rows->max('updated_at'));
    }

    private function liveCandidate(string $period, string $scopeType, string $scopeKey, string $metric): ?array
    {
        [$from, $to] = [Carbon::createFromFormat('!Y-m', $period)->startOfMonth(), Carbon::createFromFormat('!Y-m', $period)->endOfMonth()];
        $classes = $scopeType === 'CLASS'
            ? AcademicClass::query()->where(fn ($query) => $query->whereKey($scopeKey)->orWhere('class_code', $scopeKey))->get()
            : AcademicClass::query()->where('status', 'ACTIVE')->whereHas('academicYear', fn ($query) => $query->where('year_code', 'not like', '%-PILOT'))->get();
        if ($classes->isEmpty()) {
            return null;
        }
        $metrics = ['eligible_opportunities' => 0, 'resolved_opportunities' => 0, 'missing_opportunities' => 0, 'present' => 0, 'late' => 0, 'permission' => 0, 'sick' => 0, 'absent' => 0, 'excused' => 0];
        $semanticDecisionRequired = false;
        foreach ($classes as $class) {
            $result = $this->liveMetrics->forClassPeriod($class, $from, $to);
            $metrics['eligible_opportunities'] += $result['eligible_opportunities'];
            $metrics['resolved_opportunities'] += $result['resolved_opportunities'];
            $metrics['missing_opportunities'] += $result['missing_opportunities'];
            $metrics['present'] += $result['counts']['PRESENT'] ?? 0;
            $metrics['permission'] += ($result['counts']['PERMISSION'] ?? $result['counts']['IZIN'] ?? 0);
            $metrics['sick'] += $result['counts']['SICK'] ?? 0;
            $metrics['absent'] += $result['counts']['ABSENT'] ?? 0;
            $metrics['late'] += $result['counts']['LATE'] ?? 0;
            $metrics['excused'] += $result['counts']['EXCUSED'] ?? 0;
            $semanticDecisionRequired = $semanticDecisionRequired || ($result['semantic_decision_required'] ?? false);
        }
        $certified = AttendanceSourceCertification::query()->where(['source_type' => AttendanceSourceType::LIVE_TRANSACTIONAL->value, 'period' => $period, 'scope_type' => $scopeType, 'scope_key' => $scopeKey, 'metric' => $metric, 'certification_status' => 'CERTIFIED'])->exists();
        $dataUpdatedAt = \DB::table('student_attendance as a')->join('session_student_participants as p', 'p.id', '=', 'a.session_student_participant_id')->join('class_sessions as s', 's.id', '=', 'p.class_session_id')->whereBetween('s.planned_start_at', [$from, $to])->max('a.updated_at');
        $classIds = $classes->modelKeys();

        return $this->candidate(AttendanceSourceType::LIVE_TRANSACTIONAL->value, $certified, $metrics, true, $semanticDecisionRequired, 'CLASS_SESSIONS:'.$period, $dataUpdatedAt);
    }

    private function candidate(string $sourceType, bool $certified, array $metrics, bool $validationPass, bool $semanticDecisionRequired, string $sourceReference, mixed $updatedAt): array
    {
        $eligible = $metrics['eligible_opportunities'];
        $resolved = $metrics['resolved_opportunities'];
        $completeness = $eligible === 0 ? null : round(($resolved / $eligible) * 100, 2);
        $metrics['completeness_rate'] = $completeness;
        $metrics['official_attendance_rate'] = $completeness === 100.0 && $validationPass ? round((($metrics['present']) / $eligible) * 100, 2) : null;

        return [
            'source_type' => $sourceType,
            'certification_status' => $certified ? 'CERTIFIED' : 'NOT_CERTIFIED',
            'completeness_rate' => $completeness,
            'validation_state' => $validationPass ? 'VALIDATED_OR_PUBLISHED' : 'INCOMPLETE_VALIDATION',
            'data_status' => $completeness === 100.0 ? 'COMPLETE' : ($resolved === 0 ? 'NOT_STARTED' : 'PARTIAL'),
            'semantic_decision_required' => $semanticDecisionRequired,
            'official_attendance_rate' => $metrics['official_attendance_rate'],
            'metrics' => $metrics,
            'record_count' => $eligible,
            'source_reference' => $sourceReference,
            'data_updated_at' => $updatedAt,
        ];
    }

    private function reconciles($candidates): bool
    {
        $first = $candidates->first()['metrics'];

        return $candidates->every(fn (array $candidate): bool => $candidate['metrics']['eligible_opportunities'] === $first['eligible_opportunities']
            && $candidate['metrics']['present'] === $first['present']
            && ($candidate['metrics']['late'] ?? 0) === ($first['late'] ?? 0)
            && $candidate['metrics']['permission'] === $first['permission']
            && $candidate['metrics']['sick'] === $first['sick']
            && $candidate['metrics']['absent'] === $first['absent']);
    }

    private function selectByConfiguredPrecedence($candidates): array
    {
        foreach (config('academic.attendance_source_precedence', []) as $sourceType) {
            $selected = $candidates->firstWhere('source_type', $sourceType);
            if ($selected !== null) {
                return $selected;
            }
        }

        return $candidates->first();
    }
}
