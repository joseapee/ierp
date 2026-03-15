<?php

namespace App\Http\Middleware;

use App\Models\Tenant;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ResolveTenant
{
    /**
     * Resolve the current tenant from the authenticated user and bind it
     * to the service container so all global scopes and tenant-aware code
     * can access it throughout the request lifecycle.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user && ! $user->is_super_admin && $user->tenant_id !== null) {
            $tenant = Tenant::query()->withoutGlobalScopes()->find($user->tenant_id);

            if (! $tenant) {
                auth()->logout();

                return redirect()->route('login')
                    ->withErrors(['email' => 'Your account is no longer accessible. Please contact support.']);
            }

            // Bind tenant regardless of status — CheckSubscription handles access control
            app()->instance('current.tenant', $tenant);
            view()->share('currentTenant', $tenant);
        }

        return $next($request);
    }
}
