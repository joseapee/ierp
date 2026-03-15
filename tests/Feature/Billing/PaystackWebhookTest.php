<?php

declare(strict_types=1);

namespace Tests\Feature\Billing;

use App\Models\Plan;
use App\Models\Subscription;
use App\Models\Tenant;
use App\Services\PaystackService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PaystackWebhookTest extends TestCase
{
    use RefreshDatabase;

    protected function webhookPayload(string $event, array $data = []): array
    {
        return [
            'event' => $event,
            'data' => $data,
        ];
    }

    protected function generateSignature(string $payload): string
    {
        $secret = config('services.paystack.webhook_secret') ?? 'test-webhook-secret';

        return hash_hmac('sha512', $payload, $secret);
    }

    public function test_invalid_signature_is_rejected(): void
    {
        $payload = json_encode($this->webhookPayload('charge.success'));

        $this->postJson(route('webhook.paystack'), json_decode($payload, true), [
            'x-paystack-signature' => 'invalid-signature',
        ])->assertStatus(403);
    }

    public function test_charge_success_activates_trial_subscription(): void
    {
        $plan = Plan::factory()->create();
        $tenant = Tenant::factory()->create(['plan_id' => $plan->id]);
        $subscription = Subscription::factory()->trial()->for($tenant)->for($plan)->create();

        $data = [
            'reference' => 'WH_REF_'.uniqid(),
            'id' => 12345,
            'amount' => 500000,
            'channel' => 'card',
            'authorization' => ['authorization_code' => 'AUTH_abc123'],
            'customer' => ['customer_code' => 'CUS_abc123'],
            'metadata' => ['subscription_id' => $subscription->id],
        ];

        $payload = json_encode($this->webhookPayload('charge.success', $data));
        $signature = $this->generateSignature($payload);

        // Mock the PaystackService to accept our signature
        $this->mock(PaystackService::class, function ($mock) {
            $mock->shouldReceive('verifyWebhookSignature')->andReturn(true);
        });

        $this->postJson(route('webhook.paystack'), json_decode($payload, true), [
            'x-paystack-signature' => $signature,
        ])->assertOk();

        $subscription->refresh();
        $this->assertEquals('active', $subscription->status);
    }

    public function test_payment_failed_marks_subscription_past_due(): void
    {
        $plan = Plan::factory()->create();
        $tenant = Tenant::factory()->create(['plan_id' => $plan->id]);
        $subscription = Subscription::factory()->active()->for($tenant)->for($plan)->create();

        $data = [
            'metadata' => ['subscription_id' => $subscription->id],
        ];

        $payload = json_encode($this->webhookPayload('invoice.payment_failed', $data));

        $this->mock(PaystackService::class, function ($mock) {
            $mock->shouldReceive('verifyWebhookSignature')->andReturn(true);
        });

        $this->postJson(route('webhook.paystack'), json_decode($payload, true), [
            'x-paystack-signature' => 'mocked',
        ])->assertOk();

        $subscription->refresh();
        $this->assertEquals('past_due', $subscription->status);
    }

    public function test_unknown_event_is_handled_gracefully(): void
    {
        $payload = json_encode($this->webhookPayload('unknown.event', ['foo' => 'bar']));

        $this->mock(PaystackService::class, function ($mock) {
            $mock->shouldReceive('verifyWebhookSignature')->andReturn(true);
        });

        $this->postJson(route('webhook.paystack'), json_decode($payload, true), [
            'x-paystack-signature' => 'mocked',
        ])->assertOk();
    }
}
