<?php

namespace App\Domains\Academic\AI;

use App\Domains\Academic\Models\AcademicClass;
use App\Domains\Academic\Models\ClassSession;
use App\Domains\Academic\Models\SessionStudentParticipant;
use App\Domains\Academic\Semantics\CanonicalAttendanceStatusMapper;
use App\Domains\Academic\Services\AttendanceSemanticMetricsService;
use App\Domains\Academic\Services\CanonicalAttendanceSemanticService;
use App\Domains\Academic\Services\JointAttendanceRosterBreakdownService;
use App\Domains\Academic\Services\SessionOccurrenceCutover;
use App\Shared\Core\Models\Student;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

final class AcademicAiAttendanceReader
{
    public function __construct(
        private readonly CanonicalAttendanceSemanticService $canonical,
        private readonly AttendanceSemanticMetricsService $metrics,
        private readonly SessionOccurrenceCutover $cutover,
        private readonly CanonicalAttendanceStatusMapper $statusMapper,
        private readonly JointAttendanceRosterBreakdownService $rosterBreakdown,
    ) {}

    public function studentSummary(Student $student, Carbon $from, Carbon $to): array
    {
        $sessions = $this->studentSessions($student, $from, $to);
        $participants = $this->opportunityParticipants($sessions);
        $summary = $this->canonical->summarize($participants);

        return [
            'summary' => $summary,
            'sessions' => $sessions,
            'warnings' => $this->warnings($sessions, $summary),
            'semantic_regime' => $this->semanticRegime($sessions),
        ];
    }

    public function studentDetails(Student $student, Carbon $from, Carbon $to, ?string $status, int $page, int $limit): array
    {
        $sessions = $this->studentSessions($student, $from, $to);
        $rows = $this->opportunityParticipants($sessions)->map(function (SessionStudentParticipant $participant): ?array {
            $attendance = $participant->attendance;
            if ($attendance?->workflow_status !== 'VALIDATED' || $attendance->attendance_status === null) {
                return null;
            }
            $semantic = $this->statusMapper->normalize($attendance->attendance_status);
            if ($semantic === null || $semantic->reconciliationRequired) {
                return null;
            }

            $session = $participant->classSession;

            return [
                'session_id' => (string) $session->id,
                'date' => $session->planned_start_at?->toDateString(),
                'scheduled_start_at' => $session->planned_start_at?->toIso8601String(),
                'class' => [
                    'class_id' => (string) ($session->academicClass?->id ?? $session->class_id),
                    'class_name' => $session->academicClass?->display_name,
                ],
                'subject' => $session->teachingAssignment?->subject?->subject_name,
                'attendance_status' => $semantic->outcomeValue(),
                'punctuality_status' => $semantic->punctualityStatus,
                'semantic_regime' => $this->cutover->regime($session),
            ];
        })->filter();

        if ($status !== null) {
            $rows = $rows->filter(fn (array $row): bool => $row['attendance_status'] === $status);
        }

        $total = $rows->count();
        $records = $rows->forPage($page, $limit)->values()->all();
        $warnings = $this->warnings($sessions, $this->canonical->summarize($this->opportunityParticipants($sessions)));
        if ($total > ($page * $limit)) {
            $warnings[] = ['code' => 'RESULTS_TRUNCATED', 'message' => 'Gunakan page berikutnya untuk melanjutkan data.'];
        }

        return compact('records', 'total', 'warnings') + ['semantic_regime' => $this->semanticRegime($sessions)];
    }

    public function classSummary(AcademicClass $class, Carbon $from, Carbon $to): array
    {
        $summary = $this->metrics->forClassPeriod($class, $from, $to);
        $sessions = $this->classSessions($class, $from, $to);

        return [
            'summary' => $summary,
            'sessions' => $sessions,
            'warnings' => $this->warnings($sessions, $summary),
            'semantic_regime' => $this->semanticRegime($sessions),
        ];
    }

