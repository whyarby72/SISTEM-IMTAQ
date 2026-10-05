<?php

namespace App\Domains\Academic\Services;

use App\Domains\Academic\Models\AcademicClass;
use App\Domains\Academic\Models\ClassSession;
use App\Domains\Academic\Models\SessionStudentParticipant;
use App\Domains\Academic\Semantics\CanonicalAttendanceStatusMapper;
use App\Domains\Academic\Semantics\Enums\AttendanceDataReadiness;
use App\Domains\Academic\Semantics\Enums\CanonicalAttendanceStatus;
use App\Domains\Academic\Semantics\Enums\EligibilityStatus;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

class CanonicalAttendanceSemanticService
{
    public function __construct(
        private readonly CanonicalAttendanceStatusMapper $statusMapper,
        private readonly JointAttendanceRosterBreakdownService $rosterBreakdown,
        private readonly SessionOccurrenceCutover $cutover,
        private readonly WaliClassEntitlementResolver $waliEntitlements,
    ) {}

    public function sessionState(ClassSession $session, ?Carbon $now = null): string
    {
        $now ??= Carbon::now();

        if ($session->session_status === 'CANCELLED') {
            return 'CANCELLED';
        }
        if ($session->session_status === 'RESCHEDULED') {
            return 'RESCHEDULED_SOURCE';
        }
        if ($session->session_status === 'PLANNED' && $session->planned_start_at->isFuture($now)) {
            return 'PLANNED_FUTURE';
        }
        if ($session->session_status === 'PLANNED') {
            return 'PLANNED_HISTORICAL_WORK_QUEUE';
        }
        if ($session->session_status === 'COMPLETED') {
            return 'COMPLETED';
        }

        return 'OTHER';
    }

    /** @return array{status: EligibilityStatus, reason: ?string} */
    public function eligibility(SessionStudentParticipant $participant): array
    {
        $status = $participant->eligibility_status;
        if ($status === null) {
            $status = $participant->is_required ? EligibilityStatus::ELIGIBLE->value : EligibilityStatus::NON_ELIGIBLE->value;
        }

        return ['status' => EligibilityStatus::from($status), 'reason' => $participant->non_eligible_reason];
    }

    public function summarize(Collection $participants): array
    {
        $eligible = $participants->filter(fn (SessionStudentParticipant $participant): bool => $this->eligibility($participant)['status'] === EligibilityStatus::ELIGIBLE);
        $counts = collect(CanonicalAttendanceStatus::cases())->mapWithKeys(fn (CanonicalAttendanceStatus $status): array => [$status->value => 0])->all();
        $invalidStatuses = [];
        $legacyStatusCounts = [];
        $reconciliationRequiredStatuses = [];
        $lateCount = 0;
        $resolved = 0;
        $missing = 0;

        foreach ($eligible as $participant) {
            $attendance = $participant->attendance;
            if ($attendance?->workflow_status !== 'VALIDATED' || $attendance->attendance_status === null) {
                $missing++;

                continue;
            }

            try {
                $semantic = $this->statusMapper->normalize($attendance->attendance_status);
            } catch (\InvalidArgumentException) {
                $invalidStatuses[] = $attendance->attendance_status;
                $reconciliationRequiredStatuses[$attendance->attendance_status] = ($reconciliationRequiredStatuses[$attendance->attendance_status] ?? 0) + 1;

                continue;
            }

            $legacyStatusCounts[$semantic->legacySourceStatus] = ($legacyStatusCounts[$semantic->legacySourceStatus] ?? 0) + 1;
            if ($semantic->reconciliationRequired) {
                $reconciliationRequiredStatuses[$semantic->legacySourceStatus] = ($reconciliationRequiredStatuses[$semantic->legacySourceStatus] ?? 0) + 1;

                continue;
            }

            $counts[$semantic->outcomeValue()]++;
            $lateCount += $semantic->punctualityStatus === 'LATE' ? 1 : 0;
            $resolved++;
        }

        $eligibleCount = $eligible->count();
        $reconciliationRequired = array_sum($reconciliationRequiredStatuses);
        $completeness = $eligibleCount === 0 ? null : round(($resolved / $eligibleCount) * 100, 2);
        $attendanceRate = $eligibleCount === 0 ? null : round((($counts[CanonicalAttendanceStatus::PRESENT->value] ?? 0) / $eligibleCount) * 100, 2);
        $accountingInvariant = [
            'eligible_opportunities' => $eligibleCount,
            'resolved_opportunities' => $resolved,
            'missing_opportunities' => $missing,
            'reconciliation_required_count' => $reconciliationRequired,
            'is_balanced' => $eligibleCount === ($resolved + $missing + $reconciliationRequired),
        ];
        $readiness = $eligibleCount === 0
            ? AttendanceDataReadiness::NOT_STARTED
            : ($resolved === 0 && $reconciliationRequired === 0 ? AttendanceDataReadiness::NOT_STARTED : ($missing > 0 || $reconciliationRequired > 0 ? AttendanceDataReadiness::PARTIAL : AttendanceDataReadiness::COMPLETE));

        return [
            'eligible_opportunities' => $eligibleCount,
            'resolved_opportunities' => $resolved,
            'missing_opportunities' => $missing,
            'counts' => $counts,
            'late_count' => $lateCount,
            'physical_present_count' => $counts[CanonicalAttendanceStatus::PRESENT->value] ?? 0,
            'attendance_rate' => $attendanceRate,
            'completeness_rate' => $completeness,
            'data_status' => $readiness->value,
            'semantic_decision_required' => $reconciliationRequired > 0,
            'reconciliation_required_count' => $reconciliationRequired,
            'reconciliation_required_statuses' => $reconciliationRequiredStatuses,
            'legacy_status_counts' => $legacyStatusCounts,
            'accounting_invariant' => $accountingInvariant,
            'invalid_statuses' => array_values(array_unique($invalidStatuses)),
            'official_attendance_rate' => $this->officialAttendanceRate($attendanceRate, $missing, $reconciliationRequired),
        ];
    }

