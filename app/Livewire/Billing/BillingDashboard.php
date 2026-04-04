<?php

declare(strict_types=1);

namespace App\Livewire\Billing;

use App\Models\Plan;
use App\Services\PaystackService;
use App\Services\PlanService;
use App\Services\SubscriptionService;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.app')]
class BillingDashboard extends Component
{
    public function initiatePlanChange(int $planId, string $cycle = 'monthly'): void
    {
        $tenant = app('current.tenant');
        $user = auth()->user();
        $plan = Plan::query()->findOrFail($planId);
        $subscription = $tenant->activeSubscription;

        $price = $cycle === 'annual' ? (float) $plan->annual_price : (float) $plan->monthly_price;

        if ($price <= 0) {
            return;
        }

        $paystackService = app(PaystackService::class);
        $result = $paystackService->initializeTransaction(
            $price,
            $user->email,
            [
                'subscription_id' => $subscription?->id,
                'plan_id' => $plan->id,
                'billing_cycle' => $cycle,
            ],
            route('billing.callback')
        );

        if ($result['status'] && isset($result['authorization_url'])) {
            $this->redirect($result['authorization_url']);
        } else {
            session()->flash('error', 'Unable to initialize payment. Please try again.');
        }
    }

    public function initiateRenewal(): void
    {
        $tenant = app('current.tenant');
        $user = auth()->user();
        $subscription = $tenant->activeSubscription;

        if (! $subscription || ! $subscription->plan) {
            session()->flash('error', 'No active subscription to renew.');

            return;
        }

        $plan = $subscription->plan;
        $price = $subscription->billing_cycle === 'annual'
            ? (float) $plan->annual_price
            : (float) $plan->monthly_price;

        if ($price <= 0) {
            return;
        }

        $paystackService = app(PaystackService::class);
        $result = $paystackService->initializeTransaction(
            $price,
            $user->email,
            [
                'subscription_id' => $subscription->id,
                'plan_id' => $plan->id,
                'billing_cycle' => $subscription->billing_cycle,
                'type' => 'renewal',
            ],
            route('billing.callback')
        );

        if ($result['status'] && isset($result['authorization_url'])) {
            $this->redirect($result['authorization_url']);
        } else {
            session()->flash('error', 'Unable to initialize renewal payment. Please try again.');
        }
    }

    public function cancelSubscription(): void
    {
        $tenant = app('current.tenant');
        $subscription = $tenant->activeSubscription;

        if ($subscription) {
            $subscriptionService = app(SubscriptionService::class);
            $subscriptionService->cancel($subscription);
            session()->flash('success', 'Subscription cancelled successfully.');
        }
    }

    public function render(): mixed
    {
        $tenant = app()->bound('current.tenant') ? app('current.tenant') : null;
        $subscription = $tenant?->activeSubscription;
        $plan = $tenant?->currentPlan;

        $planService = app(PlanService::class);

        return view('livewire.billing.billing-dashboard', [
            'tenant' => $tenant,
            'subscription' => $subscription,
            'currentPlan' => $plan,
            'plans' => $planService->getActivePlans(),
            'payments' => $tenant ? $tenant->payments()->latest()->limit(10)->get() : collect(),
            'invoices' => $tenant ? $tenant->invoices()->latest()->limit(10)->get() : collect(),
        ]);
    }
}
