<?php

namespace App\Domains\Academic\Services;

use App\Domains\Academic\Models\ClassHomeroomAssignment;
use App\Domains\Academic\Models\ClassSession;
use App\Models\User;
use App\Shared\Core\Models\Staff;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Carbon;

class WaliKelasContextResolver
{
    public function __construct(private readonly AcademicAuthorizationService $authorization) {}

    public function resolve(User $user, ClassSession $session): Staff
    {
        $date = $session->planned_start_at->toDateString();
        $link = $user->staffLink()
            ->where(fn ($query) => $query->whereNull('effective_from')->orWhereDate('effective_from', '<=', $date))
            ->where(fn ($query) => $query->whereNull('effective_until')->orWhereDate('effective_until', '>', $date))
            ->with('staff')
            ->first();

        if ($link === null || $link->staff === null) {
            throw new AuthorizationException('Authenticated user has no effective Staff identity.');
        }

        if ($this->canManageAllClasses($user, $date)) {
            return $link->staff;
        }

        $hasRole = $this->authorization->hasEffectiveRole($user, 'WALI_KELAS', Carbon::parse($date));
        $hasHomeroom = ClassHomeroomAssignment::query()
            ->where('class_id', $session->class_id)
            ->where('staff_id', $link->staff_id)
            ->where('status', 'ACTIVE')
            ->whereDate('effective_from', '<=', $date)
            ->where(fn ($query) => $query->whereNull('effective_until')->orWhereDate('effective_until', '>', $date))
            ->exists();

        if (! $hasRole || ! $hasHomeroom) {
            throw new AuthorizationException('Authenticated user is not the effective Wali Kelas for this session.');
        }

        return $link->staff;
    }

    public function canManageAllClasses(User $user, string $date): bool
    {
        return $this->authorization->canManageAllAcademicClasses($user, Carbon::parse($date));
    }

    public function homeroomStaff(ClassSession $session): ?Staff
    {
        return ClassHomeroomAssignment::query()
            ->with('staff')
            ->where('class_id', $session->class_id)
            ->where('status', 'ACTIVE')
            ->whereDate('effective_from', '<=', $session->planned_start_at->toDateString())
            ->where(fn ($query) => $query->whereNull('effective_until')->orWhereDate('effective_until', '>', $session->planned_start_at->toDateString()))
            ->first()?->staff;
    }
}
