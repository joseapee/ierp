<?php

declare(strict_types=1);

namespace App\Http\Controllers\Billing;

use App\Http\Controllers\Controller;
use App\Models\Subscription;
use App\Services\PaystackService;
use App\Services\SubscriptionService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class PaystackCallbackController extends Controller
{
    public function handle(Request $request, PaystackService $paystackService, SubscriptionService $subscriptionService): RedirectResponse
    {
        $reference = $request->query('reference');

        if (! $reference) {
            return redirect()->route('billing.index')
                ->with('error', 'Invalid payment reference.');
        }

        $result = $paystackService->verifyTransaction((string) $reference);

        if (! $result['status'] || ($result['data']['status'] ?? '') !== 'success') {
            return redirect()->route('billing.index')
                ->with('error', 'Payment verification failed. Please try again.');
        }

        $data = $result['data'];
        $metadata = $data['metadata'] ?? [];
        $subscriptionId = $metadata['subscription_id'] ?? null;

        if ($subscriptionId) {
            $subscription = Subscription::query()->find($subscriptionId);

            if ($subscription) {
                $paymentInfo = [
                    'reference' => $data['reference'],
                    'transaction_id' => (string) ($data['id'] ?? ''),
                    'authorization_code' => $data['authorization']['authorization_code'] ?? null,
                    'customer_code' => $data['customer']['customer_code'] ?? null,
                    'amount' => ($data['amount'] ?? 0) / 100,
                    'method' => $data['channel'] ?? 'card',
                ];

                $isRenewal = ($metadata['type'] ?? '') === 'renewal';

                if ($isRenewal && $subscription->status === 'active') {
                    $subscriptionService->renew($subscription, $paymentInfo);

                    return redirect()->route('billing.index')
                        ->with('success', 'Renewal successful! Your subscription has been extended.');
                }

                if (in_array($subscription->status, ['trial', 'past_due', 'grace_period'])) {
                    $subscriptionService->activate($subscription, $paymentInfo);
                }
            }
        }

        return redirect()->route('billing.index')
            ->with('success', 'Payment successful! Your subscription is now active.');
    }
}
