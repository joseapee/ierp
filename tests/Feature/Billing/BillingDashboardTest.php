<?php

declare(strict_types=1);

namespace Tests\Feature\Billing;

use App\Models\Plan;
use App\Models\Subscription;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
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
}
