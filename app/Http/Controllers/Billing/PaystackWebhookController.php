<?php

declare(strict_types=1);

namespace App\Http\Controllers\Billing;

use App\Http\Controllers\Controller;
use App\Models\Subscription;
use App\Services\PaystackService;
use App\Services\SubscriptionService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class PaystackWebhookController extends Controller
{
    public function handle(Request $request, PaystackService $paystackService, SubscriptionService $subscriptionService): Response
    {
        $payload = $request->getContent();
        $signature = $request->header('x-paystack-signature', '');

        if (! $paystackService->verifyWebhookSignature($payload, $signature)) {
            return response('Invalid signature', 403);
        }

        $event = $request->input('event');
        $data = $request->input('data', []);

        match ($event) {
            'charge.success' => $this->handleChargeSuccess($data, $subscriptionService),
            'invoice.payment_failed' => $this->handlePaymentFailed($data, $subscriptionService),
            default => null,
        };

        return response('OK', 200);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    protected function handleChargeSuccess(array $data, SubscriptionService $subscriptionService): void
    {
        $metadata = $data['metadata'] ?? [];
        $subscriptionId = $metadata['subscription_id'] ?? null;

        if (! $subscriptionId) {
            return;
        }

        $subscription = Subscription::query()->find($subscriptionId);

        if (! $subscription) {
            return;
        }

        if ($subscription->status === 'trial' || $subscription->status === 'past_due' || $subscription->status === 'grace_period') {
            $subscriptionService->activate($subscription, [
                'reference' => $data['reference'] ?? '',
                'transaction_id' => (string) ($data['id'] ?? ''),
                'authorization_code' => $data['authorization']['authorization_code'] ?? null,
                'customer_code' => $data['customer']['customer_code'] ?? null,
                'amount' => ($data['amount'] ?? 0) / 100,
                'method' => $data['channel'] ?? 'card',
            ]);
        }
    }

    /**
     * @param  array<string, mixed>  $data
     */
    protected function handlePaymentFailed(array $data, SubscriptionService $subscriptionService): void
    {
        $metadata = $data['metadata'] ?? [];
        $subscriptionId = $metadata['subscription_id'] ?? null;

        if (! $subscriptionId) {
            return;
        }

        $subscription = Subscription::query()->find($subscriptionId);

        if ($subscription && $subscription->status === 'active') {
            $subscriptionService->enterPastDue($subscription);
        }
    }
}
