<?php

namespace App\Domains\Academic\Services;

use App\Domains\Academic\Models\AttendancePeriodLock;
use Illuminate\Support\Carbon;

class AttendanceScopeLockEvaluator
{
    /** @param array<int,string> $classIds */
    public function isLocked(array $classIds, Carbon $at): bool
    {
        $periodStart = $at->copy()->startOfMonth()->toDateString();

        return AttendancePeriodLock::query()
            ->whereIn('class_id', array_values(array_unique(array_map('strval', $classIds))))
            ->where('status', 'LOCKED')
            ->whereDate('period_start', $periodStart)
            ->exists();
    }
}
