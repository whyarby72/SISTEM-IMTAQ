<?php

namespace Tests\Feature\Shared;

use App\Models\User;
use App\Shared\Platform\Authorization\Models\Permission;
use App\Shared\Platform\Authorization\Models\Role;
use App\Shared\Platform\Authorization\Models\UserRoleAssignment;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class CoreRbacTest extends TestCase
{
    use RefreshDatabase;

    public function test_rbac_tables_exist_and_users_has_no_role_string(): void
    {
        foreach (['roles', 'permissions', 'role_permissions', 'user_role_assignments'] as $table) {
            $this->assertTrue(Schema::hasTable($table));
        }

        $this->assertTrue(Schema::hasColumn('user_role_assignments', 'scope_type'));
        $this->assertTrue(Schema::hasColumn('user_role_assignments', 'effective_from'));
        $this->assertTrue(Schema::hasColumn('user_role_assignments', 'effective_until'));
        $this->assertFalse(Schema::hasColumn('users', 'role'));
    }

    public function test_role_permission_catalog_and_effective_assignment_are_related(): void
    {
        $user = User::factory()->create();
        $role = Role::create(['code' => 'CORE_VIEWER', 'name' => 'Core Viewer']);
        $permission = Permission::create(['code' => 'core.student.view', 'name' => 'View Student']);
        $role->permissions()->attach($permission);

        $assignment = UserRoleAssignment::create([
            'user_id' => $user->id,
            'role_id' => $role->id,
            'scope_type' => 'UNIT',
            'scope_key' => 'UNIT-CORE',
            'effective_from' => '2026-01-01',
            'effective_until' => '2027-01-01',
        ]);

        $this->assertTrue($role->permissions->contains('code', 'core.student.view'));
        $this->assertTrue($user->roleAssignments()->effectiveAt(Carbon::parse('2026-06-01'))->exists());
        $this->assertFalse($user->roleAssignments()->effectiveAt(Carbon::parse('2027-01-01'))->exists());
        $this->assertSame('UNIT-CORE', $assignment->scope_key);
    }
}
