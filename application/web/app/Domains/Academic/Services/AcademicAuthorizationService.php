<?php

namespace App\Domains\Academic\Services;

use App\Domains\Academic\Models\ClassHomeroomAssignment;
use App\Domains\Academic\Models\ClassSession;
use App\Models\User;
use App\Shared\Platform\Authorization\Models\Permission;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Carbon;

class AcademicAuthorizationService
{
    public const ACADEMIC_MANAGE = 'academic.domain.manage';

    public const INSTITUTION_MANAGE = 'platform.institution.manage';

    public function __construct(private readonly AcademicClassScopeResolver $classScope) {}

    public function hasEffectivePermission(User $user, string $permission, ?Carbon $at = null): bool
    {
        return $user->roleAssignments()
            ->effectiveAt($at)
            ->whereHas('role.permissions', fn ($query) => $query->where('code', $permission))
            ->exists();
    }

    public function hasEffectiveRole(User $user, string $roleCode, ?Carbon $at = null): bool
    {
        return $user->roleAssignments()
            ->effectiveAt($at)
            ->whereHas('role', fn ($query) => $query->where('code', $roleCode))
            ->exists();
    }

    public function hasAcademicFullAuthority(User $user, ?Carbon $at = null): bool
    {
        if ($this->authorityPermissionsAreConfigured()) {
            return $this->hasEffectivePermission($user, self::ACADEMIC_MANAGE, $at)
                || $this->hasEffectivePermission($user, self::INSTITUTION_MANAGE, $at);
        }

        return $this->hasEffectiveRole($user, 'WAKA_AKADEMIK', $at)
            || $this->hasEffectiveRole($user, 'SUPER_ADMIN', $at);
    }

    public function hasInstitutionWideAuthority(User $user, ?Carbon $at = null): bool
    {
        if ($this->authorityPermissionsAreConfigured()) {
            return $this->hasEffectivePermission($user, self::INSTITUTION_MANAGE, $at);
        }

        return $this->hasEffectiveRole($user, 'SUPER_ADMIN', $at);
    }

    public function canManageAllAcademicClasses(User $user, ?Carbon $at = null): bool
    {
        return $this->hasAcademicFullAuthority($user, $at);
    }

    public function isEffectiveWaliForSession(User $user, ClassSession $session): bool
    {
        $date = $session->planned_start_at->toDateString();
        $link = $user->staffLink()
            ->where(fn ($query) => $query->whereNull('effective_from')->orWhereDate('effective_from', '<=', $date))
            ->where(fn ($query) => $query->whereNull('effective_until')->orWhereDate('effective_until', '>', $date))
            ->first();

        if ($link === null || ! $this->hasEffectiveRole($user, 'WALI_KELAS', Carbon::parse($date))) {
            return false;
        }

        return ClassHomeroomAssignment::query()
            ->whereIn('class_id', $this->classScope->forSession($session))
            ->where('staff_id', $link->staff_id)
            ->where('status', 'ACTIVE')
            ->whereDate('effective_from', '<=', $date)
            ->where(fn ($query) => $query->whereNull('effective_until')->orWhereDate('effective_until', '>', $date))
            ->exists();
    }

    public function canManageAcademicSession(User $user, ClassSession $session): bool
    {
        return $this->hasAcademicFullAuthority($user, Carbon::parse($session->planned_start_at))
            || $this->isEffectiveWaliForSession($user, $session);
    }

    public function requireFullAcademicAuthority(User $user, ?Carbon $at = null): void
    {
        if (! $this->hasAcademicFullAuthority($user, $at)) {
            throw new AuthorizationException('Academic full authority is required.');
        }
    }

    private function authorityPermissionsAreConfigured(): bool
    {
        return Permission::query()->whereIn('code', [self::ACADEMIC_MANAGE, self::INSTITUTION_MANAGE])->exists();
    }
}
