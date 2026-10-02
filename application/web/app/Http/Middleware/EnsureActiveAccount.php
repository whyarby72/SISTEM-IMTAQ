<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureActiveAccount
{
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->user()?->status === 'DISABLED') {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            abort(403);
        }

        if ($request->user()?->must_change_password && ! in_array($request->route()?->getName(), ['password.change', 'password.update', 'logout'], true)) {
            return redirect()->route('password.change');
        }

        return $next($request);
    }
}
