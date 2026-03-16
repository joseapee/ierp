<?php

declare(strict_types=1);

namespace Tests\Feature\Middleware;

use App\Models\Plan;
use App\Models\Subscription;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CheckSubscriptionTest extends TestCase
{
    use RefreshDatabase;

    public function test_active_subscription_passes(): void
    {
        $plan = Plan::factory()->create();
        $tenant = Tenant::factory()->setupComplete()->create(['plan_id' => $plan->id]);
        Subscription::factory()->active()->for($tenant)->for($plan)->create();
        $user = User::factory()->create(['tenant_id' => $tenant->id]);

        $this->actingAs($user)
            ->get(route('categories.index'))
            ->assertStatus(403); // 403 from permission check, not redirect — proves subscription passed
    }

    public function test_trial_subscription_passes(): void
    {
        $plan = Plan::factory()->create();
        $tenant = Tenant::factory()->setupComplete()->create(['plan_id' => $plan->id]);
        Subscription::factory()->trial()->for($tenant)->for($plan)->create();
        $user = User::factory()->create(['tenant_id' => $tenant->id]);

        $this->actingAs($user)
            ->get(route('categories.index'))
            ->assertStatus(403); // 403 from permission check, not redirect — proves subscription passed
    }

    public function test_expired_subscription_redirects_to_billing(): void
    {
        $plan = Plan::factory()->create();
        $tenant = Tenant::factory()->setupComplete()->create(['plan_id' => $plan->id]);
        Subscription::factory()->expired()->for($tenant)->for($plan)->create();
        $user = User::factory()->create(['tenant_id' => $tenant->id]);

        $this->actingAs($user)
            ->get(route('categories.index'))
            ->assertRedirect(route('billing.index'));
    }

    public function test_no_subscription_redirects_to_billing(): void
    {
        $tenant = Tenant::factory()->setupComplete()->create();
        $user = User::factory()->create(['tenant_id' => $tenant->id]);

        $this->actingAs($user)
            ->get(route('categories.index'))
            ->assertRedirect(route('billing.index'));
    }

    public function test_super_admin_bypasses_subscription_check(): void
    {
        $admin = User::factory()->create([
            'is_super_admin' => true,
            'tenant_id' => null,
        ]);

        $this->actingAs($admin)
            ->get(route('categories.index'))
            ->assertOk();
    }

    public function test_user_without_tenant_bypasses(): void
    {
        $user = User::factory()->create(['tenant_id' => null]);

        // Use billing route (no subscription/permission middleware) to confirm no redirect loop
        $this->actingAs($user)
            ->get(route('billing.index'))
            ->assertOk();
    }
}
