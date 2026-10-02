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
        $result = $request->user() ? app(FeatureAccessResolver::class)->resolve($request->user(), $feature) : null;
        // The registry is additive. Until a deployment runs its feature seeder,
        // an unknown code preserves the pre-registry route contract; a known
        // DISABLED/denied feature is always enforced server-side.
        abort_unless($request->user() && ($result['feature'] === null || $result['effective_enabled']), 403);

        return $next($request);
    }
}
