<?php

declare(strict_types=1);

namespace Tests\Feature\Setup;

use App\Models\Plan;
use App\Models\Subscription;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class OnboardingWizardTest extends TestCase
{
    use RefreshDatabase;

    private function createOnboardingUser(): array
    {
        $plan = Plan::factory()->create();
        $tenant = Tenant::factory()->create([
            'plan_id' => $plan->id,
            'setup_completed_at' => now(),
            'onboarding_completed_at' => null,
        ]);
        Subscription::factory()->trial()->for($tenant)->for($plan)->create();
        $user = User::factory()->create(['tenant_id' => $tenant->id]);

        // Bind tenant to container as ResolveTenant middleware would
        app()->instance('current.tenant', $tenant);

        return [$user, $tenant];
    }

    public function test_onboarding_requires_auth(): void
    {
        $this->get(route('onboarding'))
            ->assertRedirect(route('login'));
    }

    public function test_onboarding_renders_for_tenant_user(): void
    {
        [$user, $tenant] = $this->createOnboardingUser();

        $this->actingAs($user)
            ->get(route('onboarding'))
            ->assertOk()
            ->assertSee('What industry is your business in?');
    }

    public function test_onboarding_has_ten_steps(): void
    {
        [$user, $tenant] = $this->createOnboardingUser();

        Livewire::actingAs($user)
            ->test(\App\Livewire\Setup\OnboardingWizard::class)
            ->assertSet('totalSteps', 10);
    }

    public function test_onboarding_saves_industry(): void
    {
        [$user, $tenant] = $this->createOnboardingUser();

        Livewire::actingAs($user)
            ->test(\App\Livewire\Setup\OnboardingWizard::class)
            ->set('industry', 'technology')
            ->call('saveStep');

        $this->assertEquals('technology', $tenant->fresh()->industry);
    }

    public function test_onboarding_saves_country(): void
    {
        [$user, $tenant] = $this->createOnboardingUser();

        Livewire::actingAs($user)
            ->test(\App\Livewire\Setup\OnboardingWizard::class)
            ->set('step', 2)
            ->set('country', 'NG')
            ->call('saveStep');

        $this->assertEquals('NG', $tenant->fresh()->country);
    }

    public function test_onboarding_saves_address(): void
    {
        [$user, $tenant] = $this->createOnboardingUser();

        Livewire::actingAs($user)
            ->test(\App\Livewire\Setup\OnboardingWizard::class)
            ->set('step', 4)
            ->set('address', '123 Test Street, Lagos')
            ->call('saveStep');

        $this->assertEquals('123 Test Street, Lagos', $tenant->fresh()->address);
    }

    public function test_onboarding_saves_currency(): void
    {
        [$user, $tenant] = $this->createOnboardingUser();

        Livewire::actingAs($user)
            ->test(\App\Livewire\Setup\OnboardingWizard::class)
            ->set('step', 5)
            ->set('currency', 'USD')
            ->call('saveStep');

        $this->assertEquals('USD', $tenant->fresh()->currency);
    }

    public function test_complete_onboarding_sets_completed_at(): void
    {
        [$user, $tenant] = $this->createOnboardingUser();

        Livewire::actingAs($user)
            ->test(\App\Livewire\Setup\OnboardingWizard::class)
            ->set('step', 10)
            ->call('completeOnboarding')
            ->assertRedirect(route('dashboard'));

        $this->assertNotNull($tenant->fresh()->onboarding_completed_at);
    }

    public function test_save_step_validates_industry_required(): void
    {
        [$user, $tenant] = $this->createOnboardingUser();

        Livewire::actingAs($user)
            ->test(\App\Livewire\Setup\OnboardingWizard::class)
            ->set('industry', '')
            ->call('saveStep')
            ->assertHasErrors(['industry'])
            ->assertSet('step', 1);
    }

    public function test_save_step_validates_country_required(): void
    {
        [$user, $tenant] = $this->createOnboardingUser();

        Livewire::actingAs($user)
            ->test(\App\Livewire\Setup\OnboardingWizard::class)
            ->set('step', 2)
            ->set('country', '')
            ->call('saveStep')
            ->assertHasErrors(['country'])
            ->assertSet('step', 2);
    }

    public function test_save_step_validates_warehouse_name_required(): void
    {
        [$user, $tenant] = $this->createOnboardingUser();

        Livewire::actingAs($user)
            ->test(\App\Livewire\Setup\OnboardingWizard::class)
            ->set('step', 7)
            ->set('warehouseName', '')
            ->call('saveStep')
            ->assertHasErrors(['warehouseName'])
            ->assertSet('step', 7);
    }

    public function test_save_step_validates_invite_email_required(): void
    {
        [$user, $tenant] = $this->createOnboardingUser();

        Livewire::actingAs($user)
            ->test(\App\Livewire\Setup\OnboardingWizard::class)
            ->set('step', 8)
            ->set('inviteEmail', '')
            ->call('saveStep')
            ->assertHasErrors(['inviteEmail'])
            ->assertSet('step', 8);
    }

    public function test_save_step_validates_category_name_required(): void
    {
        [$user, $tenant] = $this->createOnboardingUser();

        Livewire::actingAs($user)
            ->test(\App\Livewire\Setup\OnboardingWizard::class)
            ->set('step', 9)
            ->set('categoryName', '')
            ->call('saveStep')
            ->assertHasErrors(['categoryName'])
            ->assertSet('step', 9);
    }
}
