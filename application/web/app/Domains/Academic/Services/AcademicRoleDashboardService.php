<?php

namespace App\Domains\Academic\Services;

use App\Domains\Academic\Models\AcademicClass;
use App\Domains\Academic\Models\ClassHomeroomAssignment;
use App\Domains\Academic\Models\ClassSession;
use App\Domains\Academic\Models\Semester;
use App\Domains\Academic\Models\SessionTeacherParticipation;
use App\Domains\Academic\Models\StudentClassEnrollment;
use App\Domains\Academic\Models\TeachingAssignment;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

class AcademicRoleDashboardService
{
    public function __construct(
        private readonly AttendanceSemanticMetricsService $attendanceMetrics,
        private readonly SessionSemanticMetricsService $sessionMetrics,
        private readonly GradeSemanticMetricsService $gradeMetrics,
        private readonly AcademicAuthorizationService $authorization,
        private readonly JointAttendanceRosterBreakdownService $rosterBreakdown,
    ) {}

    public function forUser(User $actor, Carbon $from, Carbon $to, ?Semester $semester = null, int $trendDays = 14): array
    {
        $role = $this->roleFor($actor, $to);
        $classes = in_array($role, ['SUPER_ADMIN', 'WAKA_AKADEMIK'], true)
            ? AcademicClass::query()->with('gradeLevel')
                ->whereHas('academicYear', fn ($query) => $query
                    ->where('year_code', 'not like', '%-PILOT')
                    ->whereDate('starts_on', '<=', $to->toDateString())
                    ->whereDate('ends_on', '>=', $from->toDateString()))
                ->orderBy('display_name')->get()
            : $this->waliClasses($actor, $from, $to);

        $classItems = $classes->map(fn (AcademicClass $class): array => [
            'class' => $class,
            'attendance' => $this->dashboardAttendance($this->attendanceMetrics->forClassPeriod($class, $from, $to)),
            'sessions' => $this->sessionMetrics->forClassPeriod($class, $from, $to),
            'grades' => $semester === null ? null : $this->gradeMetrics->forSemester($semester, null, $class),
        ])->values();
        $teacherAttendance = $this->teacherAttendance($classes, $from, $to);

        return [
            'role' => $role,
            'classes' => $classItems,
            'grade_levels' => $this->aggregateGradeLevels($classItems),
            'overview' => $this->overview($classes, $classItems, $from, $to),
            'attendance_trend' => $this->attendanceTrend($classes, $from, $to, $trendDays),
            'attendance_trend_source' => 'daily_transactions',
            'today_attendance' => $this->periodAttendance($classes, $from, $to, $role === 'WALI_KELAS'),
            'attendance_status_source' => 'live_sessions',
            'teacher_attendance' => $teacherAttendance,
            'attendance_sessions' => $role === 'WALI_KELAS' ? $this->attendanceSessions($classes, $from, $to) : collect(),
            'wali_operational' => $role === 'WALI_KELAS' ? $this->waliOperationalHome($classes) : null,
        ];
    }

