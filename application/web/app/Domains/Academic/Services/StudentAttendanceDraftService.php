<?php

namespace App\Domains\Academic\Services;

use App\Domains\Academic\Models\ClassHomeroomAssignment;
use App\Domains\Academic\Models\ClassSession;
use App\Domains\Academic\Models\SessionStudentParticipant;
use App\Domains\Academic\Models\StudentAttendance;
use App\Models\User;
use App\Shared\Core\Models\Staff;
use App\Shared\Platform\Audit\Services\AuditLogger;
use App\Shared\Platform\Presentation\AcademicBusinessTime;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class StudentAttendanceDraftService
{
    public function __construct(
        private readonly AuditLogger $auditLogger,
        private readonly SessionParticipantSnapshotter $snapshotter,
        private readonly AcademicClassScopeResolver $classScope,
        private readonly SessionAttendanceScopeResolver $attendanceScopeResolver,
        private readonly AttendanceScopeLockEvaluator $lockEvaluator,
        private readonly AcademicSessionExecutionStateResolver $executionResolver,
    ) {}

    public function save(
        ClassSession $session,
        SessionStudentParticipant $participant,
        Staff $inputter,
        int $actorUserId,
        array $attributes = [],
        bool $canManageAllClasses = false,
        bool $scopeAuthorized = false,
        ?int $expectedVersion = null,
    ): StudentAttendance {
        if ((string) $participant->class_session_id !== (string) $session->id) {
            throw new InvalidArgumentException('Student participant does not belong to the session.');
        }

        if ($participant->participant_status !== 'EXPECTED') {
            throw new InvalidArgumentException('Removed participants cannot receive attendance.');
        }

        if (! $this->executionResolver->resolve($session)['attendance_input_allowed']) {
            throw new InvalidArgumentException('Pelaksanaan KBM belum dikonfirmasi; kehadiran belum dapat diisi.');
        }

        if (! in_array($session->session_status, ['PLANNED', 'CONFIRMED'], true)) {
            throw new InvalidArgumentException('Only planned or confirmed sessions can accept attendance drafts.');
        }

        $date = AcademicBusinessTime::date($session->planned_start_at);
        $scopeClassIds = [(string) $session->class_id];
        $scopeAuthorized = false;
        if (count($this->classScope->forSession($session)) > 1) {
            $scope = $this->attendanceScopeResolver->resolveForUser(User::query()->findOrFail($actorUserId), $session);
            $this->attendanceScopeResolver->assertParticipantAuthorized($scope, $participant);
            $scopeClassIds = $scope['mode'] === 'WALI_CLASS_PARTITION'
                ? array_map('strval', $scope['effective_class_ids'])
                : [(string) $session->class_id];
            $scopeAuthorized = true;
        }
        $isHomeroom = ClassHomeroomAssignment::query()
            ->where('class_id', $session->class_id)
            ->where('staff_id', $inputter->id)
            ->where('status', 'ACTIVE')
            ->whereDate('effective_from', '<=', $date)
            ->where(fn ($query) => $query->whereNull('effective_until')->orWhereDate('effective_until', '>', $date))
            ->exists();

        if (! $isHomeroom && ! $canManageAllClasses && ! $scopeAuthorized) {
            throw new AuthorizationException('Only the effective Wali Kelas may save student attendance drafts.');
        }

        // Keep roster materialization lazy, but guarantee it before a
        // participant-dependent attendance transaction begins.
        $this->snapshotter->ensure($session);
        $participant = $participant->fresh();
        if ($participant === null) {
            throw new InvalidArgumentException('Student participant does not belong to the session roster.');
        }

        $allowed = ['attendance_status', 'reason_code', 'permission_event_id', 'arrival_at', 'departure_at', 'notes'];
        $changes = collect($attributes)->only($allowed)->all();

        return DB::transaction(function () use ($session, $participant, $actorUserId, $changes, $scopeClassIds, $expectedVersion): StudentAttendance {
            $lockedSession = ClassSession::query()->whereKey($session->id)->lockForUpdate()->firstOrFail();
            if (! $this->executionResolver->resolve($lockedSession)['attendance_input_allowed']) {
                throw new InvalidArgumentException('Pelaksanaan KBM belum dikonfirmasi; kehadiran belum dapat diisi.');
            }

            if (! in_array($lockedSession->session_status, ['PLANNED', 'CONFIRMED'], true)) {
                throw new InvalidArgumentException('Only planned or confirmed sessions can accept attendance drafts.');
            }

            if ($this->lockEvaluator->isLocked($scopeClassIds, $lockedSession->planned_start_at)) {
                throw new InvalidArgumentException('Locked attendance must use the post-lock correction workflow.');
            }

            $attendance = StudentAttendance::query()
                ->where('session_student_participant_id', $participant->id)
                ->lockForUpdate()
                ->first();
            $oldValues = $attendance?->only($changes ? array_keys($changes) : ['attendance_status']);

            if ($attendance === null) {
                if ($expectedVersion !== null) {
                    throw new InvalidArgumentException('Attendance version is stale.');
                }
                $attendance = StudentAttendance::create([
                    'session_student_participant_id' => $participant->id,
                    ...$changes,
                    'workflow_status' => 'DRAFT',
                    'version_no' => 1,
                    'entered_by' => $actorUserId,
                    'entered_at' => now(),
                    'updated_by' => $actorUserId,
                    'updated_at' => now(),
                ]);
            } elseif ($attendance->workflow_status === 'DRAFT') {
                if ($expectedVersion !== null && $expectedVersion !== $attendance->version_no) {
                    throw new InvalidArgumentException('Attendance version is stale.');
                }
                $attendance->update([
                    ...$changes,
                    'workflow_status' => 'DRAFT',
                    'version_no' => $attendance->version_no + 1,
                    'updated_by' => $actorUserId,
                    'updated_at' => now(),
                ]);
            } else {
                throw new InvalidArgumentException('Attendance validated/completed must use the correction workflow.');
            }

            $this->auditLogger->record([
                'actor_user_id' => $actorUserId,
                'action' => 'STUDENT_ATTENDANCE_DRAFT_SAVED',
                'entity_type' => StudentAttendance::class,
                'entity_id' => (string) $attendance->id,
                'version_before' => $attendance->version_no - ($attendance->wasRecentlyCreated ? 1 : 1),
                'version_after' => $attendance->version_no,
                'old_values' => $oldValues,
                'new_values' => $attendance->only($changes ? array_keys($changes) : ['attendance_status']),
                'technical_metadata' => ['class_session_id' => (string) $session->id, 'participant_id' => (string) $participant->id],
            ]);

            return $attendance->fresh();
        });
    }
}
