<?php

namespace Tests\Feature\Admin;

use App\Models\User;
use App\Shared\Core\Models\Staff;
use App\Shared\Platform\Authorization\Models\Role;
use App\Shared\Platform\Authorization\Models\UserRoleAssignment;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StaffAdminTest extends TestCase
{
    use RefreshDatabase;

    public function test_academic_admin_can_view_and_create_staff(): void
    {
        $user = User::factory()->create();
        $role = Role::create(['code' => 'WAKA_AKADEMIK', 'name' => 'Waka Akademik']);
        UserRoleAssignment::create(['user_id' => $user->id, 'role_id' => $role->id, 'effective_from' => '2026-07-01']);

        $this->actingAs($user)->get(route('admin.academic.staff.index'))->assertOk()->assertSee('Master Guru/Staf');
        $this->actingAs($user)->post(route('admin.academic.staff.store'), [
            'staff_code' => 'STAFF-ADMIN-01', 'full_name' => 'Guru Admin 01', 'active_from' => '2026-07-01',
        ])->assertRedirect(route('admin.academic.staff.index'));

        $this->assertDatabaseHas('staff', ['staff_code' => 'STAFF-ADMIN-01', 'full_name' => 'Guru Admin 01']);
    }

    public function test_non_admin_cannot_manage_staff(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user)->get(route('admin.academic.staff.index'))->assertForbidden();
    }

    public function test_operational_staff_list_excludes_pilot_history_without_deleting_it(): void
    {
        $user = User::factory()->create();
        $role = Role::create(['code' => 'WAKA_AKADEMIK', 'name' => 'Waka Akademik']);
        UserRoleAssignment::create(['user_id' => $user->id, 'role_id' => $role->id, 'effective_from' => '2026-07-01']);
        Staff::create(['staff_code' => 'AKH', 'full_name' => 'Akhen', 'record_status' => 'ACTIVE']);
        Staff::create(['staff_code' => 'PILOT-AZHAR', 'full_name' => 'Azhar', 'record_status' => 'ACTIVE']);

        $this->actingAs($user)->get(route('admin.academic.staff.index'))
            ->assertOk()
            ->assertSee('Akhen')
            ->assertDontSee('Azhar')
            ->assertSee('1 data guru/staf pilot lama');

        $this->assertDatabaseHas('staff', ['staff_code' => 'PILOT-AZHAR', 'full_name' => 'Azhar']);
    }

    public function test_academic_admin_can_edit_safe_staff_fields(): void
    {
        $user = User::factory()->create();
        $role = Role::create(['code' => 'WAKA_AKADEMIK', 'name' => 'Waka Akademik']);
        UserRoleAssignment::create(['user_id' => $user->id, 'role_id' => $role->id, 'effective_from' => '2026-07-01']);
        $staff = Staff::create(['staff_code' => 'STAFF-ADMIN-EDIT', 'full_name' => 'Guru Lama', 'active_from' => '2026-07-01']);

        $this->actingAs($user)->get(route('admin.academic.staff.edit', $staff))->assertOk()->assertSee('Kode staf dikunci');
        $this->actingAs($user)->put(route('admin.academic.staff.update', $staff), [
            'full_name' => 'Guru Baru', 'record_status' => 'INACTIVE', 'active_from' => '2026-07-01', 'active_until' => '2026-12-31',
        ])->assertRedirect(route('admin.academic.staff.index'));

        $this->assertDatabaseHas('staff', ['id' => $staff->id, 'staff_code' => 'STAFF-ADMIN-EDIT', 'full_name' => 'Guru Baru', 'record_status' => 'INACTIVE']);
    }
}