    private function waliOperationalHome(Collection $classes): array
    {
        $class = $classes->first();
        if (! $class instanceof AcademicClass) {
            return [
                'has_assignment' => false,
                'class' => null,
                'semester' => null,
                'active_student_count' => 0,
                'today_sessions' => collect(),
                'urgent' => ['due_not_started' => 0, 'due_incomplete' => 0, 'teacher_attendance_missing' => 0],
                'today_completion' => ['finalized' => 0, 'due' => 0, 'rate' => null],
                'next_session' => null,
            ];
        }

        $timezone = (string) config('academic.business_timezone', 'Asia/Jakarta');
        $now = Carbon::now($timezone);
        $todayStart = $now->copy()->startOfDay();
        $tomorrowStart = $todayStart->copy()->addDay();
        $class->loadMissing(['gradeLevel', 'academicYear']);
        $semester = Semester::query()
            ->where('academic_year_id', $class->academic_year_id)
            ->whereDate('starts_on', '<=', $now->toDateString())
            ->whereDate('ends_on', '>=', $now->toDateString())
            ->orderBy('sequence_no')
            ->first();

        $todaySessions = $this->waliOperationalSessionsQuery($classes)
            ->where('planned_start_at', '>=', $todayStart->copy()->utc())
            ->where('planned_start_at', '<', $tomorrowStart->copy()->utc())
            ->get()
            ->map(fn (ClassSession $session): array => $this->waliSessionItem($session, $classes, $now))
            ->sortBy(fn (array $item): string => sprintf('%02d-%s', $item['priority'], $item['session']->planned_start_at->format('YmdHis')))
            ->values();

        $nextSession = $this->waliOperationalSessionsQuery($classes)
            ->where('planned_start_at', '>', $now->copy()->utc())
            ->orderBy('planned_start_at')
            ->first();

        $due = $todaySessions->whereIn('state', ['DUE_NOT_STARTED', 'DUE_INCOMPLETE', 'FINALIZED'])->count();
        $finalized = $todaySessions->where('state', 'FINALIZED')->count();

        return [
            'has_assignment' => true,
            'class' => $class,
            'semester' => $semester,
            'active_student_count' => $this->activeStudentCountForClass($class, $now),
            'today_sessions' => $todaySessions,
            'urgent' => [
                'due_not_started' => $todaySessions->where('state', 'DUE_NOT_STARTED')->count(),
                'due_incomplete' => $todaySessions->where('state', 'DUE_INCOMPLETE')->count(),
                'teacher_attendance_missing' => $todaySessions
                    ->whereIn('state', ['DUE_NOT_STARTED', 'DUE_INCOMPLETE', 'IN_PROGRESS'])
                    ->where('teacher_attendance_missing', true)
                    ->count(),
            ],
            'today_completion' => [
                'finalized' => $finalized,
                'due' => $due,
                'rate' => $due === 0 ? null : round(($finalized / $due) * 100, 2),
            ],
            'next_session' => $nextSession === null ? null : $this->waliSessionItem($nextSession, $classes, $now),
        ];
    }

    private function waliOperationalSessionsQuery(Collection $classes)
    {
        return ClassSession::query()
            ->with([
                'academicClass.gradeLevel',
                'academicClass.academicYear',
                'scopeGroups.academicClass',
                'teachingAssignment.subject',
                'teachingAssignment.teacher',
                'studentParticipants' => fn ($query) => $query
                    ->where('participant_status', 'EXPECTED')
                    ->where('is_required', true)
                    ->with(['attendance', 'student.classEnrollments']),
                'teacherParticipations' => fn ($query) => $query
                    ->where('participation_status', 'EXPECTED')
                    ->with('teacher'),
            ])
            ->where(fn ($query) => $query
                ->whereIn('class_id', $classes->modelKeys())
                ->orWhereHas('scopeGroups', fn ($scopeQuery) => $scopeQuery->whereIn('class_id', $classes->modelKeys())))
            ->whereNotIn('session_status', ['CANCELLED', 'RESCHEDULED']);
    }

