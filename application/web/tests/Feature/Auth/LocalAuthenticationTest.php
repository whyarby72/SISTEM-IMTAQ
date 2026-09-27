<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use App\Shared\Platform\Authorization\Models\Role;
use App\Shared\Platform\Authorization\Models\UserRoleAssignment;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LocalAuthenticationTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_page_is_available(): void
    {
        $this->get(route('login'))
            ->assertOk()
            ->assertSee('src="'.asset('images/logo-imtaq.png').'"', false)
            ->assertSee('alt="IMTAQ Isy Karima"', false)
            ->assertSee('SISTEM IMTAQ')
            ->assertSee('Masuk ke akun Anda')
            ->assertSee('name="email" type="email"', false)
            ->assertSee('name="password" type="password"', false)
            ->assertSee('data-password-input', false)
            ->assertSee('data-password-toggle', false)
            ->assertSee('type="button"', false)
            ->assertSee('aria-label="Tampilkan kata sandi"', false)
            ->assertSee('name="remember"', false)
            ->assertSee('action="'.route('login.store').'"', false)
            ->assertSee('data-login-form', false)
            ->assertSee('data-login-submit', false)
            ->assertSee('data-login-submit>Masuk</button>', false)
            ->assertDontSee('Akses Sistem')
            ->assertDontSee('forgot password')
            ->assertDontSee('Google');
    }

    public function test_waka_is_redirected_to_operational_academic_dashboard(): void
    {
        $user = $this->adminUser();

        $this->post(route('login.store'), ['email' => $user->email, 'password' => 'password'])
            ->assertRedirect(route('academic.dashboard'));
        $this->assertAuthenticatedAs($user);
        $this->get(route('academic.dashboard'))->assertOk()->assertSee('Dashboard Waka Akademik');
        $this->get(route('admin.academic.dashboard'))
            ->assertRedirect(route('academic.dashboard'));
    }

    public function test_wali_is_redirected_to_operational_dashboard_and_cannot_see_admin_navigation(): void
    {
        $user = $this->waliUser();

        $this->post(route('login.store'), ['email' => $user->email, 'password' => 'password'])
            ->assertRedirect(route('academic.dashboard'));

        $this->get(route('academic.dashboard'))
            ->assertOk()
            ->assertSee('Dashboard Wali Kelas')
            ->assertDontSee('/admin/academic/classes');
        $this->get(route('admin.academic.dashboard'))
            ->assertForbidden()
            ->assertSee('Only Waka Akademik or Super Admin may open the admin dashboard.');
    }

    public function test_invalid_password_is_rejected(): void
    {
        $user = $this->adminUser();

        $response = $this->from(route('login'))->followingRedirects()->post(route('login.store'), ['email' => $user->email, 'password' => 'wrong']);
        $response->assertOk()
            ->assertSee('role="alert"', false)
            ->assertSee('Email atau password tidak sesuai.')
            ->assertSee('value="'.$user->email.'"', false)
            ->assertDontSee('name="password" type="password" value=', false);
        $this->assertGuest();
    }

    public function test_authenticated_user_can_logout(): void
    {
        $user = $this->adminUser();

        $this->actingAs($user)->post(route('logout'))->assertRedirect(route('login'));
        $this->assertGuest();
    }

    private function adminUser(): User
    {
        $user = User::factory()->create(['email' => 'admin@example.test', 'password' => 'password']);
        $role = Role::create(['code' => 'WAKA_AKADEMIK', 'name' => 'Waka Akademik']);
        UserRoleAssignment::create(['user_id' => $user->id, 'role_id' => $role->id, 'effective_from' => '2026-07-01']);

        return $user;
    }

    private function waliUser(): User
    {
        $user = User::factory()->create(['email' => 'wali@example.test', 'password' => 'password']);
        $role = Role::create(['code' => 'WALI_KELAS', 'name' => 'Wali Kelas']);
        UserRoleAssignment::create(['user_id' => $user->id, 'role_id' => $role->id, 'effective_from' => '2026-07-01']);

        return $user;
    }
}
