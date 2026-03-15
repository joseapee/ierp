<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Plan;
use App\Models\Subscription;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

class PlanService
{
    /**
     * Paginated, searchable plan list.
     *
     * @param  array{search?: string, status?: string}  $filters
     */
    public function paginate(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        return Plan::query()
            ->withCount('subscriptions')
            ->when($filters['search'] ?? null, fn ($q, $search) => $q->where('name', 'like', "%{$search}%"))
            ->when(isset($filters['status']), fn ($q) => $filters['status'] === 'active'
                ? $q->where('is_active', true)
                : $q->where('is_active', false)
            )
            ->orderBy('sort_order')
            ->paginate($perPage);
    }

    /**
     * Create a new plan.
     *
     * @param  array{name: string, slug: string, description?: string, monthly_price: float, annual_price: float, trial_days?: int, is_active?: bool, sort_order?: int}  $data
     */
    public function create(array $data): Plan
    {
        return Plan::create($data);
    }

    /**
     * Update a plan.
     *
     * @param  array<string, mixed>  $data
     */
    public function update(Plan $plan, array $data): Plan
    {
        $plan->update($data);

        return $plan->refresh();
    }

    /**
     * Get all active plans ordered by sort_order.
     *
     * @return Collection<int, Plan>
     */
    public function getActivePlans(): Collection
    {
        return Plan::query()
            ->where('is_active', true)
            ->with('features')
            ->orderBy('sort_order')
            ->get();
    }

    /**
     * Get plans with features for comparison display.
     *
     * @return Collection<int, Plan>
     */
    public function comparePlans(): Collection
    {
        return Plan::query()
            ->where('is_active', true)
            ->with('features')
            ->orderBy('sort_order')
            ->get();
    }

    /**
     * Calculate prorated amount for a plan change.
     */
    public function calculateProration(Subscription $currentSubscription, Plan $newPlan, string $newCycle = 'monthly'): float
    {
        $daysRemaining = max(0, $currentSubscription->daysRemaining());
        $totalDays = $currentSubscription->billing_cycle === 'annual' ? 365 : 30;

        $currentPrice = $currentSubscription->billing_cycle === 'annual'
            ? (float) $currentSubscription->plan->annual_price
            : (float) $currentSubscription->plan->monthly_price;

        $newPrice = $newCycle === 'annual'
            ? (float) $newPlan->annual_price
            : (float) $newPlan->monthly_price;

        $currentDailyRate = $currentPrice / $totalDays;
        $unusedCredit = $currentDailyRate * $daysRemaining;

        return max(0, $newPrice - $unusedCredit);
    }
}
