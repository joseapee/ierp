<?php

declare(strict_types=1);

namespace App\Livewire\Setup;

use App\Models\Plan;
use App\Models\Tenant;
use App\Services\SubscriptionService;
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

    public function updatedBusinessName(string $value): void
    {
        $this->slug = Str::slug($value);
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

        $user = auth()->user();
        $user->update(['tenant_id' => $tenant->id]);

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
