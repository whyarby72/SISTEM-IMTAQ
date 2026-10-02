<?php

namespace App\Http\Controllers\Auth;

use App\Shared\Platform\Audit\Services\AuditLogger;
use App\Shared\Platform\Authorization\Services\FeatureAccessResolver;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class AuthenticatedSessionController
{
    public function create(): View
    {
        return view('auth.login');
    }

    public function store(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        if (! Auth::attempt($credentials, $request->boolean('remember'))) {
            return back()->withErrors(['email' => 'Email atau password tidak sesuai.'])->onlyInput('email');
        }

        if ($request->user()?->status === 'DISABLED') {
            Auth::logout();

            return back()->withErrors(['email' => 'Akun ini dinonaktifkan. Hubungi administrator.'])->onlyInput('email');
        }

        $request->session()->regenerate();

        $isOperationalAcademicRole = $request->user()?->roleAssignments()
            ->effectiveAt(Carbon::now())
            ->whereHas('role', fn ($query) => $query->whereIn('code', ['WALI_KELAS', 'WAKA_AKADEMIK']))
            ->exists();

        if ($request->user()?->must_change_password) {
            return redirect()->route('password.change');
        }

        $landing = $this->landingRoute($request->user(), $isOperationalAcademicRole);

        return redirect()->route($landing);
    }

    public function editPassword(Request $request): View
    {
        abort_unless($request->user(), 403);

        return view('auth.change-password');
    }

    public function updatePassword(Request $request): RedirectResponse
    {
        $user = $request->user();
        abort_unless($user, 403);
        $payload = $request->validate(['current_password' => ['required', 'string'], 'password' => ['required', 'string', 'min:12', 'confirmed']]);
        if (! Hash::check($payload['current_password'], $user->password)) {
            return back()->withErrors(['current_password' => 'Password saat ini tidak sesuai.']);
        }
        $user->forceFill(['password' => Hash::make($payload['password']), 'must_change_password' => false])->save();
        app(AuditLogger::class)->record(['actor_user_id' => $user->id, 'action' => 'USER_PASSWORD_CHANGED', 'entity_type' => 'User', 'entity_id' => (string) $user->id, 'old_values' => ['must_change_password' => true], 'new_values' => ['must_change_password' => false]]);

        return redirect()->route('academic.dashboard')->with('status', 'Password berhasil diperbarui.');
    }

    public function destroy(Request $request): RedirectResponse
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return to_route('login');
    }

    private function landingRoute($user, bool $operational): string
    {
        $preference = $user->preferences()->where('preference_key', 'default_landing_page')->first();
        $preferenceValue = $preference?->preference_value;
        $requested = is_array($preferenceValue) ? ($preferenceValue['value'] ?? null) : null;
        $resolver = app(FeatureAccessResolver::class);
        if ($requested === 'academic.dashboard' && $this->featureAllowedOrUnregistered($resolver, $user, 'academic.dashboard')) {
            return 'academic.dashboard';
        }
        if ($requested === 'admin.academic.dashboard' && $this->featureAllowedOrUnregistered($resolver, $user, 'academic.dashboard')) {
            return 'admin.academic.dashboard';
        }

        return $operational && $this->featureAllowedOrUnregistered($resolver, $user, 'academic.dashboard') ? 'academic.dashboard' : 'admin.academic.dashboard';
    }

    private function featureAllowedOrUnregistered(FeatureAccessResolver $resolver, $user, string $code): bool
    {
        $result = $resolver->resolve($user, $code);

        return $result['feature'] === null || $result['effective_enabled'];
    }
}
