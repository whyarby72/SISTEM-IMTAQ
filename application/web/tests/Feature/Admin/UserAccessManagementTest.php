<?php

namespace Tests\Feature\Admin;

use App\Models\User;
use App\Shared\Platform\Authorization\Models\Feature;
use App\Shared\Platform\Authorization\Models\Permission;
use App\Shared\Platform\Authorization\Models\Role;
use App\Shared\Platform\Authorization\Models\UserRoleAssignment;
use Database\Seeders\UserAccessFeatureSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserAccessManagementTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        $role = Role::create(['code' => 'SUPER_ADMIN', 'name' => 'Super Admin']);
        $permission = Permission::create(['code' => 'platform.institution.manage', 'name' => 'System settings']);
        $role->permissions()->attach($permission);
        $user = User::factory()->create(['status' => 'ACTIVE']);
        UserRoleAssignment::create(['user_id' => $user->id, 'role_id' => $role->id]);
        $this->seed(UserAccessFeatureSeeder::class);
        $role->fresh()->permissions()->syncWithoutDetaching(Permission::where('code', 'platform.user.manage')->value('id'));
        return $user;
    }

    public function test_user_access_requires_server_side_feature_permission(): void
    {
        $user = User::factory()->create(['status' => 'ACTIVE']);
        $this->seed(UserAccessFeatureSeeder::class);
        $this->actingAs($user)->get(route('admin.system.users.index'))->assertForbidden();
    }

    public function test_super_admin_can_open_user_access_and_feature_resolver_keeps_permission_boundary(): void
    {
        $admin = $this->admin();
        $this->actingAs($admin)->get(route('admin.system.users.index'))->assertOk()->assertSee('User &amp; Akses', false);
        $feature = Feature::where('code', 'platform.user_access')->firstOrFail();
        $this->assertTrue(app(\App\Shared\Platform\Authorization\Services\FeatureAccessResolver::class)->resolve($admin, $feature->code)['effective_enabled']);
    }

    public function test_last_active_super_admin_cannot_be_disabled(): void
    {
        $admin = $this->admin();
        $this->actingAs($admin)->put(route('admin.system.users.account.update', $admin), ['name' => $admin->name, 'email' => $admin->email, 'status' => 'DISABLED'])->assertForbidden();
        $this->assertSame('ACTIVE', $admin->fresh()->status);
    }
}