    private function waliSessionItem(ClassSession $session, Collection $classes, Carbon $now): array
    {
        $participants = $this->participantsForDashboardClasses($session, $classes);
        $eligible = $participants->count();
        $resolved = $participants->filter(fn ($participant) => $participant->attendance?->attendance_status !== null)->count();
        $missing = max(0, $eligible - $resolved);
        $finalized = $eligible > 0
            && $missing === 0
            && $participants->every(fn ($participant) => $participant->attendance?->workflow_status === 'VALIDATED');

        if ($finalized) {
            $state = 'FINALIZED';
        } elseif ($session->planned_start_at->greaterThan($now)) {
            $state = 'UPCOMING';
        } elseif ($session->planned_end_at->greaterThan($now)) {
            $state = 'IN_PROGRESS';
        } elseif ($resolved === 0) {
            $state = 'DUE_NOT_STARTED';
        } else {
            $state = 'DUE_INCOMPLETE';
        }

        $labels = [
            'UPCOMING' => ['Akan datang', 'Lihat Sesi'],
            'IN_PROGRESS' => ['Sedang berlangsung', $resolved > 0 ? 'Lanjutkan Pengisian' : 'Isi Kehadiran'],
            'DUE_NOT_STARTED' => [$eligible === 0 ? 'Roster belum tersedia' : 'Belum diisi', $eligible === 0 ? 'Lihat Sesi' : 'Isi Kehadiran'],
            'DUE_INCOMPLETE' => ['Belum lengkap', 'Lanjutkan Pengisian'],
            'FINALIZED' => ['Sudah disahkan', 'Lihat Hasil'],
        ];
        $teacherParticipation = $session->teacherParticipations
            ->first(fn ($participation) => $participation->role === 'PRIMARY')
            ?? $session->teacherParticipations->first();
        $teacherAttendanceLabels = [
            'PRESENT' => 'Hadir',
            'ABSENT' => 'Tidak hadir',
            'SICK' => 'Sakit',
            'IZIN' => 'Izin',
            'OTHER' => 'Lainnya',
        ];

        return [
            'session' => $session,
            'state' => $state,
            'priority' => ['DUE_INCOMPLETE' => 1, 'DUE_NOT_STARTED' => 2, 'IN_PROGRESS' => 3, 'UPCOMING' => 4, 'FINALIZED' => 5][$state],
            'status_label' => $labels[$state][0],
            'action_label' => $labels[$state][1],
            'class_label' => $classes->pluck('display_name')->implode(' + '),
            'subject_label' => $session->teachingAssignment?->subject?->subject_name ?? 'Pelajaran',
            'teacher_label' => $session->teachingAssignment?->teacher?->full_name ?? 'Guru belum ditetapkan',
            'eligible' => $eligible,
            'resolved' => $resolved,
            'missing' => $missing,
            'completion_rate' => $eligible === 0 ? null : round(($resolved / $eligible) * 100, 2),
            'teacher_attendance_label' => $teacherParticipation === null
                ? 'Partisipasi guru belum tersedia'
                : ($teacherAttendanceLabels[$teacherParticipation->attendance_status] ?? 'Belum dicatat'),
            'teacher_attendance_missing' => $teacherParticipation === null || $teacherParticipation->attendance_status === null,
        ];
    }

    private function activeStudentCountForClass(AcademicClass $class, Carbon $asOf): int
    {
        return StudentClassEnrollment::query()
            ->where('class_id', $class->id)
            ->where('status', 'ACTIVE')
            ->whereDate('effective_from', '<=', $asOf->toDateString())
            ->where(fn ($query) => $query->whereNull('effective_until')->orWhereDate('effective_until', '>', $asOf->toDateString()))
            ->whereExists(fn ($query) => $query
                ->selectRaw('1')
                ->from('student_status_history')
                ->whereColumn('student_status_history.student_id', 'student_class_enrollments.student_id')
                ->where('student_status_history.status', 'ACTIVE')
                ->whereDate('student_status_history.effective_from', '<=', $asOf->toDateString())
                ->where(fn ($statusQuery) => $statusQuery->whereNull('student_status_history.effective_until')->orWhereDate('student_status_history.effective_until', '>', $asOf->toDateString())))
            ->distinct('student_id')
            ->count('student_id');
    }

