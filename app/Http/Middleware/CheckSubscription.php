<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckSubscription
{
    /**
     * Check that the tenant has an active or trial subscription.
     * Redirects to the billing page if not.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user || $user->is_super_admin || $user->tenant_id === null) {
            return $next($request);
        }

        $tenant = app()->bound('current.tenant') ? app('current.tenant') : null;

        if (! $tenant) {
            return $next($request);
        }

        $subscription = $tenant->activeSubscription;

        if (! $subscription || ! $subscription->isActive()) {
            return redirect()->route('billing.index')
                ->with('warning', 'Your subscription is inactive. Please renew to continue.');
        }

        return $next($request);
    }
}
