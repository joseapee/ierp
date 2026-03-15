<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Invoice;
use App\Models\Payment;
use App\Models\Plan;
use App\Models\Subscription;
use App\Models\Tenant;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class SubscriptionService
{
    /**
     * Start a trial subscription for a tenant.
     */
    public function startTrial(Tenant $tenant, Plan $plan, ?int $trialDays = null, string $billingCycle = 'monthly'): Subscription
    {
        $trialDays = $trialDays ?? $plan->trial_days;
        $trialEndsAt = now()->addDays($trialDays);

        return DB::transaction(function () use ($tenant, $plan, $trialEndsAt, $billingCycle): Subscription {
            $tenant->update(['plan_id' => $plan->id]);

            return Subscription::create([
                'tenant_id' => $tenant->id,
                'plan_id' => $plan->id,
                'billing_cycle' => $billingCycle,
                'status' => 'trial',
                'trial_ends_at' => $trialEndsAt,
                'starts_at' => now(),
                'ends_at' => $trialEndsAt,
                'auto_renew' => true,
            ]);
        });
    }

    /**
     * Activate a subscription after successful payment.
     *
     * @param  array{reference: string, transaction_id?: string, authorization_code?: string, customer_code?: string, amount: float, method?: string}  $paymentData
     */
    public function activate(Subscription $subscription, array $paymentData): Subscription
    {
        return DB::transaction(function () use ($subscription, $paymentData): Subscription {
            $endsAt = $subscription->billing_cycle === 'annual'
                ? now()->addYear()
                : now()->addDays(30);

            $subscription->update([
                'status' => 'active',
                'starts_at' => now(),
                'ends_at' => $endsAt,
                'paystack_authorization_code' => $paymentData['authorization_code'] ?? null,
                'paystack_customer_code' => $paymentData['customer_code'] ?? null,
            ]);

            $payment = Payment::create([
                'tenant_id' => $subscription->tenant_id,
                'subscription_id' => $subscription->id,
                'amount' => $paymentData['amount'],
                'currency' => 'NGN',
                'payment_method' => $paymentData['method'] ?? 'card',
                'status' => 'success',
                'paystack_reference' => $paymentData['reference'],
                'paystack_transaction_id' => $paymentData['transaction_id'] ?? null,
                'paid_at' => now(),
            ]);

            $this->generateInvoice($subscription, $payment);

            $subscription->tenant->update(['status' => 'active']);

            return $subscription->refresh();
        });
    }

    /**
     * Renew a subscription.
     *
     * @param  array{reference: string, transaction_id?: string, amount: float, method?: string}  $paymentData
     */
    public function renew(Subscription $subscription, array $paymentData): Subscription
    {
        return DB::transaction(function () use ($subscription, $paymentData): Subscription {
            $endsAt = $subscription->billing_cycle === 'annual'
                ? $subscription->ends_at->addYear()
                : $subscription->ends_at->addDays(30);

            $subscription->update([
                'status' => 'active',
                'ends_at' => $endsAt,
            ]);

            $payment = Payment::create([
                'tenant_id' => $subscription->tenant_id,
                'subscription_id' => $subscription->id,
                'amount' => $paymentData['amount'],
                'currency' => 'NGN',
                'payment_method' => $paymentData['method'] ?? 'card',
                'status' => 'success',
                'paystack_reference' => $paymentData['reference'],
                'paystack_transaction_id' => $paymentData['transaction_id'] ?? null,
                'paid_at' => now(),
            ]);

            $this->generateInvoice($subscription, $payment);

            return $subscription->refresh();
        });
    }

    /**
     * Cancel a subscription.
     */
    public function cancel(Subscription $subscription): Subscription
    {
        $subscription->update([
            'status' => 'cancelled',
            'cancelled_at' => now(),
            'auto_renew' => false,
        ]);

        return $subscription->refresh();
    }

    /**
     * Suspend a subscription and its tenant.
     */
    public function suspend(Subscription $subscription): Subscription
    {
        return DB::transaction(function () use ($subscription): Subscription {
            $subscription->update(['status' => 'suspended']);
            $subscription->tenant->update(['status' => 'suspended']);

            return $subscription->refresh();
        });
    }

    /**
     * Change the plan of a subscription (upgrade is immediate, downgrade at end of cycle).
     */
    public function changePlan(Subscription $subscription, Plan $newPlan, string $billingCycle = 'monthly'): Subscription
    {
        $currentPlan = $subscription->plan;
        $currentPrice = $subscription->billing_cycle === 'annual'
            ? (float) $currentPlan->annual_price
            : (float) $currentPlan->monthly_price;
        $newPrice = $billingCycle === 'annual'
            ? (float) $newPlan->annual_price
            : (float) $newPlan->monthly_price;

        $isUpgrade = $newPrice > $currentPrice;

        return DB::transaction(function () use ($subscription, $newPlan, $billingCycle, $isUpgrade): Subscription {
            if ($isUpgrade) {
                $subscription->update([
                    'plan_id' => $newPlan->id,
                    'billing_cycle' => $billingCycle,
                ]);
                $subscription->tenant->update(['plan_id' => $newPlan->id]);
            } else {
                $subscription->update([
                    'plan_id' => $newPlan->id,
                    'billing_cycle' => $billingCycle,
                ]);
                $subscription->tenant->update(['plan_id' => $newPlan->id]);
            }

            return $subscription->refresh();
        });
    }

    /**
     * Enter past_due status.
     */
    public function enterPastDue(Subscription $subscription): Subscription
    {
        $subscription->update(['status' => 'past_due']);

        return $subscription->refresh();
    }

    /**
     * Enter grace period status.
     */
    public function enterGracePeriod(Subscription $subscription): Subscription
    {
        $subscription->update(['status' => 'grace_period']);

        return $subscription->refresh();
    }

    /**
     * Find subscriptions expiring within the given number of days that have auto_renew on.
     *
     * @return Collection<int, Subscription>
     */
    public function checkExpiring(int $withinDays = 3): Collection
    {
        return Subscription::query()
            ->whereIn('status', ['active', 'trial'])
            ->where('auto_renew', true)
            ->where('ends_at', '<=', now()->addDays($withinDays))
            ->where('ends_at', '>=', now())
            ->with(['tenant', 'plan'])
            ->get();
    }

    /**
     * Find grace period subscriptions that have expired past a given threshold.
     *
     * @return Collection<int, Subscription>
     */
    public function checkGracePeriodExpired(int $graceDays = 7): Collection
    {
        return Subscription::query()
            ->where('status', 'grace_period')
            ->where('ends_at', '<', now()->subDays($graceDays))
            ->with(['tenant'])
            ->get();
    }

    /**
     * Generate an invoice for a subscription payment.
     */
    protected function generateInvoice(Subscription $subscription, Payment $payment): Invoice
    {
        $plan = $subscription->plan;
        $cycleLabel = $subscription->billing_cycle === 'annual' ? 'Annual' : 'Monthly';

        return Invoice::create([
            'tenant_id' => $subscription->tenant_id,
            'subscription_id' => $subscription->id,
            'payment_id' => $payment->id,
            'invoice_number' => $this->getNextInvoiceNumber($subscription->tenant_id),
            'amount' => $payment->amount,
            'currency' => $payment->currency,
            'status' => 'paid',
            'issued_at' => now(),
            'paid_at' => now(),
            'due_at' => now(),
            'line_items' => [
                [
                    'description' => "{$plan->name} Plan — {$cycleLabel} Subscription",
                    'amount' => $payment->amount,
                ],
            ],
        ]);
    }

    /**
     * Get the next invoice number for a tenant.
     */
    protected function getNextInvoiceNumber(int $tenantId): string
    {
        $count = Invoice::where('tenant_id', $tenantId)->count();

        return 'INV-'.str_pad((string) $tenantId, 4, '0', STR_PAD_LEFT)
            .'-'.str_pad((string) ($count + 1), 5, '0', STR_PAD_LEFT);
    }
}