    private function attendanceSessions($classes, Carbon $from, Carbon $to)
    {
        $sessions = ClassSession::query()
            ->with([
                'academicClass',
                'scopeGroups',
                'teachingAssignment.subject',
                'studentParticipants' => fn ($query) => $query
                    ->where('participant_status', 'EXPECTED')
                    ->where('is_required', true)
                    ->with(['attendance', 'student.classEnrollments']),
            ])
            ->where(fn ($query) => $query
                ->whereIn('class_id', $classes->modelKeys())
                ->orWhereHas('scopeGroups', fn ($scopeQuery) => $scopeQuery->whereIn('class_id', $classes->modelKeys())))
            ->whereNotIn('session_status', ['CANCELLED', 'RESCHEDULED'])
            ->whereBetween('planned_start_at', [$from, $to])
            ->orderBy('planned_start_at')
            ->limit(12)
            ->get();

        return $sessions->each(function (ClassSession $session) use ($classes): void {
            $participants = $this->participantsForDashboardClasses($session, $classes);
            $session->setAttribute('student_participants_count', $participants->count());
            $session->setRelation('studentParticipants', $participants);
            $resolved = $participants->filter(fn ($participant) => $participant->attendance?->attendance_status !== null)->count();
            $finalized = $participants->isNotEmpty()
                && $resolved === $participants->count()
                && $participants->every(fn ($participant) => $participant->attendance?->workflow_status === 'VALIDATED');

            $session->setAttribute('attendance_label', $finalized ? 'Sudah disahkan' : ($resolved > 0 ? 'Belum lengkap' : 'Belum diisi'));
            $session->setAttribute('attendance_action', $finalized ? 'Lihat kehadiran' : ($resolved > 0 ? 'Lanjutkan pengisian' : 'Isi kehadiran'));
        });
    }

    private function periodAttendance($classes, Carbon $from, Carbon $to, bool $partitionByClass): array
    {
        $now = Carbon::now();
        $sessions = ClassSession::query()
            ->where(fn ($query) => $query
                ->whereIn('class_id', $classes->modelKeys())
                ->orWhereHas('scopeGroups', fn ($scopeQuery) => $scopeQuery->whereIn('class_id', $classes->modelKeys())))
            ->whereBetween('planned_start_at', [$from, $to])
            ->whereNotIn('session_status', ['CANCELLED', 'RESCHEDULED'])
            ->with(['studentParticipants' => fn ($query) => $query
                ->where('participant_status', 'EXPECTED')
                ->where('is_required', true)
                ->with('attendance')])
            ->orderBy('planned_start_at')
            ->when($partitionByClass, fn ($query) => $query->with('scopeGroups'))
            ->get();

        $due = $finalized = $dueNotFinalized = $inProgress = $upcoming = 0;
        $lastFinalizedAt = null;
        foreach ($sessions as $session) {
            $required = $this->participantsForDashboardClasses($session, $classes, $partitionByClass);
            $complete = $required->isNotEmpty() && $required->every(fn ($participant) => $participant->attendance?->workflow_status === 'VALIDATED'
                && $participant->attendance->attendance_status !== null);
            $isFinalized = $partitionByClass ? $complete : ($session->session_status === 'COMPLETED' && $complete);

            if ($session->planned_start_at->isFuture()) {
                $upcoming++;

                continue;
            }
            if ($session->planned_end_at->isFuture()) {
                $inProgress++;

                continue;
            }

            $due++;
            if ($isFinalized) {
                $finalized++;
                $sessionFinalizedAt = $required->map(fn ($participant) => $participant->attendance?->finalized_at)->filter()->max();
                if ($sessionFinalizedAt !== null && ($lastFinalizedAt === null || $sessionFinalizedAt->greaterThan($lastFinalizedAt))) {
                    $lastFinalizedAt = $sessionFinalizedAt;
                }
            } else {
                $dueNotFinalized++;
            }
        }

        return [
            'due_sessions' => $due,
            'finalized_sessions' => $finalized,
            'due_not_finalized_sessions' => $dueNotFinalized,
            'in_progress_sessions' => $inProgress,
            'upcoming_sessions' => $upcoming,
            'completion_rate' => $due === 0 ? null : round(($finalized / $due) * 100, 2),
            'last_finalized_at' => $lastFinalizedAt,
        ];
    }

