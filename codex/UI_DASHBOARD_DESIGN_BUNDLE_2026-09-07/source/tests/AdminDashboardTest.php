<?php

namespace Tests\Feature\Admin;

use App\Models\User;
use App\Shared\Platform\Authorization\Models\Role;
use App\Shared\Platform\Authorization\Models\UserRoleAssignment;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminDashboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_academic_admin_can_open_dashboard_and_see_master_links(): void
    {
        $user = User::factory()->create();
        $role = Role::create(['code' => 'ADMIN_AKADEMIK', 'name' => 'Admin Akademik']);
        UserRoleAssignment::create(['user_id' => $user->id, 'role_id' => $role->id, 'effective_from' => '2026-07-01']);

        $this->actingAs($user)->get(route('admin.academic.dashboard'))
            ->assertOk()
            ->assertSee(route('admin.academic.classes.index'))
            ->assertSee(route('admin.academic.staff.index'))
            ->assertSee(route('admin.academic.schedules.index'));
    }

    public function test_non_admin_cannot_open_dashboard(): void
    {
        $this->actingAs(User::factory()->create())->get(route('admin.academic.dashboard'))->assertForbidden();
    }
}
