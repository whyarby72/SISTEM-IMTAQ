<?php

namespace Tests\Feature\Shared;

use App\Models\User;
use App\Shared\Platform\Authorization\Models\Permission;
use App\Shared\Platform\Authorization\Models\Role;
use App\Shared\Platform\Authorization\Models\UserRoleAssignment;
use Database\Seeders\ConsolidateAcademicRolesSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ConsolidateAcademicRolesSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_authority_bootstrap_is_idempotent_additive_and_non_destructive(): void
    {
        $superAdminRole = Role::create(['code' => 'SUPER_ADMIN', 'name' => 'Super Admin']);
        $adminRole = Role::create(['code' => 'ADMIN_AKADEMIK', 'name' => 'Legacy Academic Admin']);
        $waliRole = Role::create(['code' => 'WALI_KELAS', 'name' => 'Wali Kelas']);
        $unrelatedPermission = Permission::create(['code' => 'unrelated.permission', 'name' => 'Unrelated permission']);
        $adminRole->permissions()->attach($unrelatedPermission->id);

        $admin = User::factory()->create(['email' => 'admin.existing@example.test']);
        $pilot = User::factory()->create(['email' => 'azhar.pilot@example.test']);
        $adminAssignment = UserRoleAssignment::create(['user_id' => $admin->id, 'role_id' => $adminRole->id]);
        $pilotAssignment = UserRoleAssignment::create(['user_id' => $pilot->id, 'role_id' => $waliRole->id]);
        UserRoleAssignment::create(['user_id' => $pilot->id, 'role_id' => $adminRole->id]);

        $this->seed(ConsolidateAcademicRolesSeeder::class);
        $this->seed(ConsolidateAcademicRolesSeeder::class);

        $wakaRole = Role::query()->where('code', 'WAKA_AKADEMIK')->firstOrFail();
        $this->assertSame(1, Permission::query()->where('code', 'academic.domain.manage')->count());
        $this->assertSame(1, Permission::query()->where('code', 'platform.institution.manage')->count());
        $this->assertTrue($wakaRole->permissions()->where('code', 'academic.domain.manage')->exists());
        $this->assertTrue($superAdminRole->fresh()->permissions()->where('code', 'platform.institution.manage')->exists());

        $this->assertDatabaseHas('roles', ['id' => $adminRole->id, 'code' => 'ADMIN_AKADEMIK']);
        $this->assertDatabaseHas('role_permissions', ['role_id' => $adminRole->id, 'permission_id' => $unrelatedPermission->id]);
        $this->assertDatabaseHas('user_role_assignments', ['id' => $adminAssignment->id]);
        $this->assertDatabaseHas('user_role_assignments', ['id' => $pilotAssignment->id]);
        $this->assertDatabaseHas('user_role_assignments', ['user_id' => $pilot->id, 'role_id' => $adminRole->id]);
        $this->assertDatabaseMissing('user_role_assignments', ['user_id' => $pilot->id, 'role_id' => $wakaRole->id]);
    }
}
