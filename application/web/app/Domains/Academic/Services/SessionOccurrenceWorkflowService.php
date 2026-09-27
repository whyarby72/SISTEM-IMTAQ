<?php

namespace App\Domains\Academic\Services;

use App\Domains\Academic\Models\ClassSession;
use App\Domains\Academic\Models\ScheduleChange;
use App\Domains\Academic\Models\SessionOccurrenceVersion;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class SessionOccurrenceWorkflowService
{
    public function __construct(
        private readonly CanonicalSessionOccurrenceService $occurrence,
        private readonly CancellationService $cancellation,
        private readonly RescheduleService $reschedule,
    ) {}

    public function cancel(ClassSession $session, User $actor, string $reason): SessionOccurrenceVersion
    {
        return DB::transaction(function () use ($session, $actor, $reason): SessionOccurrenceVersion {
            $this->cancellation->apply($session, $actor->id, $reason);

            return $this->occurrence->record($session->fresh(), $actor, 'CANCELLED', ['reason' => $reason]);
        });
    }

    /** @return array{change:ScheduleChange,version:SessionOccurrenceVersion} */
    public function reschedule(ClassSession $session, User $actor, Carbon|string $newStart, Carbon|string $newEnd, string $reason): array
    {
        return DB::transaction(function () use ($session, $actor, $newStart, $newEnd, $reason): array {
            $change = $this->reschedule->apply($session, $newStart, $newEnd, $actor->id, $reason);
            $version = $this->occurrence->record($session->fresh(), $actor, 'RESCHEDULED', [
                'reason' => $reason,
                'replacement_class_session_id' => $change->related_session_id,
            ]);

            return compact('change', 'version');
        });
    }
}
