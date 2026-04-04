<?php

declare(strict_types=1);

namespace Tests\Feature\Billing;

use App\Livewire\Billing\BillingDashboard;
use App\Models\Plan;
use App\Models\Subscription;
use App\Models\Tenant;
use App\Models\User;
use App\Services\PaystackService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Mockery;
use Tests\TestCase;

class BillingDashboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_is_redirected_to_login(): void
    {
        $this->get(route('billing.index'))
            ->assertRedirect(route('login'));
    }

    public function test_authenticated_user_can_view_billing_dashboard(): void
    {
        $plan = Plan::factory()->create();
        $tenant = Tenant::factory()->create(['plan_id' => $plan->id]);
        Subscription::factory()->active()->for($tenant)->for($plan)->create();
        $user = User::factory()->create(['tenant_id' => $tenant->id]);

        $this->actingAs($user)
            ->get(route('billing.index'))
            ->assertOk()
            ->assertSee('Billing');
    }

    public function test_dashboard_shows_current_plan(): void
    {
        $plan = Plan::factory()->create(['name' => 'Business Plan']);
        $tenant = Tenant::factory()->create(['plan_id' => $plan->id]);
        Subscription::factory()->active()->for($tenant)->for($plan)->create();
        $user = User::factory()->create(['tenant_id' => $tenant->id]);

        $this->actingAs($user)
            ->get(route('billing.index'))
            ->assertOk()
            ->assertSee('Business Plan');
    }

    public function test_dashboard_shows_trial_status(): void
    {
        $plan = Plan::factory()->create();
        $tenant = Tenant::factory()->create(['plan_id' => $plan->id]);
        Subscription::factory()->trial()->for($tenant)->for($plan)->create();
        $user = User::factory()->create(['tenant_id' => $tenant->id]);

        $this->actingAs($user)
            ->get(route('billing.index'))
            ->assertOk()
            ->assertSee('Trial');
    }

    public function test_renew_button_shown_for_active_subscription(): void
    {
        $plan = Plan::factory()->create();
        $tenant = Tenant::factory()->create(['plan_id' => $plan->id]);
        Subscription::factory()->active()->for($tenant)->for($plan)->create();
        $user = User::factory()->create(['tenant_id' => $tenant->id]);

        app()->instance('current.tenant', $tenant);

        $this->actingAs($user)
            ->get(route('billing.index'))
            ->assertOk()
            ->assertSee('Renew Now');
    }

    public function test_renew_button_not_shown_for_trial_subscription(): void
    {
        $plan = Plan::factory()->create();
        $tenant = Tenant::factory()->create(['plan_id' => $plan->id]);
        Subscription::factory()->trial()->for($tenant)->for($plan)->create();
        $user = User::factory()->create(['tenant_id' => $tenant->id]);

        app()->instance('current.tenant', $tenant);

        $this->actingAs($user)
            ->get(route('billing.index'))
            ->assertOk()
            ->assertDontSee('Renew Now');
    }

    public function test_initiate_renewal_redirects_to_paystack(): void
    {
        $plan = Plan::factory()->create(['monthly_price' => 5000]);
        $tenant = Tenant::factory()->create(['plan_id' => $plan->id]);
        Subscription::factory()->active()->for($tenant)->for($plan)->create();
        $user = User::factory()->create(['tenant_id' => $tenant->id]);

        app()->instance('current.tenant', $tenant);

        $mock = Mockery::mock(PaystackService::class);
        $mock->shouldReceive('initializeTransaction')
            ->once()
            ->withArgs(function (float $amount, string $email, array $metadata) use ($plan) {
                return $amount === 5000.0
                    && $email !== ''
                    && ($metadata['type'] ?? '') === 'renewal'
                    && ($metadata['plan_id'] ?? 0) === $plan->id;
            })
            ->andReturn([
                'status' => true,
                'authorization_url' => 'https://checkout.paystack.com/test-renewal',
            ]);

        $this->app->instance(PaystackService::class, $mock);

        Livewire::actingAs($user)
            ->test(BillingDashboard::class)
            ->call('initiateRenewal')
            ->assertRedirect('https://checkout.paystack.com/test-renewal');
    }

    public function test_initiate_renewal_flashes_error_on_failure(): void
    {
        $plan = Plan::factory()->create(['monthly_price' => 5000]);
        $tenant = Tenant::factory()->create(['plan_id' => $plan->id]);
        Subscription::factory()->active()->for($tenant)->for($plan)->create();
        $user = User::factory()->create(['tenant_id' => $tenant->id]);

        app()->instance('current.tenant', $tenant);

        $mock = Mockery::mock(PaystackService::class);
        $mock->shouldReceive('initializeTransaction')
            ->once()
            ->andReturn(['status' => false]);

        $this->app->instance(PaystackService::class, $mock);

        Livewire::actingAs($user)
            ->test(BillingDashboard::class)
            ->call('initiateRenewal')
            ->assertNoRedirect()
            ->assertSee('Unable to initialize renewal payment');
    }
}
