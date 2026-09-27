<?php

namespace App\Domains\Academic\Services;

use App\Domains\Academic\Models\AcademicClass;
use App\Domains\Academic\Models\ClassSession;
use Illuminate\Support\Carbon;

class AttendanceSemanticMetricsService
{
    private const COUNTED_STATUSES = ['PRESENT', 'LATE', 'SICK', 'IZIN', 'EXCUSED', 'ABSENT'];

    public function forClassPeriod(AcademicClass $class, Carbon $from, Carbon $to): array
    {
        $now = Carbon::now();
        $participants = ClassSession::query()
            ->where(fn ($query) => $query
                ->where('class_id', $class->id)
                ->orWhereHas('scopeGroups', fn ($scopeQuery) => $scopeQuery->where('class_id', $class->id)))
            ->whereNotIn('session_status', ['CANCELLED', 'RESCHEDULED'])
            ->where('planned_end_at', '<=', $now)
            ->whereBetween('planned_start_at', [$from, $to])
            ->with(['scopeGroups', 'studentParticipants' => fn ($query) => $query
                ->where('participant_status', 'EXPECTED')
                ->where('is_required', true)
                ->with(['attendance', 'student.classEnrollments'])])
            ->get()
            ->flatMap(function (ClassSession $session) use ($class) {
                if ($session->scopeGroups->count() <= 1) {
                    return $session->studentParticipants;
                }

                return $session->studentParticipants->filter(fn ($participant) => $participant->student->classEnrollments->contains(fn ($enrollment) => $enrollment->class_id === $class->id
                    && $enrollment->status === 'ACTIVE'
                    && $enrollment->effective_from->lte($session->planned_start_at->toDateString())
                    && ($enrollment->effective_until === null || $enrollment->effective_until->gt($session->planned_start_at->toDateString()))
                ));
            });

        $eligible = $participants->count();
        $resolved = $participants->filter(fn ($participant) => $participant->attendance !== null
            && $participant->attendance->workflow_status === 'VALIDATED'
            && in_array($participant->attendance->attendance_status, self::COUNTED_STATUSES, true));
        $counts = collect(self::COUNTED_STATUSES)->mapWithKeys(fn (string $status) => [$status => $resolved->where('attendance.attendance_status', $status)->count()])->all();
        $resolvedCount = $resolved->count();
        $rate = static fn (int $numerator, int $denominator) => $denominator === 0 ? null : round(($numerator / $denominator) * 100, 2);

        return [
            'eligible_opportunities' => $eligible,
            'resolved_opportunities' => $resolvedCount,
            'missing_opportunities' => $eligible - $resolvedCount,
            'counts' => $counts,
            'physical_presence_rate' => $rate(($counts['PRESENT'] ?? 0) + ($counts['LATE'] ?? 0), $resolvedCount),
            'unexcused_absence_rate' => $rate($counts['ABSENT'] ?? 0, $resolvedCount),
            'completeness_rate' => $rate($resolvedCount, $eligible),
        ];
    }
}
