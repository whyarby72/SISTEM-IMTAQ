<?php

namespace App\Domains\Academic\Services;

use App\Domains\Academic\Exceptions\ScheduleConflictException;
use App\Domains\Academic\Models\ClassSession;
use App\Domains\Academic\Models\ScheduleChange;
use App\Domains\Academic\Models\SessionTeacherParticipation;
use App\Models\User;
use App\Shared\Core\Models\Staff;
use App\Shared\Platform\Audit\Services\AuditLogger;
use Illuminate\Support\Facades\DB;

class SubstitutionService
{
    public function __construct(
        private readonly AcademicAuthorizationService $authorization,
        private readonly AuditLogger $auditLogger,
        private readonly TeacherObligationLockService $teacherObligationLock,
    ) {}

    public function applySubstitution(ClassSession $session, Staff $replacementTeacher, ?int $actorUserId, string $reason): ScheduleChange
    {
        $session->loadMissing('teachingAssignment');
        $sourceTeacherId = $session->teachingAssignment->teacher_staff_id;
        if ($sourceTeacherId === $replacementTeacher->id) {
            throw $this->teacherConflict($replacementTeacher->id);
        }

        if ($this->conflict($session, $replacementTeacher->id) !== null) {
            throw $this->teacherConflict($replacementTeacher->id);
        }

        return DB::transaction(function () use ($session, $replacementTeacher, $sourceTeacherId, $actorUserId, $reason): ScheduleChange {
            $lockedSession = ClassSession::query()
                ->whereKey($session->id)
                ->lockForUpdate()
                ->firstOrFail();

            if (in_array($lockedSession->session_status, ['CANCELLED', 'COMPLETED', 'RESCHEDULED'], true)) {
                throw new ScheduleConflictException(['conflict_type' => 'SESSION_NOT_WRITABLE', 'resource_id' => $lockedSession->id, 'severity' => 'HIGH']);
            }

            if ($actorUserId !== null) {
                $this->authorization->requireFullAcademicAuthority(User::query()->findOrFail($actorUserId), $lockedSession->planned_start_at);
            }

            $this->teacherObligationLock->lockTeachers([$replacementTeacher->id]);

            if ($this->conflict($lockedSession, $replacementTeacher->id) !== null) {
                throw $this->teacherConflict($replacementTeacher->id);
            }

            $change = ScheduleChange::create([
                'change_code' => 'SUB-'.$lockedSession->id.'-'.str()->uuid(),
                'change_type' => 'SUBSTITUTION',
                'source_session_id' => $lockedSession->id,
                'original_teacher_id' => $sourceTeacherId,
                'replacement_teacher_id' => $replacementTeacher->id,
                'reason' => $reason,
                'requested_by_user_id' => $actorUserId,
                'requested_at' => now(),
                'approved_by_user_id' => $actorUserId,
                'approved_at' => now(),
                'applied_by_user_id' => $actorUserId,
                'applied_at' => now(),
                'status' => 'APPLIED',
            ]);

            SessionTeacherParticipation::firstOrCreate(
                ['class_session_id' => $lockedSession->id, 'teacher_staff_id' => $replacementTeacher->id],
                ['role' => 'SUBSTITUTE', 'obligation_type' => 'REPLACEMENT', 'participation_status' => 'EXPECTED', 'schedule_change_id' => $change->id]
            );

            $this->auditLogger->record([
                'actor_user_id' => $actorUserId,
                'action' => 'TEACHER_SUBSTITUTION_APPLIED',
                'entity_type' => ScheduleChange::class,
                'entity_id' => (string) $change->id,
                'new_values' => ['source_session_id' => (string) $lockedSession->id, 'replacement_teacher_id' => (string) $replacementTeacher->id],
                'reason' => $reason,
            ]);

            return $change;
        });
    }

    private function conflict(ClassSession $session, string $teacherId): ?ClassSession
    {
        return ClassSession::query()
            ->whereKeyNot($session->id)
            ->whereIn('session_status', ['PLANNED', 'CONFIRMED', 'COMPLETED'])
            ->where('planned_start_at', '<', $session->planned_end_at)
            ->where('planned_end_at', '>', $session->planned_start_at)
            ->where(function ($query) use ($teacherId): void {
                $query->whereHas('teachingAssignment', fn ($assignment) => $assignment->where('teacher_staff_id', $teacherId))
                    ->orWhereHas('teacherParticipations', fn ($participation) => $participation
                        ->where('teacher_staff_id', $teacherId)
                        ->where('role', 'SUBSTITUTE')
                        ->where('participation_status', 'EXPECTED'));
            })
            ->first();
    }

    private function teacherConflict(string $teacherId): ScheduleConflictException
    {
        return new ScheduleConflictException(['conflict_type' => 'TEACHER_CONFLICT', 'resource_id' => $teacherId, 'severity' => 'HIGH']);
    }
}
