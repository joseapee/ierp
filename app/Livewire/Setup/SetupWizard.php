<?php

declare(strict_types=1);

namespace App\Livewire\Setup;

use App\Models\Plan;
use App\Models\Role;
use App\Models\Tenant;
use App\Services\RoleService;
use App\Services\SubscriptionService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.setup')]
class SetupWizard extends Component
{
    public int $step = 1;

    public int $totalSteps = 2;

    // Step 1: Business info
    public string $businessName = '';

    public string $slug = '';

    // Step 2: Plan selection + billing cycle
    public ?int $selectedPlanId = null;

    public string $billingCycle = 'monthly';

    public function mount(): void
    {
        $user = Auth::user();

        // If user already has a tenant with completed setup, move forward
        if ($user->tenant_id !== null) {
            $tenant = $user->tenant;

            if ($tenant && $tenant->setup_completed_at !== null) {
                if ($tenant->onboarding_completed_at === null) {
                    $this->redirect(route('onboarding'), navigate: true);
                } else {
                    $this->redirect(route('dashboard'), navigate: true);
                }
            }
        }
    }

    public function updatedBusinessName(string $value): void
    {
        // check if slug exists and assign a unique slug if it does
        $slug = Str::slug($value);
        $count = Tenant::query()->where('slug', 'like', "$slug%")->count();

        if ($count > 0) {
            $slug .= '-'.($count + 1);
        }

        $this->slug = $slug;
    }

    public function nextStep(): void
    {
        $this->validateStep();
        $this->step = min($this->step + 1, $this->totalSteps);
    }

    public function previousStep(): void
    {
        $this->step = max($this->step - 1, 1);
    }

    public function completeSetup(): void
    {
        $this->validateStep();

        $plan = Plan::query()->find($this->selectedPlanId);

        if (! $plan) {
            $this->addError('selectedPlanId', 'Please select a plan.');

            return;
        }

        $tenant = Tenant::create([
            'name' => $this->businessName,
            'slug' => $this->slug,
            'plan_id' => $plan->id,
            'plan' => $plan->slug,
            'status' => 'active',
            'currency' => 'NGN',
            'timezone' => 'Africa/Lagos',
            'setup_completed_at' => now(),
        ]);

        $user = Auth::user();
        $user->update(['tenant_id' => $tenant->id]);

        // Seed system roles for this tenant if not present (skip for demo tenant)
        if ($tenant->roles()->count() === 0) {
            app(RoleService::class)->seedSystemRolesForTenant($tenant);
        }

        // Assign the owner role to the tenant creator
        $ownerRole = Role::query()
            ->where('slug', 'owner')
            ->where('tenant_id', $tenant->id)
            ->first();

        if ($ownerRole) {
            $user->assignRole($ownerRole);
        } else {
            // return an error if owner role is not found (should not happen since we seed it on tenant creation)
            $this->addError('roleAssignment', 'Owner role not found. Please contact support.');

            return;
        }

        $subscriptionService = app(SubscriptionService::class);
        $subscriptionService->startTrial($tenant, $plan, billingCycle: $this->billingCycle);

        $this->redirect(route('onboarding'), navigate: true);
    }

    protected function validateStep(): void
    {
        match ($this->step) {
            1 => $this->validate([
                'businessName' => 'required|string|max:255',
                'slug' => 'required|string|max:255|unique:tenants,slug',
            ]),
            2 => $this->validate([
                'selectedPlanId' => 'required|exists:plans,id',
                'billingCycle' => 'required|in:monthly,annual',
            ]),
            default => null,
        };
    }

    public function render(): mixed
    {
        return view('livewire.setup.setup-wizard', [
            'plans' => Plan::query()->where('is_active', true)->orderBy('sort_order')->get(),
        ]);
    }
}
