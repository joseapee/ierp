<?php

namespace Tests\Unit\Services;

use App\Models\Plan;
use App\Models\Subscription;
use App\Models\Tenant;
use App\Services\SubscriptionService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SubscriptionServiceTest extends TestCase
{
    use RefreshDatabase;

    private SubscriptionService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new SubscriptionService;
    }

    public function test_start_trial(): void
    {
        $tenant = Tenant::factory()->create();
        $plan = Plan::factory()->create(['trial_days' => 14]);

        $subscription = $this->service->startTrial($tenant, $plan);

        $this->assertEquals('trial', $subscription->status);
        $this->assertEquals($plan->id, $subscription->plan_id);
        $this->assertEquals($tenant->id, $subscription->tenant_id);
        $this->assertNotNull($subscription->trial_ends_at);
        $this->assertTrue($subscription->trial_ends_at->isFuture());
        $this->assertEquals($plan->id, $tenant->fresh()->plan_id);
    }

    public function test_start_trial_with_custom_days(): void
    {
        $tenant = Tenant::factory()->create();
        $plan = Plan::factory()->create(['trial_days' => 14]);

        $subscription = $this->service->startTrial($tenant, $plan, 30);

        $this->assertGreaterThanOrEqual(29, $subscription->daysRemaining());
        $this->assertLessThanOrEqual(30, $subscription->daysRemaining());
    }

    public function test_activate(): void
    {
        $plan = Plan::factory()->create(['monthly_price' => 15000]);
        $tenant = Tenant::factory()->create(['plan_id' => $plan->id]);
        $subscription = Subscription::factory()
            ->trial()
            ->for($tenant)
            ->for($plan)
            ->create();

        $result = $this->service->activate($subscription, [
            'reference' => 'REF_'.uniqid(),
            'transaction_id' => 'TXN_123',
            'authorization_code' => 'AUTH_abc',
            'customer_code' => 'CUS_xyz',
            'amount' => 15000.00,
            'method' => 'card',
        ]);

        $this->assertEquals('active', $result->status);
        $this->assertEquals('AUTH_abc', $result->paystack_authorization_code);
        $this->assertEquals('CUS_xyz', $result->paystack_customer_code);
        $this->assertCount(1, $result->payments);
        $this->assertEquals('success', $result->payments->first()->status);
        $this->assertEquals(1, $tenant->invoices()->count());
    }

    public function test_renew(): void
    {
        $plan = Plan::factory()->create(['monthly_price' => 15000]);
        $tenant = Tenant::factory()->create(['plan_id' => $plan->id]);
        $subscription = Subscription::factory()
            ->active()
            ->for($tenant)
            ->for($plan)
            ->create(['ends_at' => now()->addDays(2)]);

        $originalEndsAt = $subscription->ends_at->copy();

        $result = $this->service->renew($subscription, [
            'reference' => 'REF_'.uniqid(),
            'amount' => 15000.00,
        ]);

        $this->assertEquals('active', $result->status);
        $this->assertTrue($result->ends_at->greaterThan($originalEndsAt));
    }

    public function test_cancel(): void
    {
        $subscription = Subscription::factory()->active()->create();

        $result = $this->service->cancel($subscription);

        $this->assertEquals('cancelled', $result->status);
        $this->assertNotNull($result->cancelled_at);
        $this->assertFalse($result->auto_renew);
    }

    public function test_suspend(): void
    {
        $subscription = Subscription::factory()->active()->create();

        $result = $this->service->suspend($subscription);

        $this->assertEquals('suspended', $result->status);
        $this->assertEquals('suspended', $result->tenant->fresh()->status);
    }

    public function test_change_plan_upgrade(): void
    {
        $currentPlan = Plan::factory()->create(['monthly_price' => 15000]);
        $newPlan = Plan::factory()->create(['monthly_price' => 45000]);
        $tenant = Tenant::factory()->create(['plan_id' => $currentPlan->id]);
        $subscription = Subscription::factory()
            ->active()
            ->for($tenant)
            ->for($currentPlan)
            ->create();

        $result = $this->service->changePlan($subscription, $newPlan);

        $this->assertEquals($newPlan->id, $result->plan_id);
        $this->assertEquals($newPlan->id, $tenant->fresh()->plan_id);
    }

    public function test_change_plan_downgrade(): void
    {
        $currentPlan = Plan::factory()->create(['monthly_price' => 45000]);
        $newPlan = Plan::factory()->create(['monthly_price' => 15000]);
        $tenant = Tenant::factory()->create(['plan_id' => $currentPlan->id]);
        $subscription = Subscription::factory()
            ->active()
            ->for($tenant)
            ->for($currentPlan)
            ->create();

        $result = $this->service->changePlan($subscription, $newPlan);

        $this->assertEquals($newPlan->id, $result->plan_id);
    }

    public function test_check_expiring(): void
    {
        // Expiring soon with auto_renew
        Subscription::factory()->create([
            'status' => 'active',
            'auto_renew' => true,
            'ends_at' => now()->addDays(2),
        ]);

        // Not expiring soon
        Subscription::factory()->create([
            'status' => 'active',
            'auto_renew' => true,
            'ends_at' => now()->addDays(10),
        ]);

        // Expiring but auto_renew off
        Subscription::factory()->create([
            'status' => 'active',
            'auto_renew' => false,
            'ends_at' => now()->addDays(2),
        ]);

        $expiring = $this->service->checkExpiring(3);

        $this->assertCount(1, $expiring);
    }

    public function test_enter_past_due(): void
    {
        $subscription = Subscription::factory()->active()->create();

        $result = $this->service->enterPastDue($subscription);

        $this->assertEquals('past_due', $result->status);
    }

    public function test_enter_grace_period(): void
    {
        $subscription = Subscription::factory()->active()->create();

        $result = $this->service->enterGracePeriod($subscription);

        $this->assertEquals('grace_period', $result->status);
    }

    public function test_check_grace_period_expired(): void
    {
        // Expired grace period (over 7 days)
        Subscription::factory()->create([
            'status' => 'grace_period',
            'ends_at' => now()->subDays(10),
        ]);

        // Still in grace period
        Subscription::factory()->create([
            'status' => 'grace_period',
            'ends_at' => now()->subDays(3),
        ]);

        $expired = $this->service->checkGracePeriodExpired(7);

        $this->assertCount(1, $expired);
    }
}
