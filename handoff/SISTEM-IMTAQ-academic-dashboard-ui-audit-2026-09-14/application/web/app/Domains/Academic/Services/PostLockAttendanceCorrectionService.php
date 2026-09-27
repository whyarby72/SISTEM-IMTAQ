<?php

namespace App\Domains\Academic\Services;

use App\Domains\Academic\Models\AttendancePeriodLock;
use App\Domains\Academic\Models\StudentAttendance;
use App\Models\User;
use App\Shared\Platform\Audit\Models\CorrectionRequest;
use App\Shared\Platform\Audit\Services\AuditLogger;
use App\Shared\Platform\Audit\Services\CorrectionRequestService;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class PostLockAttendanceCorrectionService
{
    private const CONTROLLED_STATUSES = ['PRESENT', 'LATE', 'SICK', 'EXCUSED', 'ABSENT', 'IZIN'];

    private const CHANGE_FIELDS = ['attendance_status', 'reason_code', 'permission_event_id', 'arrival_at', 'departure_at', 'notes'];

    public function __construct(
        private readonly WaliKelasContextResolver $waliKelasResolver,
        private readonly CorrectionRequestService $requestService,
        private readonly AuditLogger $auditLogger,
        private readonly AcademicAuthorizationService $authorization,
    ) {}

    public function submit(
        StudentAttendance $attendance,
        User $requester,
        int $expectedVersion,
        string $reason,
        array $changes,
    ): CorrectionRequest {
        $this->validateReasonAndChanges($reason, $changes);
        $attendance->loadMissing('participant.classSession');
        $session = $attendance->participant?->classSession;
        if ($session === null || $attendance->workflow_status !== 'VALIDATED') {
            throw new InvalidArgumentException('Only VALIDATED attendance may be corrected after lock.');
        }
        if ($attendance->version_no !== $expectedVersion) {
            throw new InvalidArgumentException('Attendance version is stale.');
        }
        if (! $this->isLocked($session->class_id, $session->planned_start_at)) {
            throw new InvalidArgumentException('Post-lock correction requires a locked attendance period.');
        }

        $this->waliKelasResolver->resolve($requester, $session);

        return $this->requestService->submit([
            'requested_by_user_id' => $requester->id,
            'entity_type' => StudentAttendance::class,
            'entity_id' => (string) $attendance->id,
            'correction_type' => 'POST_LOCK_ATTENDANCE',
            'requested_changes' => ['expected_version' => $expectedVersion, 'changes' => $this->filterChanges($changes)],
            'reason' => $reason,
        ]);
    }

    public function review(CorrectionRequest $request, User $reviewer, bool $approve, ?string $rejectionReason = null): CorrectionRequest
    {
        if (! $approve && blank($rejectionReason)) {
            throw new InvalidArgumentException('A rejection reason is required.');
        }

        $this->authorization->requireFullAcademicAuthority($reviewer);

        return DB::transaction(function () use ($request, $reviewer, $approve, $rejectionReason): CorrectionRequest {
            $lockedRequest = CorrectionRequest::query()->whereKey($request->id)->lockForUpdate()->firstOrFail();
            if ($lockedRequest->status !== 'PENDING' || $lockedRequest->correction_type !== 'POST_LOCK_ATTENDANCE') {
                throw new InvalidArgumentException('Correction request is not pending post-lock attendance review.');
            }

            $beforeVersion = $lockedRequest->version_no;
            $lockedRequest->update([
                'status' => $approve ? 'APPROVED' : 'REJECTED',
                'reviewed_by_user_id' => $reviewer->id,
                'reviewed_at' => now(),
                'rejection_reason' => $approve ? null : $rejectionReason,
                'version_no' => $beforeVersion + 1,
            ]);
            $this->auditLogger->record([
                'actor_user_id' => $reviewer->id,
                'action' => $approve ? 'ATTENDANCE_CORRECTION_APPROVED' : 'ATTENDANCE_CORRECTION_REJECTED',
                'entity_type' => CorrectionRequest::class,
                'entity_id' => (string) $lockedRequest->id,
                'version_before' => $beforeVersion,
                'version_after' => $lockedRequest->version_no,
                'reason' => $approve ? null : $rejectionReason,
                'new_values' => ['status' => $lockedRequest->status],
            ]);

            return $lockedRequest->fresh();
        });
    }

    public function applyApproved(CorrectionRequest $request, User $actor): StudentAttendance
    {
        $this->authorization->requireFullAcademicAuthority($actor);

        return DB::transaction(function () use ($request, $actor): StudentAttendance {
            $lockedRequest = CorrectionRequest::query()->whereKey($request->id)->lockForUpdate()->firstOrFail();
            if ($lockedRequest->status !== 'APPROVED' || $lockedRequest->correction_type !== 'POST_LOCK_ATTENDANCE') {
                throw new InvalidArgumentException('Only approved correction requests may be applied.');
            }

            $payload = $lockedRequest->requested_changes;
            $lockedAttendance = StudentAttendance::query()->whereKey($lockedRequest->entity_id)->lockForUpdate()->firstOrFail();
            $this->validateLockedAttendance($lockedAttendance, (int) $payload['expected_version']);
            $this->applyLockedAttendance($lockedAttendance, $actor, $payload['changes'], $lockedRequest->reason, $lockedRequest);
            $lockedRequest->update(['status' => 'APPLIED', 'applied_at' => now(), 'version_no' => $lockedRequest->version_no + 1]);

            return $lockedAttendance->fresh();
        });
    }

    public function superAdminOverride(
        StudentAttendance $attendance,
        User $actor,
        int $expectedVersion,
        string $reason,
        array $changes,
    ): StudentAttendance {
        $this->authorization->requireFullAcademicAuthority($actor);

        return $this->applyChanges($attendance, $actor, $expectedVersion, $reason, $changes, null, false);
    }

    public function wakaOverride(
        StudentAttendance $attendance,
        User $actor,
        int $expectedVersion,
        string $reason,
        array $changes,
    ): StudentAttendance {
        $this->authorization->requireFullAcademicAuthority($actor);

        return $this->applyChanges($attendance, $actor, $expectedVersion, $reason, $changes, null, false, 'STUDENT_ATTENDANCE_WAKA_CORRECTED');
    }

    private function applyChanges(StudentAttendance $attendance, User $actor, int $expectedVersion, string $reason, array $changes, ?CorrectionRequest $request, bool $requiresLock, string $overrideAuditAction = 'STUDENT_ATTENDANCE_SUPER_ADMIN_OVERRIDE'): StudentAttendance
    {
        $this->validateReasonAndChanges($reason, $changes);

        return DB::transaction(function () use ($attendance, $actor, $expectedVersion, $reason, $changes, $request, $requiresLock, $overrideAuditAction): StudentAttendance {
            $locked = StudentAttendance::query()->whereKey($attendance->id)->lockForUpdate()->firstOrFail();
            $locked->loadMissing('participant.classSession');
            $session = $locked->participant?->classSession;
            if ($session === null || $locked->workflow_status !== 'VALIDATED') {
                throw new InvalidArgumentException('Only VALIDATED attendance may be corrected.');
            }
            if ($this->isLocked($session->class_id, $session->planned_start_at)) {
                throw new InvalidArgumentException('Locked attendance must use post-lock correction workflow.');
            }
            if ($requiresLock && ! $this->isLocked($session->class_id, $session->planned_start_at)) {
                throw new InvalidArgumentException('Approved correction requires a locked attendance period.');
            }
            if ($requiresLock) {
                $this->validateLockedAttendance($locked, $expectedVersion);
            } else {
                $this->validateAttendanceVersion($locked, $expectedVersion);
            }
            $this->applyLockedAttendance($locked, $actor, $changes, $reason, $request, $overrideAuditAction);

            return $locked->fresh();
        });
    }

    private function validateLockedAttendance(StudentAttendance $attendance, int $expectedVersion): void
    {
        $this->validateAttendanceVersion($attendance, $expectedVersion);
        $attendance->loadMissing('participant.classSession');
        if ($attendance->participant?->classSession === null || ! $this->isLocked($attendance->participant->classSession->class_id, $attendance->participant->classSession->planned_start_at)) {
            throw new InvalidArgumentException('Approved correction requires a locked attendance period.');
        }
    }

    private function validateAttendanceVersion(StudentAttendance $attendance, int $expectedVersion): void
    {
        if ($attendance->version_no !== $expectedVersion || $attendance->workflow_status !== 'VALIDATED') {
            throw new InvalidArgumentException('Attendance changed before correction could be applied.');
        }
    }

    private function applyLockedAttendance(StudentAttendance $locked, User $actor, array $changes, string $reason, ?CorrectionRequest $request = null, string $overrideAuditAction = 'STUDENT_ATTENDANCE_SUPER_ADMIN_OVERRIDE'): void
    {
        $updates = $this->filterChanges($changes);
        $beforeVersion = $locked->version_no;
        $oldValues = $locked->only(array_keys($updates));
        $locked->update([...$updates, 'workflow_status' => 'VALIDATED', 'version_no' => $beforeVersion + 1, 'updated_by' => $actor->id, 'updated_at' => now()]);
        $this->auditLogger->record([
            'actor_user_id' => $actor->id,
            'action' => $request === null ? $overrideAuditAction : 'STUDENT_ATTENDANCE_POST_LOCK_CORRECTED',
            'entity_type' => StudentAttendance::class,
            'entity_id' => (string) $locked->id,
            'correction_request_id' => $request?->id,
            'version_before' => $beforeVersion,
            'version_after' => $locked->version_no,
            'old_values' => $oldValues,
            'new_values' => $locked->only(array_keys($updates)),
            'reason' => $reason,
        ]);
    }

    private function isLocked(string $classId, Carbon $at): bool
    {
        $start = $at->copy()->startOfMonth();

        return AttendancePeriodLock::query()->where('class_id', $classId)->where('status', 'LOCKED')->whereDate('period_start', $start)->exists();
    }

    private function validateReasonAndChanges(string $reason, array $changes): void
    {
        if (blank($reason)) {
            throw new InvalidArgumentException('A correction reason is required.');
        }
        $filtered = $this->filterChanges($changes);
        if ($filtered === []) {
            throw new InvalidArgumentException('At least one attendance field must be corrected.');
        }
        if (isset($filtered['attendance_status']) && ! in_array($filtered['attendance_status'], self::CONTROLLED_STATUSES, true)) {
            throw new InvalidArgumentException('Attendance status is not controlled.');
        }
    }

    private function filterChanges(array $changes): array
    {
        return collect($changes)->only(self::CHANGE_FIELDS)->all();
    }
}
