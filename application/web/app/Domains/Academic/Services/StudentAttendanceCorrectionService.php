<?php

namespace App\Domains\Academic\Services;

use App\Domains\Academic\Models\ClassHomeroomAssignment;
use App\Domains\Academic\Models\StudentAttendance;
use App\Shared\Core\Models\Staff;
use App\Shared\Platform\Audit\Services\AuditLogger;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class StudentAttendanceCorrectionService
{
    private const CONTROLLED_STATUSES = ['PRESENT', 'LATE', 'SICK', 'EXCUSED', 'ABSENT', 'IZIN'];

    public function __construct(private readonly AuditLogger $auditLogger) {}

    public function correct(
        StudentAttendance $attendance,
        Staff $inputter,
        int $actorUserId,
        int $expectedVersion,
        string $reason,
        array $changes,
    ): StudentAttendance {
        if (blank($reason)) {
            throw new InvalidArgumentException('A correction reason is required.');
        }

        $attendance->loadMissing('participant.classSession');
        $participant = $attendance->participant;
        $session = $participant?->classSession;
        if ($participant === null || $session === null || $participant->participant_status !== 'EXPECTED') {
            throw new InvalidArgumentException('Attendance participant is not valid for correction.');
        }

        if ($attendance->workflow_status !== 'VALIDATED') {
            throw new InvalidArgumentException('Only VALIDATED attendance may be corrected in the open period.');
        }

        if (in_array($session->session_status, ['CANCELLED', 'RESCHEDULED'], true)) {
            throw new InvalidArgumentException('Cancelled or rescheduled sessions cannot be corrected.');
        }

        $date = $session->planned_start_at->toDateString();
        $isHomeroom = ClassHomeroomAssignment::query()
            ->where('class_id', $session->class_id)
            ->where('staff_id', $inputter->id)
            ->where('status', 'ACTIVE')
            ->whereDate('effective_from', '<=', $date)
            ->where(fn ($query) => $query->whereNull('effective_until')->orWhereDate('effective_until', '>', $date))
            ->exists();
        if (! $isHomeroom) {
            throw new AuthorizationException('Only the effective Wali Kelas may correct attendance.');
        }

        $allowed = ['attendance_status', 'reason_code', 'permission_event_id', 'arrival_at', 'departure_at', 'notes'];
        $updates = collect($changes)->only($allowed)->all();
        if (isset($updates['attendance_status']) && ! in_array($updates['attendance_status'], self::CONTROLLED_STATUSES, true)) {
            throw new InvalidArgumentException('Attendance status is not controlled.');
        }

        return DB::transaction(function () use ($attendance, $actorUserId, $expectedVersion, $reason, $updates): StudentAttendance {
            $locked = StudentAttendance::query()->whereKey($attendance->id)->lockForUpdate()->firstOrFail();
            if ($locked->version_no !== $expectedVersion) {
                throw new InvalidArgumentException('Attendance version is stale.');
            }
            if ($locked->workflow_status !== 'VALIDATED') {
                throw new InvalidArgumentException('Attendance is no longer VALIDATED.');
            }

            $beforeVersion = $locked->version_no;
            $oldValues = $locked->only(array_keys($updates));
            $locked->update([
                ...$updates,
                'workflow_status' => 'VALIDATED',
                'version_no' => $beforeVersion + 1,
                'updated_by' => $actorUserId,
                'updated_at' => now(),
            ]);

            $this->auditLogger->record([
                'actor_user_id' => $actorUserId,
                'action' => 'STUDENT_ATTENDANCE_CORRECTED',
                'entity_type' => StudentAttendance::class,
                'entity_id' => (string) $locked->id,
                'version_before' => $beforeVersion,
                'version_after' => $locked->version_no,
                'old_values' => $oldValues,
                'new_values' => $locked->only(array_keys($updates)),
                'reason' => $reason,
            ]);

            return $locked->fresh();
        });
    }
}