    public function classRoster(AcademicClass $class, Carbon $from, Carbon $to, int $page, int $limit): array
    {
        $sessions = $this->classSessions($class, $from, $to);
        $opportunitySessions = $sessions->filter(fn (ClassSession $session): bool => $this->canonical->isAttendanceOpportunity($session));
        $participants = $opportunitySessions->flatMap(function (ClassSession $session) use ($class): Collection {
            $required = $session->studentParticipants->filter(fn (SessionStudentParticipant $participant): bool => $participant->is_required);

            return $session->scopeGroups->count() > 1
                ? $this->rosterBreakdown->forClass($session, $required, (string) $class->id)
                : $required;
        });
        $rows = $participants->groupBy('student_id')->map(function (Collection $studentParticipants): array {
            $student = $studentParticipants->first()->student;
            $summary = $this->canonical->summarize($studentParticipants);

            return [
                'student_id' => (string) $student->id,
                'display_name' => $student->full_name,
                'present' => $summary['counts']['PRESENT'] ?? 0,
                'permission' => $summary['counts']['PERMISSION'] ?? 0,
                'sick' => $summary['counts']['SICK'] ?? 0,
                'absent' => $summary['counts']['ABSENT'] ?? 0,
                'late' => $summary['late_count'],
                'eligible_opportunities' => $summary['eligible_opportunities'],
                'resolved_opportunities' => $summary['resolved_opportunities'],
                'missing_opportunities' => $summary['missing_opportunities'],
                'attendance_rate' => $summary['attendance_rate'],
                'completeness_rate' => $summary['completeness_rate'],
                'warnings' => $summary['invalid_statuses'],
            ];
        })->values();
        $total = $rows->count();
        $records = $rows->forPage($page, $limit)->values()->all();
        $warnings = $this->warnings($sessions, $this->metrics->forClassPeriod($class, $from, $to));
        if ($total > ($page * $limit)) {
            $warnings[] = ['code' => 'RESULTS_TRUNCATED', 'message' => 'Gunakan page berikutnya untuk melanjutkan roster.'];
        }

        return compact('records', 'total', 'warnings') + ['semantic_regime' => $this->semanticRegime($sessions)];
    }

    /** @return Collection<int, ClassSession> */
    private function studentSessions(Student $student, Carbon $from, Carbon $to): Collection
    {
        return ClassSession::query()
            ->whereBetween('planned_start_at', [$from, $to])
            ->whereHas('studentParticipants', fn ($query) => $query->where('student_id', $student->id)->where('participant_status', 'EXPECTED'))
            ->with(['academicClass', 'scopeGroups', 'effectiveOccurrenceVersion', 'teachingAssignment.subject', 'studentParticipants' => fn ($query) => $query
                ->where('student_id', $student->id)->where('participant_status', 'EXPECTED')->with('attendance')])
            ->orderBy('planned_start_at')
            ->get();
    }

    /** @return Collection<int, ClassSession> */
    private function classSessions(AcademicClass $class, Carbon $from, Carbon $to): Collection
    {
        return ClassSession::query()
            ->where(fn ($query) => $query->where('class_id', $class->id)->orWhereHas('scopeGroups', fn ($scope) => $scope->where('class_id', $class->id)))
            ->whereBetween('planned_start_at', [$from, $to])
            ->with(['academicClass', 'scopeGroups', 'effectiveOccurrenceVersion', 'studentParticipants' => fn ($query) => $query
                ->where('participant_status', 'EXPECTED')->with(['attendance', 'student'])])
            ->orderBy('planned_start_at')
            ->get();
    }

    private function opportunityParticipants(Collection $sessions): Collection
    {
        return $sessions->filter(fn (ClassSession $session): bool => $this->canonical->isAttendanceOpportunity($session))
            ->flatMap(fn (ClassSession $session): Collection => $session->studentParticipants->filter(fn (SessionStudentParticipant $participant): bool => $participant->is_required));
    }

    private function semanticRegime(Collection $sessions): ?string
    {
        $regimes = $sessions->map(fn (ClassSession $session): string => $this->cutover->regime($session))->unique()->values();

        return $regimes->count() > 1 ? 'MIXED' : $regimes->first();
    }

    private function warnings(Collection $sessions, array $summary): array
    {
        $warnings = [];
        $missing = $sessions->filter(fn (ClassSession $session): bool => $this->canonical->isMissingCanonicalOccurrence($session));
        if ($missing->isNotEmpty()) {
            $warnings[] = ['code' => 'MISSING_CANONICAL_OCCURRENCE', 'count' => $missing->count()];
        }
        if (($summary['reconciliation_required_count'] ?? 0) > 0) {
            $warnings[] = ['code' => 'RECONCILIATION_REQUIRED', 'count' => $summary['reconciliation_required_count']];
        }

        return $warnings;
    }
}
