<?php

namespace App\Domains\Academic\Services;

use App\Domains\Academic\Models\ClassHomeroomAssignment;
use App\Domains\Academic\Models\ClassSession;
use App\Models\User;
use App\Shared\Platform\Authorization\Models\UserStaffLink;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Carbon;

class AcademicAlertOwnerResolver
{
    public function resolveForSession(ClassSession $session): User
    {
        $date = $session->planned_start_at->toDateString();
        $assignment = ClassHomeroomAssignment::query()
            ->where('class_id', $session->class_id)
            ->where('status', 'ACTIVE')
            ->whereDate('effective_from', '<=', $date)
            ->where(fn ($query) => $query->whereNull('effective_until')->orWhereDate('effective_until', '>', $date))
            ->first();

        if ($assignment === null) {
            throw new AuthorizationException('Class session has no effective Wali Kelas assignment.');
        }

        $link = UserStaffLink::query()
            ->where('staff_id', $assignment->staff_id)
            ->where(fn ($query) => $query->whereNull('effective_from')->orWhereDate('effective_from', '<=', $date))
            ->where(fn ($query) => $query->whereNull('effective_until')->orWhereDate('effective_until', '>', $date))
            ->whereHas('user.roleAssignments', function ($query) use ($date): void {
                $query->effectiveAt(Carbon::parse($date))
                    ->whereHas('role', fn ($roleQuery) => $roleQuery->where('code', 'WALI_KELAS'));
            })
            ->with('user')
            ->first();

        if ($link === null || $link->user === null) {
            throw new AuthorizationException('Effective Wali Kelas has no authorized user identity.');
        }

        return $link->user;
    }
}
