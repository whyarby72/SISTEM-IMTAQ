<?php

namespace App\Http\Controllers\Admin;

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
use Illuminate\View\View;

class UserAccessController
{
    public function index(Request $request): View
    {
        $this->authorize($request);
        $users = User::query()->with(['roles', 'staffLink.staff'])->orderBy('name')->paginate(20);

        return view('admin.system.users.index', compact('users'));
    }

    public function create(Request $request): View
    {
        $this->authorize($request);

        return view('admin.system.users.create', ['staff' => Staff::query()->whereDoesntHave('userLink')->orderBy('full_name')->get(), 'roles' => Role::query()->orderBy('name')->get()]);
    }

    public function store(Request $request, DatabaseManager $db): RedirectResponse
    {
        $this->authorize($request);
        $payload = $request->validate(['name' => ['required', 'string', 'max:255'], 'email' => ['required', 'email', 'max:255', 'unique:users,email'], 'password' => ['required', 'string', 'min:12'], 'staff_id' => ['nullable', 'uuid', 'exists:staff,id'], 'role_id' => ['required', 'uuid', 'exists:roles,id'], 'scope_type' => ['required', 'string', 'max:80'], 'scope_key' => ['nullable', 'string', 'max:120']]);
        $user = $db->transaction(function () use ($payload, $request): User {
            $user = User::create(['name' => $payload['name'], 'email' => $payload['email'], 'password' => $payload['password']]);
            if (! empty($payload['staff_id'])) {
                UserStaffLink::create(['user_id' => $user->id, 'staff_id' => $payload['staff_id'], 'linked_by_user_id' => $request->user()->id]);
            }
            UserRoleAssignment::create(['user_id' => $user->id, 'role_id' => $payload['role_id'], 'scope_type' => $payload['scope_type'], 'scope_key' => $payload['scope_key'] ?? null, 'assignment_reason' => 'SUPER_ADMIN_USER_ACCESS_SETUP']);

            return $user;
        });
        $this->audit($request, 'USER_ACCOUNT_CREATED', 'User', (string) $user->id, ['status' => 'ACTIVE']);

        return to_route('admin.system.users.show', $user)->with('status', 'Akun berhasil dibuat.');
    }

    public function show(Request $request, User $user): View
    {
        $this->authorize($request);
        $user->load(['roles', 'roleAssignments.role', 'staffLink.staff', 'featureOverrides.feature', 'preferences']);

        return view('admin.system.users.show', ['managedUser' => $user, 'features' => Feature::query()->orderBy('module')->orderBy('sort_order')->get(), 'roles' => Role::query()->orderBy('name')->get(), 'staff' => Staff::query()->where(fn ($q) => $q->whereDoesntHave('userLink')->orWhereHas('userLink', fn ($link) => $link->where('user_id', $user->id)))->orderBy('full_name')->get(), 'resolver' => app(FeatureAccessResolver::class)]);
    }

    public function updateAccount(Request $request, User $user, DatabaseManager $db): RedirectResponse
    {
        $this->authorize($request);
        $payload = $request->validate(['name' => ['required', 'string', 'max:255'], 'email' => ['required', 'email', 'max:255', 'unique:users,email,'.$user->id], 'status' => ['required', 'in:ACTIVE,DISABLED'], 'staff_id' => ['nullable', 'uuid', 'exists:staff,id'], 'password' => ['nullable', 'string', 'min:12']]);
        if ($payload['status'] === 'DISABLED' && $user->is($request->user())) {
            throw new AuthorizationException('Anda tidak dapat menonaktifkan akun sendiri.');
        }
        if ($payload['status'] === 'DISABLED' && $this->isLastActiveSuperAdmin($user)) {
            throw new AuthorizationException('Super Admin aktif terakhir tidak dapat dinonaktifkan.');
        }
        $db->transaction(function () use ($payload, $user, $request): void {
            $user->forceFill(['name' => $payload['name'], 'email' => $payload['email'], 'status' => $payload['status']])->save();
            if (! empty($payload['password'])) {
                $user->forceFill(['password' => Hash::make($payload['password'])])->save();
                $this->audit($request, 'USER_PASSWORD_RESET_INITIATED', 'User', (string) $user->id, ['initiated' => true]);
            }
            if (array_key_exists('staff_id', $payload)) {
                UserStaffLink::query()->where('user_id', $user->id)->delete();
                if ($payload['staff_id']) {
                    UserStaffLink::create(['user_id' => $user->id, 'staff_id' => $payload['staff_id'], 'linked_by_user_id' => $request->user()->id]);
                }
                $this->audit($request, 'USER_STAFF_LINK_CHANGED', 'User', (string) $user->id, ['staff_id_changed' => true]);
            }
        });
        $this->audit($request, $payload['status'] === 'DISABLED' ? 'USER_ACCOUNT_DISABLED' : 'USER_ACCOUNT_UPDATED', 'User', (string) $user->id, ['status' => $payload['status']]);

        return back()->with('status', 'Akun berhasil diperbarui.');
    }

