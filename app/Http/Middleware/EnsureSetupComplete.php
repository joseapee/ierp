<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureSetupComplete
{
    /**
     * Redirect to setup wizard if the user has no tenant or the tenant
     * has not completed setup. Super admins bypass this check.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user || $user->is_super_admin) {
            return $next($request);
        }

        // No tenant yet — needs setup
        if ($user->tenant_id === null) {
            return redirect()->route('setup');
        }

        $tenant = app()->bound('current.tenant') ? app('current.tenant') : null;

        if ($tenant && $tenant->setup_completed_at === null) {
            return redirect()->route('setup');
        }

        return $next($request);
    }
}
