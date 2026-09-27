<?php

namespace App\Domains\Academic\Services;

use App\Domains\Academic\Exceptions\ScheduleConflictException;
use App\Domains\Academic\Models\ClassSession;
use App\Domains\Academic\Models\ScheduleChange;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class RescheduleService
{
    public function __construct(private readonly TeacherObligationLockService $teacherObligationLock) {}

    public function apply(ClassSession $source, Carbon|string $newStart, Carbon|string $newEnd, ?int $actorUserId, string $reason, string $changeType = 'RESCHEDULE'): ScheduleChange
    {
        $start = Carbon::parse($newStart);
        $end = Carbon::parse($newEnd);
        $conflict = ClassSession::query()
            ->whereKeyNot($source->id)
            ->whereIn('session_status', ['PLANNED', 'CONFIRMED', 'COMPLETED'])
            ->where('planned_start_at', '<', $end)
            ->where('planned_end_at', '>', $start)
            ->where(function ($query) use ($source): void {
                $query->where('class_id', $source->class_id)
                    ->orWhereHas('teachingAssignment', fn ($q) => $q->where('teacher_staff_id', $source->teachingAssignment->teacher_staff_id));
            })
            ->first();

        if ($conflict !== null) {
            throw new ScheduleConflictException(['conflict_type' => $conflict->class_id === $source->class_id ? 'CLASS_CONFLICT' : 'TEACHER_CONFLICT', 'conflicting_session_ref' => $conflict->id, 'severity' => 'HIGH']);
        }

        return DB::transaction(function () use ($source, $start, $end, $actorUserId, $reason, $changeType): ScheduleChange {
            $lockedSource = ClassSession::query()->whereKey($source->id)->lockForUpdate()->firstOrFail();
            $teacherId = $lockedSource->teachingAssignment->teacher_staff_id;
            $this->teacherObligationLock->lockTeachers([$teacherId]);
            $conflict = $this->conflict($lockedSource, $start, $end);
            if ($conflict !== null) {
                throw new ScheduleConflictException(['conflict_type' => $conflict->class_id === $lockedSource->class_id ? 'CLASS_CONFLICT' : 'TEACHER_CONFLICT', 'conflicting_session_ref' => $conflict->id, 'severity' => 'HIGH']);
            }

            $lockedSource->update(['session_status' => 'RESCHEDULED']);
            $replacement = ClassSession::create([
                'session_code' => 'RESCHEDULED-'.$lockedSource->id.'-'.str()->uuid(),
                'teaching_assignment_id' => $lockedSource->teaching_assignment_id,
                'class_id' => $lockedSource->class_id,
                'subject_id' => $lockedSource->subject_id,
                'location_id' => $lockedSource->location_id,
                'planned_start_at' => $start,
                'planned_end_at' => $end,
                'session_source' => 'RESCHEDULED',
                'participant_scope' => $source->participant_scope,
                'session_status' => 'PLANNED',
                'rescheduled_from_session_id' => $lockedSource->id,
            ]);

            return ScheduleChange::create([
                'change_code' => 'RESCHEDULE-'.$source->id.'-'.str()->uuid(),
                'change_type' => $changeType,
                'source_session_id' => $lockedSource->id,
                'related_session_id' => $replacement->id,
                'new_start_at' => $start,
                'new_end_at' => $end,
                'reason' => $reason,
                'requested_by_user_id' => $actorUserId,
                'requested_at' => now(),
                'approved_by_user_id' => $actorUserId,
                'approved_at' => now(),
                'applied_by_user_id' => $actorUserId,
                'applied_at' => now(),
                'status' => 'APPLIED',
            ]);
        });
    }

    private function conflict(ClassSession $source, Carbon $start, Carbon $end): ?ClassSession
    {
        return ClassSession::query()
            ->whereKeyNot($source->id)
            ->whereIn('session_status', ['PLANNED', 'CONFIRMED', 'COMPLETED'])
            ->where('planned_start_at', '<', $end)
            ->where('planned_end_at', '>', $start)
            ->where(function ($query) use ($source): void {
                $query->where('class_id', $source->class_id)
                    ->orWhereHas('teachingAssignment', fn ($q) => $q->where('teacher_staff_id', $source->teachingAssignment->teacher_staff_id))
                    ->orWhereHas('teacherParticipations', fn ($q) => $q->where('teacher_staff_id', $source->teachingAssignment->teacher_staff_id)->where('role', 'SUBSTITUTE')->where('participation_status', 'EXPECTED'));
            })
            ->first();
    }
}
