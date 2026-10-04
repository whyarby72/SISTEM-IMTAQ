<?php

namespace App\Domains\Academic\Services;

use App\Domains\Academic\Models\ClassHomeroomAssignment;
use App\Domains\Academic\Models\ClassSession;
use App\Domains\Academic\Models\SessionTeacherParticipation;
use App\Models\User;
use App\Shared\Core\Models\Staff;
use App\Shared\Platform\Audit\Services\AuditLogger;
use App\Shared\Platform\Presentation\AcademicBusinessTime;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class TeacherAttendanceService
{
    public function __construct(
        private readonly AuditLogger $auditLogger,
        private readonly AcademicAuthorizationService $authorization,
        private readonly AcademicSessionExecutionStateResolver $executionResolver,
    ) {}

    public function record(
        ClassSession $session,
        SessionTeacherParticipation $participation,
        Staff $inputter,
        string $attendanceStatus,
        ?int $actorUserId = null,
        ?string $reason = null,
        bool $canManageAllClasses = false,
    ): SessionTeacherParticipation {
        return DB::transaction(function () use ($session, $participation, $inputter, $attendanceStatus, $actorUserId, $reason, $canManageAllClasses): SessionTeacherParticipation {
            $lockedSession = ClassSession::query()
                ->whereKey($session->id)
                ->lockForUpdate()
                ->firstOrFail();

            if (! $this->executionResolver->resolve($lockedSession)['attendance_obligation_exists']) {
                throw new InvalidArgumentException('Pelaksanaan KBM belum dikonfirmasi; kehadiran guru belum dapat dicatat.');
            }

            if (in_array($lockedSession->session_status, ['CANCELLED', 'COMPLETED', 'RESCHEDULED'], true)) {
                throw new InvalidArgumentException('Status sesi tidak dapat menerima perubahan kehadiran guru.');
            }

            if (! in_array($attendanceStatus, ['PRESENT', 'ABSENT', 'SICK', 'IZIN', 'OTHER'], true)) {
                throw new InvalidArgumentException('Teacher attendance must be PRESENT, ABSENT, SICK, IZIN, or OTHER.');
            }

            $lockedParticipation = SessionTeacherParticipation::query()
                ->whereKey($participation->id)
                ->where('class_session_id', $lockedSession->id)
                ->lockForUpdate()
                ->first();

            if ($lockedParticipation === null) {
                throw new InvalidArgumentException('Teacher participation does not belong to the session.');
            }

            $sessionDate = AcademicBusinessTime::date($lockedSession->planned_start_at);
            $authorized = $actorUserId !== null
                ? $this->authorization->canManageAcademicSession(User::query()->findOrFail($actorUserId), $lockedSession)
                : $canManageAllClasses || ClassHomeroomAssignment::query()
                    ->where('class_id', $lockedSession->class_id)
                    ->where('staff_id', $inputter->id)
                    ->where('status', 'ACTIVE')
                    ->whereDate('effective_from', '<=', $sessionDate)
                    ->where(fn ($query) => $query->whereNull('effective_until')->orWhereDate('effective_until', '>', $sessionDate))
                    ->exists();

            if (! $authorized) {
                throw new AuthorizationException('Only the effective Wali Kelas may record teacher attendance.');
            }

            $previousStatus = $lockedParticipation->attendance_status;

            if ($previousStatus !== null && $previousStatus !== $attendanceStatus && blank($reason)) {
                throw new InvalidArgumentException('A reason is required when correcting teacher attendance.');
            }

            $lockedParticipation->update([
                'attendance_status' => $attendanceStatus,
                'reason' => $reason ?? $lockedParticipation->reason,
            ]);

            $this->auditLogger->record([
                'actor_user_id' => $actorUserId,
                'action' => 'TEACHER_ATTENDANCE_RECORDED',
                'entity_type' => SessionTeacherParticipation::class,
                'entity_id' => (string) $lockedParticipation->id,
                'old_values' => ['attendance_status' => $previousStatus],
                'new_values' => ['attendance_status' => $attendanceStatus],
                'reason' => $reason,
                'technical_metadata' => ['class_session_id' => (string) $lockedSession->id, 'inputter_staff_id' => (string) $inputter->id],
            ]);

            return $lockedParticipation->fresh();
        });
    }
}
