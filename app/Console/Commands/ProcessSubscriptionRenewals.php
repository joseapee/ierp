<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Services\PaystackService;
use App\Services\SubscriptionService;
use Illuminate\Console\Command;

class ProcessSubscriptionRenewals extends Command
{
    protected $signature = 'subscriptions:process-renewals {--days=3 : Number of days before expiry to attempt renewal}';

    protected $description = 'Process auto-renewal for subscriptions expiring soon';

    public function handle(SubscriptionService $subscriptionService, PaystackService $paystackService): int
    {
        $days = (int) $this->option('days');
        $expiring = $subscriptionService->checkExpiring($days);

        $this->info("Found {$expiring->count()} subscription(s) expiring within {$days} days.");

        foreach ($expiring as $subscription) {
            if (! $subscription->paystack_authorization_code) {
                $this->warn("Subscription #{$subscription->id}: No authorization code, skipping.");
                $subscriptionService->enterPastDue($subscription);

                continue;
            }

            $tenant = $subscription->tenant;
            $user = $tenant->users()->first();

            if (! $user) {
                $this->warn("Subscription #{$subscription->id}: No user found, skipping.");

                continue;
            }

            $amount = $subscription->billing_cycle === 'annual'
                ? (float) $subscription->plan->annual_price
                : (float) $subscription->plan->monthly_price;

            $result = $paystackService->chargeAuthorization(
                $subscription->paystack_authorization_code,
                $user->email,
                $amount
            );

            if ($result['status']) {
                $subscriptionService->renew($subscription, [
                    'reference' => $result['data']['reference'] ?? 'RENEW_'.uniqid(),
                    'amount' => $amount,
                ]);
                $this->info("Subscription #{$subscription->id}: Renewed successfully.");
            } else {
                $subscriptionService->enterPastDue($subscription);
                $this->warn("Subscription #{$subscription->id}: Charge failed, marked as past_due.");
            }
        }

        return self::SUCCESS;
    }
}