    /**
     * Canonical live-period read model. Historical PLANNED sessions are returned
     * as an operational queue but never enter the official denominator.
     */
    public function forClassPeriod(AcademicClass $class, Carbon $from, Carbon $to, ?array $authorizedWindows = null): array
    {
        $query = ClassSession::query()
            ->with(['scopeGroups', 'effectiveOccurrenceVersion', 'studentParticipants' => fn ($query) => $query
                ->where('participant_status', 'EXPECTED')
                ->with(['attendance', 'student.classEnrollments'])]);
        if ($authorizedWindows === null) {
            $query
                ->where(fn ($scope) => $scope
                    ->where('class_id', $class->id)
                    ->orWhereHas('scopeGroups', fn ($scopeQuery) => $scopeQuery->where('class_id', $class->id)))
                ->whereBetween('planned_start_at', [$from, $to]);
        } else {
            $this->waliEntitlements->constrainSessionQuery($query, [(string) $class->id => $authorizedWindows], $from, $to);
        }
        $sessions = $query->get();

        $opportunitySessions = $sessions->filter(fn (ClassSession $session): bool => $this->isAttendanceOpportunity($session));
        $participants = $opportunitySessions->flatMap(function (ClassSession $session) use ($class) {
            if ($session->scopeGroups->count() <= 1) {
                return $session->studentParticipants->filter(fn (SessionStudentParticipant $participant): bool => $participant->is_required);
            }

            return $this->rosterBreakdown->forClass($session, $session->studentParticipants->filter(fn (SessionStudentParticipant $participant): bool => $participant->is_required), (string) $class->id);
        });

        $summary = $this->summarize($participants);
        $summary['completed_sessions'] = $opportunitySessions->count();
        $summary['canonical_held_sessions'] = $sessions->filter(fn (ClassSession $session): bool => $this->cutover->isCanonical($session) && $session->effectiveOccurrenceVersion?->occurrence_status === 'HELD')->count();
        $summary['legacy_completed_sessions'] = $sessions->filter(fn (ClassSession $session): bool => $this->cutover->regime($session) === SessionOccurrenceCutover::LEGACY && $session->session_status === 'COMPLETED')->count();
        $summary['missing_canonical_occurrence_sessions'] = $sessions
            ->filter(fn (ClassSession $session): bool => $this->isMissingCanonicalOccurrence($session))
            ->values()
            ->map(fn (ClassSession $session): array => [
                'id' => (string) $session->id,
                'planned_start_at' => $session->planned_start_at?->toIso8601String(),
                'planned_end_at' => $session->planned_end_at?->toIso8601String(),
                'session_status' => $session->session_status,
            ])->all();
        $summary['historical_planned_sessions'] = $sessions->filter(fn (ClassSession $session): bool => $this->sessionState($session) === 'PLANNED_HISTORICAL_WORK_QUEUE')->values()->map(fn (ClassSession $session): array => [
            'id' => (string) $session->id,
            'planned_start_at' => $session->planned_start_at?->toIso8601String(),
            'planned_end_at' => $session->planned_end_at?->toIso8601String(),
        ])->all();
        $summary['future_planned_sessions'] = $sessions->filter(fn (ClassSession $session): bool => $this->sessionState($session) === 'PLANNED_FUTURE')->count();
        $summary['cancelled_sessions'] = $sessions->filter(fn (ClassSession $session): bool => $this->sessionState($session) === 'CANCELLED')->count();
        $summary['rescheduled_sessions'] = $sessions->filter(fn (ClassSession $session): bool => $this->sessionState($session) === 'RESCHEDULED_SOURCE')->count();

        return $summary;
    }

    public function isAttendanceOpportunity(ClassSession $session): bool
    {
        if ($this->cutover->regime($session) === SessionOccurrenceCutover::LEGACY) {
            return $session->session_status === 'COMPLETED';
        }

        return $this->cutover->regime($session) === SessionOccurrenceCutover::CANONICAL
            && $session->effectiveOccurrenceVersion?->occurrence_status === 'HELD';
    }

    public function isMissingCanonicalOccurrence(ClassSession $session, ?Carbon $now = null): bool
    {
        $now ??= Carbon::now();

        return $this->cutover->regime($session) === SessionOccurrenceCutover::CANONICAL
            && $session->planned_start_at->lessThanOrEqualTo($now)
            && $session->effective_occurrence_version_id === null;
    }

    private function officialAttendanceRate(?float $attendanceRate, int $missing, int $reconciliationRequired): ?float
    {
        if ($attendanceRate === null || $missing > 0 || $reconciliationRequired > 0) {
            return null;
        }

        return $attendanceRate;
    }
}
