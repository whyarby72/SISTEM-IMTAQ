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

    public function test_waka_is_sent_to_the_operational_dashboard_with_sidebar(): void
    {
        $user = User::factory()->create();
        $role = Role::create(['code' => 'WAKA_AKADEMIK', 'name' => 'Waka Akademik']);
        UserRoleAssignment::create(['user_id' => $user->id, 'role_id' => $role->id, 'effective_from' => '2026-07-01']);

        $this->actingAs($user)->get(route('admin.academic.dashboard'))
            ->assertRedirect(route('academic.dashboard'));
    }

    public function test_non_admin_cannot_open_dashboard(): void
    {
        $this->actingAs(User::factory()->create())->get(route('admin.academic.dashboard'))->assertForbidden();
    }
}
