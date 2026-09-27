<?php

namespace App\Domains\Academic\Services;

use App\Domains\Academic\Models\ClassSession;
use App\Domains\Academic\Models\ScheduleChange;
use App\Domains\Academic\Models\ScheduleRule;
use Illuminate\Support\Facades\DB;

class ScheduleRuleArchiveService
{
    /** @return array{cancelled:int, preserved:int} */
    public function archive(ScheduleRule $rule, ?int $actorUserId, string $reason): array
    {
        return DB::transaction(function () use ($rule, $actorUserId, $reason): array {
            $lockedRule = ScheduleRule::query()->whereKey($rule->id)->lockForUpdate()->firstOrFail();
            $lockedRule->update(['workflow_status' => 'ARCHIVED', 'version_no' => $lockedRule->version_no + 1]);
            $sessions = ClassSession::query()->where('schedule_rule_id', $lockedRule->id)->whereIn('session_status', ['PLANNED', 'CONFIRMED'])->lockForUpdate()->get();
            $cancelled = 0;
            $preserved = 0;
            foreach ($sessions as $session) {
                if ($session->studentParticipants()->whereHas('attendance')->exists()) {
                    $preserved++;

                    continue;
                }
                $session->update(['session_status' => 'CANCELLED']);
                ScheduleChange::create([
                    'change_code' => 'ARCHIVE-CANCEL-'.$session->id.'-'.str()->uuid(), 'change_type' => 'SCHEDULE_ARCHIVE',
                    'source_session_id' => $session->id, 'reason' => $reason,
                    'requested_by_user_id' => $actorUserId, 'requested_at' => now(), 'approved_by_user_id' => $actorUserId,
                    'approved_at' => now(), 'applied_by_user_id' => $actorUserId, 'applied_at' => now(), 'status' => 'APPLIED',
                ]);
                $cancelled++;
            }

            return compact('cancelled', 'preserved');
        });
    }
}
