<?php

declare(strict_types=1);

namespace App\Livewire\Setup;

use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.setup')]
class OnboardingWizard extends Component
{
    public int $step = 1;

    public int $totalSteps = 10;

    // Step 1: Industry
    public string $industry = '';

    // Step 2: Country
    public string $country = '';

    // Step 3: City
    public string $city = '';

    // Step 4: Business address
    public string $address = '';

    // Step 5: Currency
    public string $currency = 'NGN';

    // Step 6: Timezone
    public string $timezone = 'Africa/Lagos';

    // Step 7: First warehouse
    public string $warehouseName = '';

    public string $warehouseLocation = '';

    // Step 8: Invite team member
    public string $inviteEmail = '';

    // Step 9: First category
    public string $categoryName = '';

    public function mount(): void
    {
        $tenant = app()->bound('current.tenant') ? app('current.tenant') : null;

        if ($tenant) {
            $this->industry = $tenant->industry ?? '';
            $this->country = $tenant->country ?? '';
            $this->city = $tenant->city ?? '';
            $this->address = $tenant->address ?? '';
            $this->currency = $tenant->currency ?? 'NGN';
            $this->timezone = $tenant->timezone ?? 'Africa/Lagos';
        }
    }

    public function previousStep(): void
    {
        $this->step = max($this->step - 1, 1);
    }

    public function saveStep(): void
    {
        $this->validateStep();

        $tenant = app('current.tenant');

        match ($this->step) {
            1 => $this->saveIndustry($tenant),
            2 => $this->saveCountry($tenant),
            3 => $this->saveCity($tenant),
            4 => $this->saveAddress($tenant),
            5 => $this->saveCurrency($tenant),
            6 => $this->saveTimezone($tenant),
            7 => $this->saveWarehouse($tenant),
            8 => $this->saveInvite(),
            9 => $this->saveCategory($tenant),
            default => null,
        };

        $this->step = min($this->step + 1, $this->totalSteps);
    }

    protected function validateStep(): void
    {
        match ($this->step) {
            1 => $this->validate(['industry' => ['required', 'string']]),
            2 => $this->validate(['country' => ['required', 'string']]),
            3 => $this->validate(['city' => ['required', 'string', 'max:255']]),
            4 => $this->validate(['address' => ['required', 'string', 'max:1000']]),
            5 => $this->validate(['currency' => ['required', 'string']]),
            6 => $this->validate(['timezone' => ['required', 'string']]),
            7 => $this->validate([
                'warehouseName' => ['required', 'string', 'max:255'],
                'warehouseLocation' => ['required', 'string', 'max:255'],
            ]),
            8 => $this->validate(['inviteEmail' => ['required', 'email', 'max:255']]),
            9 => $this->validate(['categoryName' => ['required', 'string', 'max:255']]),
            default => null,
        };
    }

    public function completeOnboarding(): void
    {
        $tenant = app('current.tenant');
        $tenant->update(['onboarding_completed_at' => now()]);

        $this->redirect(route('dashboard'), navigate: true);
    }

    protected function saveIndustry(mixed $tenant): void
    {
        $tenant->update(['industry' => $this->industry]);
    }

    protected function saveCountry(mixed $tenant): void
    {
        $tenant->update(['country' => $this->country]);
    }

    protected function saveCity(mixed $tenant): void
    {
        $tenant->update(['city' => $this->city]);
    }

    protected function saveAddress(mixed $tenant): void
    {
        $tenant->update(['address' => $this->address]);
    }

    protected function saveCurrency(mixed $tenant): void
    {
        $tenant->update(['currency' => $this->currency]);
    }

    protected function saveTimezone(mixed $tenant): void
    {
        $tenant->update(['timezone' => $this->timezone]);
    }

    protected function saveWarehouse(mixed $tenant): void
    {
        \App\Models\Warehouse::create([
            'tenant_id' => $tenant->id,
            'name' => $this->warehouseName,
            'location' => $this->warehouseLocation,
            'is_active' => true,
        ]);
    }

    protected function saveInvite(): void
    {
        // Invite functionality — placeholder for email invitation
        // Will be implemented when email service is configured
    }

    protected function saveCategory(mixed $tenant): void
    {
        \App\Models\Category::create([
            'tenant_id' => $tenant->id,
            'name' => $this->categoryName,
        ]);
    }

    public function render(): mixed
    {
        return view('livewire.setup.onboarding-wizard');
    }
}
