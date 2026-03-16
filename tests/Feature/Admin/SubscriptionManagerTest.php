<?php

declare(strict_types=1);

namespace Tests\Feature\Admin;

use App\Models\Plan;
use App\Models\Subscription;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class SubscriptionManagerTest extends TestCase
{
    use RefreshDatabase;

    protected function createSuperAdmin(): User
    {
        return User::factory()->create([
            'is_super_admin' => true,
            'tenant_id' => null,
        ]);
    }

    public function test_non_super_admin_cannot_access(): void
    {
        $tenant = Tenant::factory()->onboardingComplete()->create();
        $user = User::factory()->create(['tenant_id' => $tenant->id]);

        $this->actingAs($user)
            ->get(route('admin.subscriptions.index'))
            ->assertForbidden();
    }

    public function test_super_admin_can_view_subscription_list(): void
    {
        $admin = $this->createSuperAdmin();
        $plan = Plan::factory()->create();
        $tenant = Tenant::factory()->create(['plan_id' => $plan->id]);
        Subscription::factory()->active()->for($tenant)->for($plan)->create();

        $this->actingAs($admin)
            ->get(route('admin.subscriptions.index'))
            ->assertOk()
            ->assertSee($tenant->name);
    }

    public function test_super_admin_can_extend_trial(): void
    {
        $admin = $this->createSuperAdmin();
        $plan = Plan::factory()->create();
        $tenant = Tenant::factory()->create(['plan_id' => $plan->id]);
        $subscription = Subscription::factory()->trial()->for($tenant)->for($plan)->create();

        $originalEndsAt = $subscription->ends_at;

        Livewire::actingAs($admin)
            ->test(\App\Livewire\Admin\SubscriptionManager::class)
            ->call('extendTrial', $subscription->id);

        $subscription->refresh();
        $this->assertTrue($subscription->ends_at->greaterThan($originalEndsAt));
    }
}
