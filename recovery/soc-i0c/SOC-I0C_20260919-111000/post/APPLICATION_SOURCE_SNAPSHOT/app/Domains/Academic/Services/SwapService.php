<?php

namespace App\Domains\Academic\Services;

use App\Domains\Academic\Exceptions\ScheduleConflictException;
use App\Domains\Academic\Models\ClassSession;
use App\Domains\Academic\Models\ScheduleChange;
use App\Domains\Academic\Models\SessionTeacherParticipation;
use Illuminate\Support\Facades\DB;

class SwapService
{
    public function __construct(private readonly TeacherObligationLockService $teacherObligationLock) {}

    public function applySwap(ClassSession $first, ClassSession $second, ?int $actorUserId, string $reason): ScheduleChange
    {
        $firstTeacher = $first->teachingAssignment->teacher_staff_id;
        $secondTeacher = $second->teachingAssignment->teacher_staff_id;
        if ($firstTeacher === $secondTeacher) {
            throw new ScheduleConflictException(['conflict_type' => 'TEACHER_CONFLICT', 'resource_id' => $firstTeacher, 'severity' => 'HIGH']);
        }

        $this->assertTeacherFree($first, $secondTeacher, [$first->id, $second->id]);
        $this->assertTeacherFree($second, $firstTeacher, [$first->id, $second->id]);

        return DB::transaction(function () use ($first, $second, $firstTeacher, $secondTeacher, $actorUserId, $reason): ScheduleChange {
            $locked = ClassSession::query()->whereKey($first->id)->orWhereKey($second->id)->orderBy('id')->lockForUpdate()->get();
            if ($locked->count() !== 2) {
                throw new ScheduleConflictException(['conflict_type' => 'SESSION_CONFLICT', 'severity' => 'HIGH']);
            }

            $this->teacherObligationLock->lockTeachers([$firstTeacher, $secondTeacher]);
            $this->assertTeacherFree($locked->firstWhere('id', $first->id), $secondTeacher, [$first->id, $second->id]);
            $this->assertTeacherFree($locked->firstWhere('id', $second->id), $firstTeacher, [$first->id, $second->id]);

            $change = ScheduleChange::create([
                'change_code' => 'SWAP-'.$first->id.'-'.$second->id,
                'change_type' => 'SWAP',
                'source_session_id' => $first->id,
                'related_session_id' => $second->id,
                'original_teacher_id' => $firstTeacher,
                'replacement_teacher_id' => $secondTeacher,
                'reason' => $reason,
                'requested_by_user_id' => $actorUserId,
                'requested_at' => now(),
                'approved_by_user_id' => $actorUserId,
                'approved_at' => now(),
                'applied_by_user_id' => $actorUserId,
                'applied_at' => now(),
                'status' => 'APPLIED',
            ]);

            SessionTeacherParticipation::firstOrCreate(['class_session_id' => $first->id, 'teacher_staff_id' => $secondTeacher], ['role' => 'SUBSTITUTE', 'obligation_type' => 'REPLACEMENT', 'participation_status' => 'EXPECTED', 'schedule_change_id' => $change->id]);
            SessionTeacherParticipation::firstOrCreate(['class_session_id' => $second->id, 'teacher_staff_id' => $firstTeacher], ['role' => 'SUBSTITUTE', 'obligation_type' => 'REPLACEMENT', 'participation_status' => 'EXPECTED', 'schedule_change_id' => $change->id]);

            return $change;
        });
    }

    private function assertTeacherFree(ClassSession $target, string $teacherId, array $excludedSessionIds): void
    {
        $conflict = ClassSession::query()
            ->whereNotIn('id', $excludedSessionIds)
            ->whereIn('session_status', ['PLANNED', 'CONFIRMED', 'COMPLETED'])
            ->where('planned_start_at', '<', $target->planned_end_at)
            ->where('planned_end_at', '>', $target->planned_start_at)
            ->where(function ($query) use ($teacherId): void {
                $query->whereHas('teachingAssignment', fn ($assignment) => $assignment->where('teacher_staff_id', $teacherId))
                    ->orWhereHas('teacherParticipations', fn ($participation) => $participation
                        ->where('teacher_staff_id', $teacherId)
                        ->where('role', 'SUBSTITUTE')
                        ->where('participation_status', 'EXPECTED'));
            })
            ->first();

        if ($conflict !== null) {
            throw new ScheduleConflictException(['conflict_type' => 'TEACHER_CONFLICT', 'conflicting_session_ref' => $conflict->id, 'resource_id' => $teacherId, 'severity' => 'HIGH']);
        }
    }
}
