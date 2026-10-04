<?php

namespace Tests\Feature\Admin;

use App\Models\User;
use App\Shared\Platform\Audit\Models\AuditLog;
use App\Shared\Platform\Authorization\Models\Feature;
use App\Shared\Platform\Authorization\Models\Permission;
use App\Shared\Platform\Authorization\Models\Role;
use App\Shared\Platform\Authorization\Models\UserFeatureOverride;
use App\Shared\Platform\Authorization\Models\UserPreference;
use App\Shared\Platform\Authorization\Models\UserRoleAssignment;
use App\Shared\Platform\Authorization\Services\FeatureAccessResolver;
use Database\Seeders\UserAccessFeatureSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class UserAccessManagementTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        $role = Role::create(['code' => 'SUPER_ADMIN', 'name' => 'Super Admin']);
        $role->permissions()->attach(Permission::create(['code' => 'platform.institution.manage', 'name' => 'System settings']));
        $user = User::factory()->create(['status' => 'ACTIVE']);
        UserRoleAssignment::create(['user_id' => $user->id, 'role_id' => $role->id]);
        $this->seed(UserAccessFeatureSeeder::class);
        $role->fresh()->permissions()->syncWithoutDetaching(Permission::whereIn('code', ['platform.user.manage', 'academic.domain.manage'])->pluck('id'));

        return $user;
    }

    private function feature(string $code): Feature
    {
        return Feature::where('code', $code)->firstOrFail();
    }

    public function test_user_access_feature_seeder_bootstraps_required_permissions_and_grants_idempotently(): void
    {
        $superAdmin = Role::create(['code' => 'SUPER_ADMIN', 'name' => 'Super Admin']);
        $waka = Role::create(['code' => 'WAKA_AKADEMIK', 'name' => 'Waka Akademik']);
        $wali = Role::create(['code' => 'WALI_KELAS', 'name' => 'Wali Kelas']);

        $this->seed(UserAccessFeatureSeeder::class);
        $this->seed(UserAccessFeatureSeeder::class);

        $this->assertSame(3, Permission::whereIn('code', [
            'academic.domain.manage', 'platform.institution.manage', 'platform.user.manage',
        ])->count());
        $this->assertSame(3, $superAdmin->fresh()->permissions()->whereIn('code', [
            'academic.domain.manage', 'platform.institution.manage', 'platform.user.manage',
        ])->count());
        $this->assertSame(1, $waka->fresh()->permissions()->where('code', 'academic.domain.manage')->count());
        $this->assertSame(0, $waka->fresh()->permissions()->whereIn('code', [
            'platform.institution.manage', 'platform.user.manage',
        ])->count());
        $this->assertSame(0, $wali->fresh()->permissions()->whereIn('code', [
            'academic.domain.manage', 'platform.institution.manage', 'platform.user.manage',
        ])->count());
        $this->assertSame(14, Feature::count());
        $this->assertSame(3, $superAdmin->fresh()->permissions()->whereIn('code', [
            'academic.domain.manage', 'platform.institution.manage', 'platform.user.manage',
        ])->get()->unique('id')->count());
        $this->assertSame(9, Feature::whereNotNull('required_permission')->count());
        $this->assertSame([], Feature::whereNotNull('required_permission')->pluck('required_permission')->unique()->diff(
            Permission::pluck('code')
        )->values()->all());

        $admin = User::factory()->create(['status' => 'ACTIVE']);
        UserRoleAssignment::create(['user_id' => $admin->id, 'role_id' => $superAdmin->id]);
        foreach (['academic.students', 'platform.user_access', 'platform.system_settings'] as $featureCode) {
            $this->assertTrue(app(FeatureAccessResolver::class)->resolve($admin, $featureCode)['effective_enabled']);
        }
        $this->assertFalse((bool) config('academic.ai.assistant_enabled'));
    }

    public function test_unauthenticated_user_is_denied(): void
    {
        $this->get(route('admin.system.users.index'))->assertRedirect(route('login'));
    }

    public function test_unauthorized_authenticated_user_is_denied(): void
    {
        $this->actingAs(User::factory()->create())->get(route('admin.system.users.index'))->assertForbidden();
    }

    public function test_authorized_super_admin_opens_user_access(): void
    {
        $this->actingAs($this->admin())->get(route('admin.system.users.index'))->assertOk()->assertSee('User &amp; Akses', false);
    }

    public function test_user_create_succeeds_and_requires_first_login_change(): void
    {
        $admin = $this->admin();
        $role = Role::where('code', 'SUPER_ADMIN')->firstOrFail();
        $response = $this->actingAs($admin)->post(route('admin.system.users.store'), ['name' => 'New User', 'email' => 'new@example.test', 'password' => 'temporary-password-123', 'role_id' => $role->id, 'scope_type' => 'INSTITUTION']);
        $created = User::where('email', 'new@example.test')->firstOrFail();
        $response->assertRedirect(route('admin.system.users.show', $created));
        $this->assertTrue((bool) $created->must_change_password);
    }

    public function test_invalid_staff_link_is_rejected(): void
    {
        $this->actingAs($this->admin())->post(route('admin.system.users.store'), ['name' => 'Invalid', 'email' => 'invalid@example.test', 'password' => 'temporary-password-123', 'role_id' => Role::where('code', 'SUPER_ADMIN')->value('id'), 'scope_type' => 'INSTITUTION', 'staff_id' => 'not-a-uuid'])->assertSessionHasErrors('staff_id');
    }

    public function test_disabled_account_cannot_log_in(): void
    {
        $user = User::factory()->create(['email' => 'disabled@example.test', 'password' => Hash::make('correct-password'), 'status' => 'DISABLED']);
        $this->post(route('login.store'), ['email' => $user->email, 'password' => 'correct-password'])->assertSessionHasErrors('email');
    }

    public function test_existing_session_of_newly_disabled_account_is_denied(): void
    {
        $user = $this->admin();
        $this->actingAs($user);
        $user->forceFill(['status' => 'DISABLED'])->save();
        $this->get(route('admin.system.users.index'))->assertForbidden();
    }

    public function test_last_active_super_admin_cannot_be_disabled(): void
    {
        $admin = $this->admin();
        $this->actingAs($admin)->put(route('admin.system.users.account.update', $admin), ['name' => $admin->name, 'email' => $admin->email, 'status' => 'DISABLED'])->assertForbidden();
        $this->assertSame('ACTIVE', $admin->fresh()->status);
    }

    public function test_self_demotion_is_blocked(): void
    {
        $admin = $this->admin();
        $role = Role::create(['code' => 'WALI_KELAS', 'name' => 'Wali Kelas']);
        $this->actingAs($admin)->post(route('admin.system.users.roles.update', $admin), ['role_id' => $role->id, 'scope_type' => 'INSTITUTION'])->assertForbidden();
    }

    public function test_role_assignment_with_valid_scope_passes(): void
    {
        $admin = $this->admin();
        $target = User::factory()->create();
        $role = Role::where('code', 'SUPER_ADMIN')->firstOrFail();
        $this->actingAs($admin)->post(route('admin.system.users.roles.update', $target), ['role_id' => $role->id, 'scope_type' => 'INSTITUTION', 'effective_from' => '2026-10-01', 'effective_until' => '2026-11-01'])->assertRedirect();
        $this->assertDatabaseHas('user_role_assignments', ['user_id' => $target->id, 'scope_type' => 'INSTITUTION']);
    }

    public function test_invalid_scope_type_is_rejected(): void
    {
        $admin = $this->admin();
        $target = User::factory()->create();
        $role = Role::where('code', 'SUPER_ADMIN')->firstOrFail();
        $this->actingAs($admin)->post(route('admin.system.users.roles.update', $target), ['role_id' => $role->id, 'scope_type' => 'UNKNOWN'])->assertSessionHasErrors('scope_type');
    }

    public function test_invalid_scope_key_is_rejected(): void
    {
        $admin = $this->admin();
        $target = User::factory()->create();
        $role = Role::where('code', 'SUPER_ADMIN')->firstOrFail();
        $this->actingAs($admin)->post(route('admin.system.users.roles.update', $target), ['role_id' => $role->id, 'scope_type' => 'ASSIGNED_CLASS', 'scope_key' => '00000000-0000-0000-0000-000000000000'])->assertSessionHasErrors('scope_key');
    }

    public function test_role_revocation_preserves_history(): void
    {
        $admin = $this->admin();
        $target = User::factory()->create();
        $role = Role::where('code', 'SUPER_ADMIN')->firstOrFail();
        $assignment = UserRoleAssignment::create(['user_id' => $target->id, 'role_id' => $role->id, 'scope_type' => 'INSTITUTION']);
        $this->actingAs($admin)->delete(route('admin.system.users.roles.revoke', [$target, $assignment]))->assertRedirect();
        $this->assertNotNull($assignment->fresh()->effective_until);
        $this->assertDatabaseHas('user_role_assignments', ['id' => $assignment->id]);
    }

    public function test_disabled_feature_hides_sidebar_menu(): void
    {
        $admin = $this->admin();
        $feature = $this->feature('academic.students');
        UserFeatureOverride::create(['user_id' => $admin->id, 'feature_id' => $feature->id, 'state' => 'DISABLED', 'version_no' => 1]);
        $this->actingAs($admin)->get(route('academic.dashboard'))->assertOk()->assertDontSee('aria-label="Santri"', false);
    }

    public function test_disabled_feature_denies_direct_route(): void
    {
        $admin = $this->admin();
        $feature = $this->feature('academic.students');
        UserFeatureOverride::create(['user_id' => $admin->id, 'feature_id' => $feature->id, 'state' => 'DISABLED', 'version_no' => 1]);
        $this->actingAs($admin)->get(route('admin.academic.students.index'))->assertForbidden();
    }

    public function test_enabled_feature_cannot_bypass_missing_permission(): void
    {
        $user = User::factory()->create();
        $this->seed(UserAccessFeatureSeeder::class);
        $feature = $this->feature('academic.students');
        UserFeatureOverride::create(['user_id' => $user->id, 'feature_id' => $feature->id, 'state' => 'ENABLED', 'version_no' => 1]);
        $this->actingAs($user)->get(route('admin.academic.students.index'))->assertForbidden();
    }

    public function test_enabled_feature_cannot_bypass_business_scope(): void
    {
        $admin = $this->admin();
        $result = app(FeatureAccessResolver::class)->resolve($admin, 'academic.dashboard', null, false);
        $this->assertFalse($result['effective_enabled']);
    }

    public function test_inherit_preserves_normal_baseline(): void
    {
        $this->assertTrue(app(FeatureAccessResolver::class)->resolve($this->admin(), 'academic.dashboard')['effective_enabled']);
    }

    public function test_preference_whitelist_is_enforced(): void
    {
        $admin = $this->admin();
        $target = User::factory()->create();
        $this->actingAs($admin)->put(route('admin.system.users.preferences.update', $target), ['default_landing_page' => 'academic.dashboard', 'table_page_size' => '25', 'evil' => 'secret'])->assertRedirect();
        $this->assertDatabaseMissing('user_preferences', ['user_id' => $target->id, 'preference_key' => 'evil']);
    }

    public function test_default_landing_preference_is_applied_safely(): void
    {
        $admin = $this->admin();
        UserPreference::create(['user_id' => $admin->id, 'preference_key' => 'default_landing_page', 'preference_value' => ['value' => 'admin.academic.dashboard']]);
        $this->post(route('login.store'), ['email' => $admin->email, 'password' => 'password'])->assertRedirect(route('admin.academic.dashboard'));
    }

    public function test_inaccessible_landing_preference_falls_back(): void
    {
        $admin = $this->admin();
        UserPreference::create(['user_id' => $admin->id, 'preference_key' => 'default_landing_page', 'preference_value' => ['value' => 'academic.dashboard']]);
        UserFeatureOverride::create(['user_id' => $admin->id, 'feature_id' => $this->feature('academic.dashboard')->id, 'state' => 'DISABLED', 'version_no' => 1]);
        $this->post(route('login.store'), ['email' => $admin->email, 'password' => 'password'])->assertRedirect(route('admin.academic.dashboard'));
    }

    public function test_table_page_size_changes_pagination(): void
    {
        $admin = $this->admin();
        UserPreference::create(['user_id' => $admin->id, 'preference_key' => 'table_page_size', 'preference_value' => ['value' => 10]]);
        User::factory()->count(12)->create();
        $this->actingAs($admin)->get(route('admin.system.users.index'))->assertOk()->assertSee('page=2');
    }

    public function test_sidebar_preference_changes_presentation(): void
    {
        $admin = $this->admin();
        UserPreference::create(['user_id' => $admin->id, 'preference_key' => 'sidebar_compact', 'preference_value' => ['value' => true]]);
        $this->actingAs($admin)->get(route('academic.dashboard'))->assertOk()->assertSee('is-compact');
    }

    public function test_audit_excludes_password_token_and_secret(): void
    {
        $admin = $this->admin();
        $this->actingAs($admin)->post(route('admin.system.users.store'), ['name' => 'Audited', 'email' => 'audit@example.test', 'password' => 'never-log-this-password', 'role_id' => Role::where('code', 'SUPER_ADMIN')->value('id'), 'scope_type' => 'INSTITUTION']);
        $audit = AuditLog::latest('occurred_at')->first();
        $this->assertStringNotContainsString('never-log-this-password', json_encode($audit?->toArray()));
    }

    public function test_feature_batch_update_rolls_back_on_invalid_feature(): void
    {
        $admin = $this->admin();
        $feature = $this->feature('academic.dashboard');
        $this->actingAs($admin)->put(route('admin.system.users.features.update', $admin), ['features' => [$feature->id => 'DISABLED', '00000000-0000-0000-0000-000000000000' => 'ENABLED']])->assertSessionHasErrors('features');
        $this->assertDatabaseMissing('user_feature_overrides', ['user_id' => $admin->id, 'feature_id' => $feature->id]);
    }

    public function test_idor_assignment_mismatch_is_denied(): void
    {
        $admin = $this->admin();
        $first = User::factory()->create();
        $second = User::factory()->create();
        $role = Role::where('code', 'SUPER_ADMIN')->firstOrFail();
        $assignment = UserRoleAssignment::create(['user_id' => $first->id, 'role_id' => $role->id]);
        $this->actingAs($admin)->delete(route('admin.system.users.roles.revoke', [$second, $assignment]))->assertNotFound();
    }

    public function test_temporary_password_is_enforced_at_login(): void
    {
        $user = User::factory()->create(['password' => Hash::make('temporary-password-123'), 'must_change_password' => true]);
        $this->post(route('login.store'), ['email' => $user->email, 'password' => 'temporary-password-123'])->assertRedirect(route('password.change'));
    }

    public function test_password_change_clears_temporary_flag(): void
    {
        $user = User::factory()->create(['password' => Hash::make('temporary-password-123'), 'must_change_password' => true]);
        $this->actingAs($user)->put(route('password.update'), ['current_password' => 'temporary-password-123', 'password' => 'new-secure-password-123', 'password_confirmation' => 'new-secure-password-123'])->assertRedirect();
        $this->assertFalse((bool) $user->fresh()->must_change_password);
    }

    public function test_scope_dates_must_be_ordered(): void
    {
        $admin = $this->admin();
        $target = User::factory()->create();
        $role = Role::where('code', 'SUPER_ADMIN')->firstOrFail();
        $this->actingAs($admin)->post(route('admin.system.users.roles.update', $target), ['role_id' => $role->id, 'scope_type' => 'INSTITUTION', 'effective_from' => '2026-11-01', 'effective_until' => '2026-10-01'])->assertSessionHasErrors('effective_until');
    }
}