    private function teacherAttendance($classes, Carbon $from, Carbon $to): array
    {
        $participations = SessionTeacherParticipation::query()
            ->whereHas('classSession', fn ($query) => $query
                ->where(fn ($sessionQuery) => $sessionQuery
                    ->whereIn('class_id', $classes->modelKeys())
                    ->orWhereHas('scopeGroups', fn ($scopeQuery) => $scopeQuery->whereIn('class_id', $classes->modelKeys())))
                ->whereNotIn('session_status', ['CANCELLED', 'RESCHEDULED'])
                ->where('planned_end_at', '<=', Carbon::now())
                ->whereBetween('planned_start_at', [$from, $to]))
            ->where('participation_status', 'EXPECTED')
            ->get();
        $eligible = $participations->count();
        $resolved = $participations->whereIn('attendance_status', ['PRESENT', 'ABSENT', 'SICK', 'IZIN', 'OTHER'])->count();
        $present = $participations->where('attendance_status', 'PRESENT')->count();
        $absent = $participations->where('attendance_status', 'ABSENT')->count();
        $sick = $participations->where('attendance_status', 'SICK')->count();
        $izin = $participations->where('attendance_status', 'IZIN')->count();
        $other = $participations->where('attendance_status', 'OTHER')->count();
        $rate = static fn (int $numerator, int $denominator) => $denominator === 0 ? null : round(($numerator / $denominator) * 100, 2);

        return [
            'eligible_participations' => $eligible,
            'resolved_participations' => $resolved,
            'missing_participations' => $eligible - $resolved,
            'present' => $present,
            'absent' => $absent,
            'sick' => $sick,
            'izin' => $izin,
            'other' => $other,
            'presence_rate' => $rate($present, $eligible),
            'completion_rate' => $rate($resolved, $eligible),
        ];
    }

    private function attendanceTrend($classes, Carbon $from, Carbon $to, int $trendDays): array
    {
        $trendFrom = $to->copy()->subDays(max(1, $trendDays) - 1)->startOfDay()->max($from);
        $days = [];
        for ($date = $trendFrom->copy()->startOfDay(); $date->lte($to); $date->addDay()) {
            $day = $date->toDateString();
            $days[$day] = $classes->map(fn (AcademicClass $class): array => $this->dashboardAttendance(
                $this->attendanceMetrics->forClassPeriod($class, $date->copy(), $date->copy()->endOfDay())
            ));
        }

        return collect($days)->sortKeys()->map(function (Collection $day, string $date): array {
            $attendance = $this->aggregateAttendanceMetrics($day);

            return [
                'date' => $date,
                'eligible_opportunities' => $attendance['eligible_opportunities'],
                'resolved_opportunities' => $attendance['resolved_opportunities'],
                'physical_presence_rate' => $attendance['physical_presence_rate'],
                'completeness_rate' => $attendance['completeness_rate'],
                'attendance' => $attendance,
            ];
        })->values()->all();
    }

    private function overview($classes, $classItems, Carbon $from, Carbon $to): array
    {
        $classIds = $classes->modelKeys();
        $asOf = $to->toDateString();
        $activeStudentCount = StudentClassEnrollment::query()
            ->whereIn('class_id', $classIds)
            ->where('status', 'ACTIVE')
            ->whereDate('effective_from', '<=', $asOf)
            ->where(fn ($query) => $query->whereNull('effective_until')->orWhereDate('effective_until', '>', $asOf))
            ->whereExists(fn ($query) => $query
                ->selectRaw('1')
                ->from('student_status_history')
                ->whereColumn('student_status_history.student_id', 'student_class_enrollments.student_id')
                ->where('student_status_history.status', 'ACTIVE')
                ->whereDate('student_status_history.effective_from', '<=', $asOf)
                ->where(fn ($statusQuery) => $statusQuery->whereNull('student_status_history.effective_until')->orWhereDate('student_status_history.effective_until', '>', $asOf)))
            ->distinct('student_id')
            ->count('student_id');

        $activeTeacherCount = TeachingAssignment::query()
            ->whereIn('class_id', $classIds)
            ->whereIn('workflow_status', ['ACTIVE', 'APPROVED', 'PUBLISHED'])
            ->whereDate('effective_from', '<=', $to->toDateString())
            ->where(fn ($query) => $query->whereNull('effective_until')->orWhereDate('effective_until', '>', $from->toDateString()))
            ->whereHas('teacher', fn ($query) => $query
                ->where('record_status', 'ACTIVE')
                ->where(fn ($staffQuery) => $staffQuery->whereNull('active_from')->orWhereDate('active_from', '<=', $to->toDateString()))
                ->where(fn ($staffQuery) => $staffQuery->whereNull('active_until')->orWhereDate('active_until', '>', $from->toDateString())))
            ->distinct('teacher_staff_id')
            ->count('teacher_staff_id');

        $attendance = $this->aggregateAttendanceMetrics($classItems->map(fn (array $item): array => $item['attendance']));

        return [
            'active_student_count' => $activeStudentCount,
            'active_teacher_count' => $activeTeacherCount,
            'active_class_count' => $classes->count(),
            'attendance' => $attendance,
        ];
    }

