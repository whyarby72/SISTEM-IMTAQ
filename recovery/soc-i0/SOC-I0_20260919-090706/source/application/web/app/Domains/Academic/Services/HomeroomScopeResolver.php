<?php

namespace App\Domains\Academic\Services;

use App\Domains\Academic\Models\ClassHomeroomAssignment;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

class HomeroomScopeResolver
{
    public function classIdsForStaff(string $staffId, Carbon|string $at): Collection
    {
        $date = $at instanceof Carbon ? $at->toDateString() : $at;

        return ClassHomeroomAssignment::query()
            ->where('staff_id', $staffId)
            ->where('status', 'ACTIVE')
            ->whereDate('effective_from', '<=', $date)
            ->where(function ($query) use ($date): void {
                $query->whereNull('effective_until')
                    ->orWhereDate('effective_until', '>', $date);
            })
            ->pluck('class_id');
    }
}
