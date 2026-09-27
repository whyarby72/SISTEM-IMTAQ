<?php

namespace App\Http\Controllers\Auth;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
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

        $request->session()->regenerate();

        $isOperationalAcademicRole = $request->user()?->roleAssignments()
            ->effectiveAt(Carbon::now())
            ->whereHas('role', fn ($query) => $query->whereIn('code', ['WALI_KELAS', 'WAKA_AKADEMIK']))
            ->exists();

        return redirect()->route($isOperationalAcademicRole ? 'academic.dashboard' : 'admin.academic.dashboard');
    }

    public function destroy(Request $request): RedirectResponse
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return to_route('login');
    }
}
