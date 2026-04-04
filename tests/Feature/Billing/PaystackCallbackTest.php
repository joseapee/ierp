<?php

declare(strict_types=1);

namespace Tests\Feature\Billing;

use App\Models\Plan;
use App\Models\Subscription;
use App\Models\Tenant;
use App\Models\User;
use App\Services\PaystackService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery;
use Tests\TestCase;

class PaystackCallbackTest extends TestCase
{
    use RefreshDatabase;

    public function test_callback_without_reference_redirects_with_error(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get(route('billing.callback'))
            ->assertRedirect(route('billing.index'))
            ->assertSessionHas('error', 'Invalid payment reference.');
    }

    public function test_callback_with_failed_verification_shows_error(): void
    {
        $mock = Mockery::mock(PaystackService::class);
        $mock->shouldReceive('verifyTransaction')
            ->once()
            ->andReturn(['status' => false]);

        $this->app->instance(PaystackService::class, $mock);

        $user = User::factory()->create();

        $this->actingAs($user)
            ->get(route('billing.callback', ['reference' => 'ref_invalid']))
            ->assertRedirect(route('billing.index'))
            ->assertSessionHas('error', 'Payment verification failed. Please try again.');
    }

    public function test_callback_activates_trial_subscription(): void
    {
        $plan = Plan::factory()->create(['monthly_price' => 5000]);
        $tenant = Tenant::factory()->create(['plan_id' => $plan->id]);
        $subscription = Subscription::factory()->trial()->for($tenant)->for($plan)->create();
        $user = User::factory()->create(['tenant_id' => $tenant->id]);

        $mock = Mockery::mock(PaystackService::class);
        $mock->shouldReceive('verifyTransaction')
            ->once()
            ->andReturn([
                'status' => true,
                'data' => [
                    'status' => 'success',
                    'reference' => 'ref_activate_123',
                    'id' => 12345,
                    'amount' => 500000,
                    'channel' => 'card',
                    'authorization' => ['authorization_code' => 'AUTH_test'],
                    'customer' => ['customer_code' => 'CUS_test'],
                    'metadata' => [
                        'subscription_id' => $subscription->id,
                        'plan_id' => $plan->id,
                        'billing_cycle' => 'monthly',
                    ],
                ],
            ]);

        $this->app->instance(PaystackService::class, $mock);

        $this->actingAs($user)
            ->get(route('billing.callback', ['reference' => 'ref_activate_123']))
            ->assertRedirect(route('billing.index'))
            ->assertSessionHas('success');

        $subscription->refresh();
        $this->assertEquals('active', $subscription->status);
    }

    public function test_callback_renews_active_subscription(): void
    {
        $plan = Plan::factory()->create(['monthly_price' => 5000]);
        $tenant = Tenant::factory()->create(['plan_id' => $plan->id]);
        $originalEndsAt = now()->addDays(5);
        $subscription = Subscription::factory()->active()->for($tenant)->for($plan)->create([
            'ends_at' => $originalEndsAt,
        ]);
        $user = User::factory()->create(['tenant_id' => $tenant->id]);

        $mock = Mockery::mock(PaystackService::class);
        $mock->shouldReceive('verifyTransaction')
            ->once()
            ->andReturn([
                'status' => true,
                'data' => [
                    'status' => 'success',
                    'reference' => 'ref_renew_456',
                    'id' => 67890,
                    'amount' => 500000,
                    'channel' => 'card',
                    'authorization' => ['authorization_code' => 'AUTH_renew'],
                    'customer' => ['customer_code' => 'CUS_renew'],
                    'metadata' => [
                        'subscription_id' => $subscription->id,
                        'plan_id' => $plan->id,
                        'billing_cycle' => 'monthly',
                        'type' => 'renewal',
                    ],
                ],
            ]);

        $this->app->instance(PaystackService::class, $mock);

        $this->actingAs($user)
            ->get(route('billing.callback', ['reference' => 'ref_renew_456']))
            ->assertRedirect(route('billing.index'))
            ->assertSessionHas('success', 'Renewal successful! Your subscription has been extended.');

        $subscription->refresh();
        $this->assertEquals('active', $subscription->status);
        $this->assertTrue($subscription->ends_at->greaterThan($originalEndsAt));
    }
}
