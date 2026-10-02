<?php

namespace App\Http\Middleware;

use App\Shared\Platform\Authorization\Services\FeatureAccessResolver;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureFeatureAccess
{
    public function handle(Request $request, Closure $next, string $feature): Response
    {
        abort_unless($request->user() && app(FeatureAccessResolver::class)->allowed($request->user(), $feature), 403);

        return $next($request);
    }
}
