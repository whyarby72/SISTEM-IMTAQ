<?php

namespace App\Domains\Academic\Services;

use App\Domains\Academic\Models\ClassHomeroomAssignment;
use App\Domains\Academic\Models\ClassSession;
use App\Domains\Academic\Models\SessionStudentParticipant;
use App\Domains\Academic\Models\StudentAttendance;
use App\Shared\Core\Models\Staff;
use App\Shared\Platform\Audit\Services\AuditLogger;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class StudentAttendanceFinalizer
{
    private const CONTROLLED_STATUSES = ['PRESENT', 'LATE', 'SICK', 'EXCUSED', 'ABSENT', 'IZIN'];

    public function __construct(private readonly AuditLogger $auditLogger) {}

    public function finalize(
        ClassSession $session,
        Staff $inputter,
        int $actorUserId,
        array $expectedVersions = [],
        bool $canManageAllClasses = false,
    ): ClassSession {
        if (in_array($session->session_status, ['CANCELLED', 'RESCHEDULED'], true)) {
            throw new InvalidArgumentException('Cancelled or rescheduled sessions cannot be finalized.');
        }

        if (! in_array($session->session_status, ['PLANNED', 'CONFIRMED', 'COMPLETED'], true)) {
            throw new InvalidArgumentException('Session is not eligible for attendance finalization.');
        }

        $date = $session->planned_start_at->toDateString();
        $isHomeroom = ClassHomeroomAssignment::query()
            ->where('class_id', $session->class_id)
            ->where('staff_id', $inputter->id)
            ->where('status', 'ACTIVE')
            ->whereDate('effective_from', '<=', $date)
            ->where(fn ($query) => $query->whereNull('effective_until')->orWhereDate('effective_until', '>', $date))
            ->exists();

        if (! $isHomeroom && ! $canManageAllClasses) {
            throw new AuthorizationException('Only the effective Wali Kelas may finalize student attendance.');
        }

        return DB::transaction(function () use ($session, $actorUserId, $expectedVersions): ClassSession {
            $lockedSession = ClassSession::query()->whereKey($session->id)->lockForUpdate()->firstOrFail();
            $primaryTeacher = $lockedSession->teacherParticipations()
                ->where('role', 'PRIMARY')
                ->where('participation_status', 'EXPECTED')
                ->lockForUpdate()
                ->first();

            if ($primaryTeacher === null) {
                throw new InvalidArgumentException('Guru utama sesi belum tercatat; sesi belum dapat disahkan.');
            }

            if ($primaryTeacher->attendance_status === null) {
                throw new InvalidArgumentException('Status kehadiran guru utama harus dicatat sebelum kehadiran santri disahkan.');
            }

            if (! in_array($primaryTeacher->attendance_status, ['PRESENT', 'ABSENT', 'SICK', 'IZIN', 'OTHER'], true)) {
                throw new InvalidArgumentException('Status kehadiran guru utama tidak valid.');
            }

            if (in_array($primaryTeacher->attendance_status, ['ABSENT', 'SICK', 'IZIN', 'OTHER'], true)) {
                $hasPresentSubstitute = $lockedSession->teacherParticipations()
                    ->where('role', 'SUBSTITUTE')
                    ->where('participation_status', 'EXPECTED')
                    ->where('attendance_status', 'PRESENT')
                    ->lockForUpdate()
                    ->exists();

                if (! $hasPresentSubstitute) {
                    throw new InvalidArgumentException('Guru utama tidak hadir. Pilih guru pengganti atau Wali Kelas dan catat hadir sebelum mengesahkan absensi santri.');
                }
            }

            $participants = SessionStudentParticipant::query()
                ->where('class_session_id', $lockedSession->id)
                ->where('participant_status', 'EXPECTED')
                ->where('is_required', true)
                ->lockForUpdate()
                ->get();

            if ($participants->isEmpty()) {
                throw new InvalidArgumentException('Session has no required EXPECTED participants to finalize.');
            }

            $changed = [];
            foreach ($participants as $participant) {
                $attendance = StudentAttendance::query()
                    ->where('session_student_participant_id', $participant->id)
                    ->lockForUpdate()
                    ->first();

                if ($attendance === null || $attendance->attendance_status === null) {
                    throw new InvalidArgumentException('All required EXPECTED participants must have attendance status.');
                }

                if (! in_array($attendance->attendance_status, self::CONTROLLED_STATUSES, true)) {
                    throw new InvalidArgumentException('Attendance status is not controlled.');
                }

                if (array_key_exists($participant->id, $expectedVersions)
                    && (int) $expectedVersions[$participant->id] !== $attendance->version_no) {
                    throw new InvalidArgumentException('Attendance version is stale.');
                }

                if ($attendance->workflow_status !== 'DRAFT' && $attendance->workflow_status !== 'VALIDATED') {
                    throw new InvalidArgumentException('Attendance workflow status is invalid.');
                }

                if ($attendance->workflow_status === 'DRAFT') {
                    $beforeVersion = $attendance->version_no;
                    $attendance->update([
                        'workflow_status' => 'VALIDATED',
                        'version_no' => $beforeVersion + 1,
                        'finalized_by' => $actorUserId,
                        'finalized_at' => now(),
                        'updated_by' => $actorUserId,
                        'updated_at' => now(),
                    ]);
                    $changed[] = [$attendance->fresh(), $beforeVersion];
                }
            }

            if (in_array($lockedSession->session_status, ['PLANNED', 'CONFIRMED'], true)) {
                $beforeVersion = $lockedSession->version_no;
                $lockedSession->update([
                    'session_status' => 'COMPLETED',
                    'version_no' => $beforeVersion + 1,
                ]);
            }

            foreach ($changed as [$attendance, $beforeVersion]) {
                $this->auditLogger->record([
                    'actor_user_id' => $actorUserId,
                    'action' => 'STUDENT_ATTENDANCE_FINALIZED',
                    'entity_type' => StudentAttendance::class,
                    'entity_id' => (string) $attendance->id,
                    'version_before' => $beforeVersion,
                    'version_after' => $attendance->version_no,
                    'old_values' => ['workflow_status' => 'DRAFT'],
                    'new_values' => ['workflow_status' => 'VALIDATED', 'attendance_status' => $attendance->attendance_status],
                    'technical_metadata' => ['class_session_id' => (string) $lockedSession->id],
                ]);
            }

            return $lockedSession->fresh();
        });
    }
}