    public function updateRole(Request $request, User $user): RedirectResponse
    {
        $this->authorize($request);
        $payload = $request->validate(['role_id' => ['required', 'uuid', 'exists:roles,id'], 'scope_type' => ['required', 'string', 'max:80'], 'scope_key' => ['nullable', 'string', 'max:120']]);
        $role = Role::findOrFail($payload['role_id']);
        if ($user->is($request->user()) && $role->code !== 'SUPER_ADMIN') {
            throw new AuthorizationException('Anda tidak dapat menghapus otoritas akun sendiri.');
        }
        if ($user->is($request->user()) && $this->isLastActiveSuperAdmin($user)) {
            throw new AuthorizationException('Super Admin aktif terakhir tidak dapat diturunkan.');
        }
        UserRoleAssignment::create(['user_id' => $user->id, 'role_id' => $role->id, 'scope_type' => $payload['scope_type'], 'scope_key' => $payload['scope_key'] ?: null, 'assignment_reason' => 'SUPER_ADMIN_ACCESS_MANAGEMENT']);
        $this->audit($request, 'USER_ROLE_GRANTED', 'User', (string) $user->id, ['role' => $role->code, 'scope_type' => $payload['scope_type'], 'scope_key' => $payload['scope_key'] ?: null]);

        return back()->with('status', 'Assignment role baru berhasil ditambahkan.');
    }

    public function revokeRole(Request $request, User $user, UserRoleAssignment $assignment): RedirectResponse
    {
        $this->authorize($request);
        abort_unless($assignment->user_id === $user->id, 404);
        if ($assignment->role()->where('code', 'SUPER_ADMIN')->exists() && $this->isLastActiveSuperAdmin($user)) {
            throw new AuthorizationException('Super Admin aktif terakhir tidak dapat dicabut.');
        }
        $assignment->forceFill(['effective_until' => now()->toDateString(), 'version_no' => $assignment->version_no + 1])->save();
        $this->audit($request, 'USER_ROLE_REVOKED', 'UserRoleAssignment', (string) $assignment->id, ['user_id' => $user->id]);

        return back()->with('status', 'Assignment role dinonaktifkan tanpa menghapus riwayat.');
    }

    public function updateFeatures(Request $request, User $user): RedirectResponse
    {
        $this->authorize($request);
        $states = $request->validate(['features' => ['nullable', 'array'], 'features.*' => ['in:INHERIT,ENABLED,DISABLED']])['features'] ?? [];
        foreach ($states as $featureId => $state) {
            $feature = Feature::findOrFail($featureId);
            if (! $feature->is_toggleable) {
                continue;
            }
            UserFeatureOverride::updateOrCreate(['user_id' => $user->id, 'feature_id' => $feature->id], ['state' => $state, 'changed_by_user_id' => $request->user()->id, 'version_no' => 1]);
            $this->audit($request, 'USER_FEATURE_OVERRIDE_CHANGED', 'Feature', (string) $feature->id, ['user_id' => $user->id, 'state' => $state]);
        }

        return back()->with('status', 'Akses fitur berhasil diperbarui.');
    }

    public function updatePreferences(Request $request, User $user): RedirectResponse
    {
        $this->authorize($request);
        $values = $request->validate(['default_landing_page' => ['required', 'in:academic.dashboard,admin.academic.dashboard'], 'sidebar_compact' => ['boolean'], 'table_page_size' => ['required', 'in:10,25,50'], 'show_help_text' => ['boolean']]);
        foreach ($values as $key => $value) {
            UserPreference::updateOrCreate(['user_id' => $user->id, 'preference_key' => $key], ['preference_value' => ['value' => $value], 'updated_by_user_id' => $request->user()->id]);
        }
        $this->audit($request, 'USER_PREFERENCES_CHANGED', 'User', (string) $user->id, ['keys' => array_keys($values)]);

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

    private function audit(Request $request, string $action, string $type, string $id, array $values): void
    {
        app(AuditLogger::class)->record(['actor_user_id' => $request->user()->id, 'action' => $action, 'entity_type' => $type, 'entity_id' => $id, 'new_values' => $values]);
    }
}
