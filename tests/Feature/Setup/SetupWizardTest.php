<?php

declare(strict_types=1);

namespace Tests\Feature\Setup;

use App\Livewire\Setup\SetupWizard;
use App\Models\Plan;
use App\Models\Role;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class SetupWizardTest extends TestCase
{
    use RefreshDatabase;

    public function test_setup_requires_auth(): void
    {
        $this->get(route('setup'))
            ->assertRedirect(route('login'));
    }

    public function test_setup_renders_for_new_user(): void
    {
        $user = User::factory()->create(['tenant_id' => null]);

        $this->actingAs($user)
            ->get(route('setup'))
            ->assertOk()
            ->assertSee('Set Up Your Business');
    }

    public function test_complete_setup_creates_tenant_and_subscription(): void
    {
        $plan = Plan::factory()->create(['trial_days' => 14]);
        $user = User::factory()->create(['tenant_id' => null]);

        Livewire::actingAs($user)
            ->test(SetupWizard::class)
            ->set('businessName', 'Test Company')
            ->set('slug', 'test-company')
            ->call('nextStep')
            ->set('selectedPlanId', $plan->id)
            ->set('billingCycle', 'monthly')
            ->call('completeSetup')
            ->assertRedirect(route('onboarding'));

        $user->refresh();
        $this->assertNotNull($user->tenant_id);
        $this->assertEquals('test-company', $user->tenant->slug);
        $this->assertNotNull($user->tenant->setup_completed_at);
        $this->assertEquals(1, $user->tenant->subscriptions()->count());
        $this->assertEquals('trial', $user->tenant->subscriptions()->first()->status);
    }

    public function test_setup_validates_business_name_required(): void
    {
        $user = User::factory()->create(['tenant_id' => null]);

        Livewire::actingAs($user)
            ->test(SetupWizard::class)
            ->set('businessName', '')
            ->set('slug', '')
            ->call('nextStep')
            ->assertHasErrors(['businessName']);
    }

    public function test_setup_validates_unique_slug(): void
    {
        Tenant::factory()->create(['slug' => 'taken-slug']);
        $user = User::factory()->create(['tenant_id' => null]);

        Livewire::actingAs($user)
            ->test(SetupWizard::class)
            ->set('businessName', 'Some Name')
            ->set('slug', 'taken-slug')
            ->call('nextStep')
            ->assertHasErrors(['slug']);
    }

    public function test_setup_stores_billing_cycle(): void
    {
        $plan = Plan::factory()->create(['trial_days' => 14]);
        $user = User::factory()->create(['tenant_id' => null]);

        Livewire::actingAs($user)
            ->test(SetupWizard::class)
            ->set('businessName', 'Annual Company')
            ->set('slug', 'annual-company')
            ->call('nextStep')
            ->set('selectedPlanId', $plan->id)
            ->set('billingCycle', 'annual')
            ->call('completeSetup')
            ->assertRedirect(route('onboarding'));

        $user->refresh();
        $subscription = $user->tenant->subscriptions()->first();
        $this->assertEquals('annual', $subscription->billing_cycle);
    }

    public function test_setup_has_only_two_steps(): void
    {
        $user = User::factory()->create(['tenant_id' => null]);

        Livewire::actingAs($user)
            ->test(SetupWizard::class)
            ->assertSet('totalSteps', 2);
    }

    public function test_complete_setup_assigns_owner_role(): void
    {
        $plan = Plan::factory()->create(['trial_days' => 14]);
        $user = User::factory()->create(['tenant_id' => null]);

        Livewire::actingAs($user)
            ->test(SetupWizard::class)
            ->set('businessName', 'Role Test Co')
            ->set('slug', 'role-test-co')
            ->call('nextStep')
            ->set('selectedPlanId', $plan->id)
            ->set('billingCycle', 'monthly')
            ->call('completeSetup')
            ->assertRedirect(route('onboarding'));

        $user->refresh();
        $ownerRole = Role::query()
            ->where('slug', 'owner')
            ->where('tenant_id', $user->tenant_id)
            ->first();
        $this->assertNotNull($ownerRole, 'Owner role should have been seeded for the tenant');
        $this->assertTrue($user->roles->contains($ownerRole));
    }
}
