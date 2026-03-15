<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Services\SubscriptionService;
use Illuminate\Console\Command;

class ExpireGracePeriods extends Command
{
    protected $signature = 'subscriptions:expire-grace-periods {--days=7 : Number of grace period days before suspension}';

    protected $description = 'Suspend subscriptions that have exceeded the grace period';

    public function handle(SubscriptionService $subscriptionService): int
    {
        $days = (int) $this->option('days');
        $expired = $subscriptionService->checkGracePeriodExpired($days);

        $this->info("Found {$expired->count()} subscription(s) with expired grace periods.");

        foreach ($expired as $subscription) {
            $subscriptionService->suspend($subscription);
            $this->info("Subscription #{$subscription->id} (Tenant: {$subscription->tenant->name}): Suspended.");
        }

        return self::SUCCESS;
    }
}
