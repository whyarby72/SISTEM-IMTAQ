<?php

namespace App\Domains\Academic\Services;

use App\Domains\Academic\Models\ClassSession;
use App\Domains\Academic\Models\SessionTeacherParticipation;
use Illuminate\Support\Facades\DB;

class TeacherParticipationRecorder
{
    public function __construct(private readonly TeacherObligationLockService $teacherObligationLock) {}

    public function ensurePrimary(ClassSession $session): SessionTeacherParticipation
    {
        return DB::transaction(function () use ($session): SessionTeacherParticipation {
            $lockedSession = ClassSession::query()->whereKey($session->id)->lockForUpdate()->firstOrFail();
            $teacherStaffId = $lockedSession->teachingAssignment->teacher_staff_id;
            $this->teacherObligationLock->lockTeachers([$teacherStaffId]);

            return SessionTeacherParticipation::firstOrCreate(
                ['class_session_id' => $lockedSession->id, 'teacher_staff_id' => $teacherStaffId],
                ['role' => 'PRIMARY', 'obligation_type' => 'TEACHING_ASSIGNMENT', 'participation_status' => 'EXPECTED']
            );
        });
    }
}
