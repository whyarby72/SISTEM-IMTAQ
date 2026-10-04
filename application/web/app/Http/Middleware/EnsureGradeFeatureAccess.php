<?php

namespace App\Http\Middleware;

use App\Shared\Platform\Authorization\Services\FeatureAccessResolver;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureGradeFeatureAccess
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();
        $result = $user ? app(FeatureAccessResolver::class)->resolve($user, 'academic.grades') : null;

        abort_unless($user && $result['feature'] !== null && $result['effective_enabled'], 403);

        return $next($request);
    }
}
