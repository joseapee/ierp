<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Services\FeatureGateService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckFeature
{
    /**
     * Check that the tenant's plan has a specific feature enabled.
     * Returns 403 if the feature is disabled or the limit is reached.
     *
     * Usage: middleware('feature:manufacturing_enabled') or middleware('feature:max_users')
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, string $featureKey): Response
    {
        $user = $request->user();

        if (! $user || $user->is_super_admin || $user->tenant_id === null) {
            return $next($request);
        }

        $tenant = app()->bound('current.tenant') ? app('current.tenant') : null;

        if (! $tenant) {
            return $next($request);
        }

        $featureGate = app(FeatureGateService::class);

        if (! $featureGate->check($tenant, $featureKey)) {
            abort(403, 'This feature is not available on your current plan.');
        }

        return $next($request);
    }
}
