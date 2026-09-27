<?php

namespace App\Domains\Academic\Services;

use App\Domains\Academic\Models\ClassSession;
use App\Domains\Academic\Models\ScheduleChange;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class CancellationService
{
    public function apply(ClassSession $session, ?int $actorUserId, string $reason): ScheduleChange
    {
        if (in_array($session->session_status, ['COMPLETED', 'CANCELLED', 'RESCHEDULED'], true)) {
            throw new InvalidArgumentException('Only active planned sessions may be cancelled.');
        }

        if ($session->studentParticipants()->whereHas('attendance')->exists()) {
            throw new InvalidArgumentException('A session with attendance data cannot be cancelled.');
        }

        return DB::transaction(function () use ($session, $actorUserId, $reason): ScheduleChange {
            $session->refresh();
            if ($session->session_status !== 'PLANNED' || $session->studentParticipants()->whereHas('attendance')->exists()) {
                throw new InvalidArgumentException('Session changed before cancellation could be applied.');
            }
            $session->update(['session_status' => 'CANCELLED']);

            return ScheduleChange::create([
                'change_code' => 'CANCEL-'.$session->id.'-'.str()->uuid(),
                'change_type' => 'CANCELLATION',
                'source_session_id' => $session->id,
                'reason' => $reason,
                'requested_by_user_id' => $actorUserId,
                'requested_at' => now(),
                'approved_by_user_id' => $actorUserId,
                'approved_at' => now(),
                'applied_by_user_id' => $actorUserId,
                'applied_at' => now(),
                'status' => 'APPLIED',
            ]);
        });
    }

    /** @return array{cancelled:int, skipped:int} */
    public function applyBulk(Collection $sessions, ?int $actorUserId, string $reason): array
    {
        return DB::transaction(function () use ($sessions, $actorUserId, $reason): array {
            $cancelled = 0;
            $skipped = 0;

            foreach ($sessions as $session) {
                $locked = ClassSession::query()->whereKey($session->id)->lockForUpdate()->first();
                if ($locked === null || $locked->session_status !== 'PLANNED' || $locked->studentParticipants()->whereHas('attendance')->exists()) {
                    $skipped++;

                    continue;
                }

                $locked->update(['session_status' => 'CANCELLED']);
                ScheduleChange::create([
                    'change_code' => 'CANCEL-'.$locked->id.'-'.str()->uuid(),
                    'change_type' => 'CANCELLATION',
                    'source_session_id' => $locked->id,
                    'reason' => $reason,
                    'requested_by_user_id' => $actorUserId,
                    'requested_at' => now(),
                    'approved_by_user_id' => $actorUserId,
                    'approved_at' => now(),
                    'applied_by_user_id' => $actorUserId,
                    'applied_at' => now(),
                    'status' => 'APPLIED',
                ]);
                $cancelled++;
            }

            return compact('cancelled', 'skipped');
        });
    }
}
