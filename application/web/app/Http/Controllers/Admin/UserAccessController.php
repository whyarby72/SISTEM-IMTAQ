<?php

namespace App\Http\Controllers\Admin;

use App\Http\Services\RoleScopeValidator;
use App\Models\User;
use App\Shared\Core\Models\Staff;
use App\Shared\Platform\Audit\Services\AuditLogger;
use App\Shared\Platform\Authorization\Models\Feature;
use App\Shared\Platform\Authorization\Models\Role;
use App\Shared\Platform\Authorization\Models\UserFeatureOverride;
use App\Shared\Platform\Authorization\Models\UserPreference;
use App\Shared\Platform\Authorization\Models\UserRoleAssignment;
use App\Shared\Platform\Authorization\Models\UserStaffLink;
use App\Shared\Platform\Authorization\Services\FeatureAccessResolver;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\DatabaseManager;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class UserAccessController
{
    public function index(Request $request): View
    {
        $this->authorize($request);
        $pageSize = (int) $this->preference($request->user(), 'table_page_size', 20);
        $users = User::query()->with(['roles', 'staffLink.staff'])->orderBy('name')->paginate(in_array($pageSize, [10, 20, 25, 50], true) ? $pageSize : 20);

        return view('admin.system.users.index', ['users' => $users, 'showHelpText' => (bool) $this->preference($request->user(), 'show_help_text', true)]);
    }

    public function create(Request $request): View
    {
        $this->authorize($request);

        return view('admin.system.users.create', [
            'staff' => Staff::query()->whereDoesntHave('userLink')->orderBy('full_name')->get(),
            'roles' => Role::query()->orderBy('name')->get(),
            'showHelpText' => (bool) $this->preference($request->user(), 'show_help_text', true),
        ]);
    }

    public function store(Request $request, DatabaseManager $db, RoleScopeValidator $scopeValidator): RedirectResponse
    {
        $this->authorize($request);
        $payload = $request->validate([
            'name' => ['required', 'string', 'max:255'], 'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:12'], 'staff_id' => ['nullable', 'uuid', 'exists:staff,id'],
            'role_id' => ['required', 'uuid', 'exists:roles,id'], 'scope_type' => ['required', 'string', 'max:80'],
            'scope_key' => ['nullable', 'string', 'max:120'], 'effective_from' => ['nullable', 'date'], 'effective_until' => ['nullable', 'date'],
        ]);
        if (! empty($payload['staff_id']) && UserStaffLink::query()->where('staff_id', $payload['staff_id'])->exists()) {
            throw ValidationException::withMessages(['staff_id' => 'Staff sudah ditautkan ke akun lain.']);
        }
        $scope = $scopeValidator->validate($payload);
        $user = $db->transaction(function () use ($payload, $scope, $request): User {
            $user = User::create(['name' => $payload['name'], 'email' => $payload['email'], 'password' => $payload['password']]);
            $user->forceFill(['must_change_password' => true])->save();
            if (! empty($payload['staff_id'])) {
                UserStaffLink::create(['user_id' => $user->id, 'staff_id' => $payload['staff_id'], 'linked_by_user_id' => $request->user()->id]);
            }
            UserRoleAssignment::create(['user_id' => $user->id, 'role_id' => $payload['role_id'], ...$scope, 'assignment_reason' => 'SUPER_ADMIN_USER_ACCESS_SETUP']);
            $this->audit($request, 'USER_ACCOUNT_CREATED', 'User', (string) $user->id, [], ['status' => 'ACTIVE', 'scope_type' => $scope['scope_type']], 'Account provisioned');

            return $user;
        });

        return to_route('admin.system.users.show', $user)->with('status', 'Akun berhasil dibuat. User wajib mengganti password saat login pertama.');
    }

    public function show(Request $request, User $user): View
    {
        $this->authorize($request);
        $user->load(['roles', 'roleAssignments.role', 'staffLink.staff', 'featureOverrides.feature', 'preferences']);

        return view('admin.system.users.show', [
            'managedUser' => $user, 'features' => Feature::query()->orderBy('module')->orderBy('sort_order')->get(),
            'roles' => Role::query()->orderBy('name')->get(),
            'staff' => Staff::query()->where(fn ($q) => $q->whereDoesntHave('userLink')->orWhereHas('userLink', fn ($link) => $link->where('user_id', $user->id)))->orderBy('full_name')->get(),
            'resolver' => app(FeatureAccessResolver::class),
            'showHelpText' => (bool) $this->preference($request->user(), 'show_help_text', true),
        ]);
    }

    public function updateAccount(Request $request, User $user, DatabaseManager $db): RedirectResponse
    {
        $this->authorize($request);
        $payload = $request->validate([
            'name' => ['required', 'string', 'max:255'], 'email' => ['required', 'email', 'max:255', 'unique:users,email,'.$user->id],
            'status' => ['required', 'in:ACTIVE,DISABLED'], 'staff_id' => ['nullable', 'uuid', 'exists:staff,id'], 'password' => ['nullable', 'string', 'min:12'],
        ]);
        if ($payload['status'] === 'DISABLED' && $user->is($request->user())) {
            throw new AuthorizationException('Anda tidak dapat menonaktifkan akun sendiri.');
        }
        if ($payload['status'] === 'DISABLED' && $this->isLastActiveSuperAdmin($user)) {
            throw new AuthorizationException('Super Admin aktif terakhir tidak dapat dinonaktifkan.');
        }
        if (! empty($payload['staff_id']) && $payload['staff_id'] !== $user->staffLink?->staff_id && UserStaffLink::query()->where('staff_id', $payload['staff_id'])->where('user_id', '!=', $user->id)->exists()) {
            throw ValidationException::withMessages(['staff_id' => 'Staff sudah ditautkan ke akun lain.']);
        }
        $before = ['name' => $user->name, 'email' => $user->email, 'status' => $user->status ?? 'ACTIVE', 'staff_id' => $user->staffLink?->staff_id];
        $db->transaction(function () use ($payload, $user, $request, $before): void {
            $user->forceFill(['name' => $payload['name'], 'email' => $payload['email'], 'status' => $payload['status']])->save();
            if (! empty($payload['password'])) {
                $user->forceFill(['password' => Hash::make($payload['password']), 'must_change_password' => true])->save();
                $this->audit($request, 'USER_PASSWORD_RESET_INITIATED', 'User', (string) $user->id, ['status' => $before['status']], ['must_change_password' => true], 'Temporary password issued');
            }
            if (array_key_exists('staff_id', $payload) && $payload['staff_id'] !== $before['staff_id']) {
                UserStaffLink::query()->where('user_id', $user->id)->delete();
                if ($payload['staff_id']) {
                    UserStaffLink::create(['user_id' => $user->id, 'staff_id' => $payload['staff_id'], 'linked_by_user_id' => $request->user()->id]);
                }
            }
            $this->audit($request, $payload['status'] === 'DISABLED' ? 'USER_ACCOUNT_DISABLED' : 'USER_ACCOUNT_UPDATED', 'User', (string) $user->id, $before, ['name' => $payload['name'], 'email' => $payload['email'], 'status' => $payload['status'], 'staff_id' => $payload['staff_id'] ?? null], 'Account change');
        });

        return back()->with('status', 'Akun berhasil diperbarui.');
    }

    public function updateRole(Request $request, User $user, RoleScopeValidator $scopeValidator, DatabaseManager $db): RedirectResponse
    {
        $this->authorize($request);
        $payload = $request->validate([
            'role_id' => ['required', 'uuid', 'exists:roles,id'], 'scope_type' => ['required', 'string', 'max:80'], 'scope_key' => ['nullable', 'string', 'max:120'],
            'effective_from' => ['nullable', 'date'], 'effective_until' => ['nullable', 'date'],
        ]);
        $scope = $scopeValidator->validate($payload);
        $role = Role::findOrFail($payload['role_id']);
        if ($user->is($request->user()) && $role->code !== 'SUPER_ADMIN') {
            throw new AuthorizationException('Anda tidak dapat menurunkan otoritas akun sendiri.');
        }
        if ($user->is($request->user()) && $this->isLastActiveSuperAdmin($user)) {
            throw new AuthorizationException('Super Admin aktif terakhir tidak dapat diturunkan.');
        }
        $db->transaction(function () use ($scope, $role, $user, $request): void {
            $assignment = UserRoleAssignment::create(['user_id' => $user->id, 'role_id' => $role->id, ...$scope, 'assignment_reason' => 'SUPER_ADMIN_ACCESS_MANAGEMENT']);
            $this->audit($request, 'USER_ROLE_GRANTED', 'UserRoleAssignment', (string) $assignment->id, [], ['user_id' => $user->id, 'role' => $role->code, ...$scope], 'Role assignment granted');
        });

        return back()->with('status', 'Assignment role baru berhasil ditambahkan.');
    }

    public function revokeRole(Request $request, User $user, UserRoleAssignment $assignment, DatabaseManager $db): RedirectResponse
    {
        $this->authorize($request);
        abort_unless($assignment->user_id === $user->id, 404);
        if ($user->is($request->user())) {
            throw new AuthorizationException('Self-demotion harus dilakukan oleh administrator lain.');
        }
        if ($assignment->role()->where('code', 'SUPER_ADMIN')->exists() && $this->isLastActiveSuperAdmin($user)) {
            throw new AuthorizationException('Super Admin aktif terakhir tidak dapat dicabut.');
        }
        $before = ['effective_until' => $assignment->effective_until?->toDateString(), 'version_no' => $assignment->version_no];
        $db->transaction(function () use ($assignment, $request, $before): void {
            $assignment->forceFill(['effective_until' => now()->toDateString(), 'version_no' => $assignment->version_no + 1])->save();
            $this->audit($request, 'USER_ROLE_REVOKED', 'UserRoleAssignment', (string) $assignment->id, $before, ['effective_until' => now()->toDateString(), 'version_no' => $assignment->version_no], 'Role assignment revoked');
        });

        return back()->with('status', 'Assignment role dinonaktifkan tanpa menghapus riwayat.');
    }

    public function updateFeatures(Request $request, User $user, DatabaseManager $db): RedirectResponse
    {
        $this->authorize($request);
        $validated = $request->validate(['features' => ['nullable', 'array'], 'features.*' => ['in:INHERIT,ENABLED,DISABLED'], 'feature_reasons' => ['nullable', 'array'], 'feature_reasons.*' => ['nullable', 'string', 'max:1000']]);
        $states = $validated['features'] ?? [];
        $features = Feature::query()->whereIn('id', array_keys($states))->get()->keyBy('id');
        if ($features->count() !== count($states)) {
            throw ValidationException::withMessages(['features' => 'Feature tidak ditemukan.']);
        }
        $reasons = $validated['feature_reasons'] ?? [];
        $db->transaction(function () use ($states, $features, $reasons, $user, $request): void {
            foreach ($states as $featureId => $state) {
                $feature = $features->get($featureId);
                if (! $feature->is_toggleable) {
                    continue;
                }
                $override = UserFeatureOverride::query()->where(['user_id' => $user->id, 'feature_id' => $featureId])->lockForUpdate()->first();
                $before = $override ? ['state' => $override->state, 'version_no' => $override->version_no, 'reason' => $override->reason] : [];
                $attributes = ['state' => $state, 'reason' => $reasons[$featureId] ?? null, 'changed_by_user_id' => $request->user()->id, 'version_no' => ($override?->version_no ?? 0) + 1];
                if ($override) {
                    $override->update($attributes);
                } else {
                    $override = UserFeatureOverride::create(['user_id' => $user->id, 'feature_id' => $featureId, ...$attributes]);
                }
                $this->audit($request, 'USER_FEATURE_OVERRIDE_CHANGED', 'UserFeatureOverride', (string) $override->id, $before, ['user_id' => $user->id, 'feature' => $feature->code, 'state' => $state, 'version_no' => $attributes['version_no']], $attributes['reason']);
            }
        });

        return back()->with('status', 'Akses fitur berhasil diperbarui.');
    }

    public function updatePreferences(Request $request, User $user): RedirectResponse
    {
        $this->authorize($request);
        $values = $request->validate(['default_landing_page' => ['required', 'in:academic.dashboard,admin.academic.dashboard'], 'table_page_size' => ['required', 'in:10,25,50']]);
        $values['sidebar_compact'] = $request->boolean('sidebar_compact');
        $values['show_help_text'] = $request->boolean('show_help_text');
        $before = $user->preferences()->get()->mapWithKeys(fn ($preference) => [$preference->preference_key => $preference->preference_value['value'] ?? null])->all();
        foreach ($values as $key => $value) {
            UserPreference::updateOrCreate(['user_id' => $user->id, 'preference_key' => $key], ['preference_value' => ['value' => $value], 'updated_by_user_id' => $request->user()->id]);
        }
        $this->audit($request, 'USER_PREFERENCES_CHANGED', 'User', (string) $user->id, $before, $values, 'User preferences changed');

        return back()->with('status', 'Preferensi berhasil disimpan.');
    }

    private function authorize(Request $request): void
    {
        abort_unless($request->user() && app(FeatureAccessResolver::class)->allowed($request->user(), 'platform.user_access'), 403);
    }

    private function isLastActiveSuperAdmin(User $user): bool
    {
        return $user->roleAssignments()->effectiveAt(now())->whereHas('role', fn ($q) => $q->where('code', 'SUPER_ADMIN'))->exists()
            && User::query()->where('status', 'ACTIVE')->whereHas('roleAssignments', fn ($q) => $q->effectiveAt(now())->whereHas('role', fn ($role) => $role->where('code', 'SUPER_ADMIN')))->count() <= 1;
    }

    private function preference(User $user, string $key, mixed $default): mixed
    {
        $value = $user->preferences()->where('preference_key', $key)->first()?->preference_value;

        return is_array($value) && array_key_exists('value', $value) ? $value['value'] : $default;
    }

    private function audit(Request $request, string $action, string $type, string $id, array $before, array $after, ?string $reason = null): void
    {
        app(AuditLogger::class)->record(['actor_user_id' => $request->user()->id, 'action' => $action, 'entity_type' => $type, 'entity_id' => $id, 'old_values' => $before, 'new_values' => $after, 'technical_metadata' => ['reason' => $reason]]);
    }
}