    private function aggregateGradeLevels($classItems)
    {
        return $classItems->groupBy(fn (array $item) => $item['class']->grade_level_id)
            ->map(function ($items, string $gradeLevelId): array {
                $attendance = $this->aggregateAttendanceMetrics($items->map(fn (array $item): array => $item['attendance']));
                $countedSessions = $items->sum(fn (array $item) => $item['sessions']['counted_sessions']);
                $completedSessions = $items->sum(fn (array $item) => $item['sessions']['completed_sessions']);
                $rate = static fn (int $numerator, int $denominator) => $denominator === 0 ? null : round(($numerator / $denominator) * 100, 2);
                $firstClass = $items->first()['class'];

                return [
                    'grade_level_id' => $gradeLevelId,
                    'grade_level' => $firstClass->gradeLevel,
                    'class_count' => $items->count(),
                    'class_ids' => $items->pluck('class.id')->values(),
                    'attendance' => $attendance,
                    'sessions' => [
                        'counted_sessions' => $countedSessions,
                        'completed_sessions' => $completedSessions,
                        'extra_sessions' => $items->sum(fn (array $item) => $item['sessions']['extra_sessions']),
                        'completion_rate' => $rate($completedSessions, $countedSessions),
                    ],
                ];
            })->sortBy(fn (array $item) => $item['grade_level']->sequence_no)->values();
    }

    private function dashboardAttendance(array $metrics): array
    {
        $counts = $metrics['counts'] ?? [];

        return [
            ...$metrics,
            'eligible_count' => (int) ($metrics['eligible_opportunities'] ?? 0),
            'present_count' => (int) ($counts['PRESENT'] ?? 0),
            'late_count' => (int) ($metrics['late_count'] ?? 0),
            'permission_count' => (int) ($counts['PERMISSION'] ?? 0),
            'sick_count' => (int) ($counts['SICK'] ?? 0),
            'absent_count' => (int) ($counts['ABSENT'] ?? 0),
            'resolved_count' => (int) ($metrics['resolved_opportunities'] ?? 0),
            'missing_count' => (int) ($metrics['missing_opportunities'] ?? 0),
            'attendance_rate' => $metrics['attendance_rate'] ?? null,
            'attendance_completeness' => $metrics['completeness_rate'] ?? null,
            'physical_presence_rate' => $metrics['attendance_rate'] ?? null,
        ];
    }

