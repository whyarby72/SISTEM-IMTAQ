<?php

namespace App\Shared\Platform\Authorization\Services;

use App\Models\User;
use App\Shared\Platform\Authorization\Models\Feature;
use Illuminate\Support\Carbon;

class FeatureAccessResolver
{
    /** @return array{feature: ?Feature, system_enabled: bool, role_permission_allowed: bool, scope_allowed: bool, user_override: string, effective_enabled: bool, denial_reason: ?string} */
    public function resolve(User $user, string $code, ?Carbon $at = null, bool $scopeAllowed = true): array
    {
        $feature = Feature::query()->where('code', $code)->first();
        if (! $feature) {
            return ['feature' => null, 'system_enabled' => false, 'role_permission_allowed' => false, 'scope_allowed' => false, 'user_override' => 'INHERIT', 'effective_enabled' => false, 'denial_reason' => 'FEATURE_UNKNOWN'];
        }

        $rolePermissionAllowed = $feature->required_permission === null
            || $user->roleAssignments()->effectiveAt($at ?? now())->whereHas('role.permissions', fn ($query) => $query->where('code', $feature->required_permission))->exists();
        $override = $user->featureOverrides()->where('feature_id', $feature->id)->value('state') ?: 'INHERIT';
        $accountActive = ($user->status ?? 'ACTIVE') === 'ACTIVE';
        $effective = $accountActive && $feature->system_enabled && ($override === 'ENABLED' || $feature->default_enabled) && $override !== 'DISABLED' && $rolePermissionAllowed && $scopeAllowed;
        $reason = $effective ? null : ($user->status === 'DISABLED' ? 'ACCOUNT_DISABLED' : (! $feature->system_enabled ? 'SYSTEM_FEATURE_DISABLED' : ($override === 'DISABLED' ? 'USER_FEATURE_DISABLED' : (! $rolePermissionAllowed ? 'PERMISSION_DENIED' : (! $scopeAllowed ? 'SCOPE_DENIED' : 'RESOURCE_STATE_DENIED')))));

        return ['feature' => $feature, 'system_enabled' => $feature->system_enabled, 'role_permission_allowed' => $rolePermissionAllowed, 'scope_allowed' => $scopeAllowed, 'user_override' => $override, 'effective_enabled' => $effective, 'denial_reason' => $reason];
    }

    public function allowed(User $user, string $code): bool
    {
        return $this->resolve($user, $code)['effective_enabled'];
    }
}
