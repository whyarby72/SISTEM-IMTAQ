<?php

namespace App\Domains\Academic\Services;

use App\Domains\Academic\Models\ClassHomeroomAssignment;
use App\Domains\Academic\Models\ClassSession;
use App\Domains\Academic\Models\SessionStudentParticipant;
use App\Domains\Academic\Models\StudentAttendance;
use App\Models\User;
use App\Shared\Core\Models\Staff;
use App\Shared\Platform\Audit\Services\AuditLogger;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class StudentAttendanceFinalizer
{
    private const CONTROLLED_STATUSES = ['PRESENT', 'LATE', 'SICK', 'EXCUSED', 'ABSENT', 'IZIN'];

    public function __construct(
        private readonly AuditLogger $auditLogger,
        private readonly SessionParticipantSnapshotter $snapshotter,
        private readonly AcademicClassScopeResolver $classScope,
        private readonly SessionAttendanceScopeResolver $attendanceScopeResolver,
        private readonly StudentAttendanceCompletenessChecker $completenessChecker,
        private readonly AttendanceScopeLockEvaluator $lockEvaluator,
    ) {}

    public function finalize(
        ClassSession $session,
        Staff $inputter,
        int $actorUserId,
        array $expectedVersions = [],
        bool $canManageAllClasses = false,
    ): ClassSession {
        // Materialize outside the finalization transaction so a later
        // validation failure does not roll the roster snapshot back.
        $this->snapshotter->ensure($session);

        return DB::transaction(function () use ($session, $inputter, $actorUserId, $expectedVersions, $canManageAllClasses): ClassSession {
            $lockedSession = ClassSession::query()->whereKey($session->id)->lockForUpdate()->firstOrFail();

            if (in_array($lockedSession->session_status, ['CANCELLED', 'RESCHEDULED'], true)) {
                throw new InvalidArgumentException('Cancelled or rescheduled sessions cannot be finalized.');
            }

            if (! in_array($lockedSession->session_status, ['PLANNED', 'CONFIRMED', 'COMPLETED'], true)) {
                throw new InvalidArgumentException('Session is not eligible for attendance finalization.');
            }

            $canonicalClassIds = array_map('strval', $this->classScope->forSession($lockedSession));
            $isJoint = count($canonicalClassIds) > 1;
            $scope = null;

            if ($isJoint) {
                $actor = User::query()->findOrFail($actorUserId);
                $scopeParticipants = $lockedSession->studentParticipants()
                    ->with('student.classEnrollments')
                    ->lockForUpdate()
                    ->get();
                $scope = $this->attendanceScopeResolver->resolve($actor, $lockedSession, $scopeParticipants);
                if (! in_array($scope['mode'], ['WALI_CLASS_PARTITION', 'FULL_SESSION'], true)) {
                    throw new AuthorizationException('Only the effective Wali Kelas may finalize student attendance.');
                }
            } elseif (! $canManageAllClasses) {
                $date = $lockedSession->planned_start_at->toDateString();
                $isHomeroom = ClassHomeroomAssignment::query()
                    ->where('class_id', $lockedSession->class_id)
                    ->where('staff_id', $inputter->id)
                    ->where('status', 'ACTIVE')
                    ->whereDate('effective_from', '<=', $date)
                    ->where(fn ($query) => $query->whereNull('effective_until')->orWhereDate('effective_until', '>', $date))
                    ->exists();

                if (! $isHomeroom) {
                    throw new AuthorizationException('Only the effective Wali Kelas may finalize student attendance.');
                }
            }

            $lockClassIds = $scope !== null && $scope['mode'] === 'WALI_CLASS_PARTITION'
                ? $scope['effective_class_ids']
                : [$lockedSession->class_id];
            if ($this->lockEvaluator->isLocked($lockClassIds, $lockedSession->planned_start_at)) {
                throw new InvalidArgumentException('Periode kehadiran sudah dikunci dan tidak dapat difinalisasi.');
            }

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
                ->with('student.classEnrollments')
                ->lockForUpdate()
                ->get();

            $targetParticipants = $scope === null
                ? $participants
                : $this->targetParticipants($participants, $scope);

            if ($targetParticipants->isEmpty()) {
                throw new InvalidArgumentException('Session has no required EXPECTED participants to finalize.');
            }

            $targetIds = $targetParticipants->map(fn (SessionStudentParticipant $participant): string => (string) $participant->id)->all();
            foreach (array_keys($expectedVersions) as $participantId) {
                if (! in_array((string) $participantId, $targetIds, true)) {
                    throw new InvalidArgumentException('Attendance participant is outside the authorized finalization scope.');
                }
            }

            $attendances = StudentAttendance::query()
                ->whereIn('session_student_participant_id', $targetIds)
                ->lockForUpdate()
                ->get()
                ->keyBy('session_student_participant_id');
            $changed = [];
            foreach ($targetParticipants as $participant) {
                $attendance = $attendances->get($participant->id);

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

            $globallyComplete = $this->completenessChecker->check($lockedSession)['is_complete'];

            if ($globallyComplete && in_array($lockedSession->session_status, ['PLANNED', 'CONFIRMED'], true)) {
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

    private function targetParticipants(Collection $participants, array $scope): Collection
    {
        $authorizedIds = array_map('strval', $scope['authorized_participant_ids']);

        return $participants->filter(fn (SessionStudentParticipant $participant): bool => in_array((string) $participant->id, $authorizedIds, true))->values();
    }
}