    private function aggregateAttendanceMetrics(Collection $metrics): array
    {
        $counts = [
            'PRESENT' => 0,
            'PERMISSION' => 0,
            'SICK' => 0,
            'ABSENT' => 0,
        ];
        foreach ($metrics as $metric) {
            foreach (array_keys($counts) as $status) {
                $counts[$status] += (int) ($metric['counts'][$status] ?? 0);
            }
        }

        $eligible = $metrics->sum(fn (array $metric): int => (int) ($metric['eligible_count'] ?? $metric['eligible_opportunities'] ?? 0));
        $resolved = $metrics->sum(fn (array $metric): int => (int) ($metric['resolved_count'] ?? $metric['resolved_opportunities'] ?? 0));
        $missing = $metrics->sum(fn (array $metric): int => (int) ($metric['missing_count'] ?? $metric['missing_opportunities'] ?? 0));
        $reconciliation = $metrics->sum(fn (array $metric): int => (int) ($metric['reconciliation_required_count'] ?? 0));
        $late = $metrics->sum(fn (array $metric): int => (int) ($metric['late_count'] ?? 0));
        $present = $counts['PRESENT'];
        $rate = static fn (int $numerator, int $denominator) => $denominator === 0 ? null : round(($numerator / $denominator) * 100, 2);

        return [
            'eligible_opportunities' => $eligible,
            'resolved_opportunities' => $resolved,
            'missing_opportunities' => $missing,
            'reconciliation_required_count' => $reconciliation,
            'counts' => $counts,
            'late_count' => $late,
            'physical_present_count' => $present,
            'eligible_count' => $eligible,
            'present_count' => $present,
            'permission_count' => $counts['PERMISSION'],
            'sick_count' => $counts['SICK'],
            'absent_count' => $counts['ABSENT'],
            'resolved_count' => $resolved,
            'missing_count' => $missing,
            'attendance_rate' => $rate($present, $eligible),
            'attendance_completeness' => $rate($resolved, $eligible),
            'physical_presence_rate' => $rate($present, $eligible),
            'completeness_rate' => $rate($resolved, $eligible),
            'accounting_invariant' => [
                'eligible_opportunities' => $eligible,
                'resolved_opportunities' => $resolved,
                'missing_opportunities' => $missing,
                'reconciliation_required_count' => $reconciliation,
                'is_balanced' => $eligible === ($resolved + $missing + $reconciliation),
            ],
        ];
    }

    private function roleFor(User $actor, Carbon $asOf): string
    {
        if ($this->authorization->hasInstitutionWideAuthority($actor, $asOf)) {
            return 'SUPER_ADMIN';
        }
        if ($this->authorization->hasAcademicFullAuthority($actor, $asOf)) {
            return 'WAKA_AKADEMIK';
        }
        if ($this->authorization->hasEffectiveRole($actor, 'WALI_KELAS', $asOf)) {
            return 'WALI_KELAS';
        }
        throw new AuthorizationException('Only Super Admin, Wali Kelas, and Waka Akademik may view the Academic dashboard.');
    }

    private function participantsForDashboardClasses(ClassSession $session, Collection $classes, bool $partitionByClass = true): Collection
    {
        $participants = $session->studentParticipants;
        if (! $partitionByClass || $session->scopeGroups->isEmpty()) {
            return $participants;
        }

        return $classes
            ->flatMap(fn (AcademicClass $class) => $this->rosterBreakdown->forClass($session, $participants, (string) $class->id))
            ->unique('id')
            ->values();
    }

    private function waliClasses(User $actor, Carbon $from, Carbon $to)
    {
        $staffLink = $actor->staffLink()
            ->where(fn ($query) => $query->whereNull('effective_from')->orWhereDate('effective_from', '<=', $from->toDateString()))
            ->where(fn ($query) => $query->whereNull('effective_until')->orWhereDate('effective_until', '>', $from->toDateString()))
            ->first();
        $staffId = $staffLink?->staff_id;

        return AcademicClass::query()
            ->whereHas('academicYear', fn ($query) => $query
                ->where('year_code', 'not like', '%-PILOT')
                ->whereDate('starts_on', '<=', $to->toDateString())
                ->whereDate('ends_on', '>=', $from->toDateString()))
            ->whereIn('id', ClassHomeroomAssignment::query()
                ->where('staff_id', $staffId)
                ->where('status', 'ACTIVE')
                ->whereDate('effective_from', '<=', $to->toDateString())
                ->where(fn ($query) => $query->whereNull('effective_until')->orWhereDate('effective_until', '>', $from->toDateString()))
                ->pluck('class_id'))
            ->with(['gradeLevel', 'academicYear'])->orderBy('display_name')->get();
    }
}
