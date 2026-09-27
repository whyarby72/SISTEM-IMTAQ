<?php

namespace App\Domains\Academic\Services;

use App\Domains\Academic\Models\AttendancePeriodLock;
use App\Domains\Academic\Models\ClassHomeroomAssignment;
use App\Domains\Academic\Models\ClassSession;
use App\Models\User;
use App\Shared\Platform\Audit\Services\AuditLogger;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class HistoricalHomeroomHandoverService
{
    public function __construct(
        private readonly StudentAttendanceFinalizer $finalizer,
        private readonly AuditLogger $auditLogger,
        private readonly AcademicAuthorizationService $authorization,
    ) {}

    public function complete(ClassSession $session, User $actor, string $reason, array $expectedVersions = []): ClassSession
    {
        if (trim($reason) === '') {
            throw new InvalidArgumentException('A handover reason is required.');
        }

        $sessionDate = $session->planned_start_at->toDateString();
        if ($session->planned_start_at->isFuture()) {
            throw new InvalidArgumentException('Only historical sessions may use handover completion.');
        }

        $this->authorize($actor, Carbon::parse($sessionDate));

        $assignment = ClassHomeroomAssignment::query()
            ->where('class_id', $session->class_id)
            ->where('status', 'ACTIVE')
            ->whereDate('effective_from', '<=', $sessionDate)
            ->where(fn ($query) => $query->whereNull('effective_until')->orWhereDate('effective_until', '>', $sessionDate))
            ->with('staff')
            ->first();

        if ($assignment?->staff === null) {
            throw new InvalidArgumentException('Historical session has no effective homeroom assignment.');
        }

        $periodLocked = AttendancePeriodLock::query()
            ->where('class_id', $session->class_id)
            ->whereDate('period_start', $session->planned_start_at->copy()->startOfMonth()->toDateString())
            ->whereDate('period_end', $session->planned_start_at->copy()->endOfMonth()->toDateString())
            ->where('status', 'LOCKED')
            ->exists();

        if ($periodLocked) {
            throw new InvalidArgumentException('Locked attendance must use post-lock correction workflow.');
        }

        return DB::transaction(function () use ($session, $actor, $reason, $expectedVersions, $assignment): ClassSession {
            $result = $this->finalizer->finalize($session, $assignment->staff, $actor->id, $expectedVersions);

            $this->auditLogger->record([
                'actor_user_id' => $actor->id,
                'action' => 'HISTORICAL_HOMEROOM_HANDOVER_COMPLETED',
                'entity_type' => ClassSession::class,
                'entity_id' => (string) $result->id,
                'version_before' => $session->version_no,
                'version_after' => $result->version_no,
                'old_values' => ['session_status' => $session->session_status],
                'new_values' => ['session_status' => $result->session_status, 'homeroom_staff_id' => (string) $assignment->staff_id],
                'reason' => $reason,
                'technical_metadata' => ['permission' => 'academic.student_attendance.handover_complete'],
            ]);

            return $result;
        });
    }

    private function authorize(User $actor, Carbon $asOf): void
    {
        $allowed = $this->authorization->hasInstitutionWideAuthority($actor, $asOf)
            || ($this->authorization->hasEffectivePermission($actor, 'academic.student_attendance.handover_complete', $asOf)
                && $this->authorization->hasEffectiveRole($actor, 'WAKA_AKADEMIK', $asOf));

        if (! $allowed) {
            throw new AuthorizationException('Only WAKA_AKADEMIK with handover permission may complete historical attendance.');
        }
    }
}
