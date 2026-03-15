<?php

declare(strict_types=1);

namespace App\Livewire\Admin;

use App\Models\Plan;
use App\Models\PlanFeature;
use App\Services\PlanService;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('components.layouts.app')]
class PlanManager extends Component
{
    use WithPagination;

    #[Url]
    public string $search = '';

    public bool $showModal = false;

    public ?int $editingPlanId = null;

    public string $name = '';

    public string $slug = '';

    public string $description = '';

    public string $monthlyPrice = '';

    public string $annualPrice = '';

    public int $trialDays = 14;

    public bool $isActive = true;

    public int $sortOrder = 0;

    /** @var array<int, array{key: string, value: string}> */
    public array $features = [];

    public function openCreate(): void
    {
        $this->resetForm();
        $this->showModal = true;
    }

    public function openEdit(int $planId): void
    {
        $plan = Plan::with('features')->findOrFail($planId);
        $this->editingPlanId = $plan->id;
        $this->name = $plan->name;
        $this->slug = $plan->slug;
        $this->description = $plan->description ?? '';
        $this->monthlyPrice = (string) $plan->monthly_price;
        $this->annualPrice = (string) $plan->annual_price;
        $this->trialDays = $plan->trial_days;
        $this->isActive = $plan->is_active;
        $this->sortOrder = $plan->sort_order;
        $this->features = $plan->features->map(fn (PlanFeature $f): array => [
            'key' => $f->feature_key,
            'value' => $f->feature_value,
        ])->toArray();
        $this->showModal = true;
    }

    public function addFeature(): void
    {
        $this->features[] = ['key' => '', 'value' => ''];
    }

    public function removeFeature(int $index): void
    {
        unset($this->features[$index]);
        $this->features = array_values($this->features);
    }

    public function save(): void
    {
        $this->validate([
            'name' => 'required|string|max:255',
            'slug' => 'required|string|max:255',
            'monthlyPrice' => 'required|numeric|min:0',
            'annualPrice' => 'required|numeric|min:0',
            'trialDays' => 'required|integer|min:0',
        ]);

        $planService = app(PlanService::class);

        $data = [
            'name' => $this->name,
            'slug' => $this->slug,
            'description' => $this->description ?: null,
            'monthly_price' => (float) $this->monthlyPrice,
            'annual_price' => (float) $this->annualPrice,
            'trial_days' => $this->trialDays,
            'is_active' => $this->isActive,
            'sort_order' => $this->sortOrder,
        ];

        if ($this->editingPlanId) {
            $plan = Plan::findOrFail($this->editingPlanId);
            $planService->update($plan, $data);
            $plan->features()->delete();
        } else {
            $plan = $planService->create($data);
        }

        foreach ($this->features as $feature) {
            if (! empty($feature['key'])) {
                PlanFeature::create([
                    'plan_id' => $plan->id,
                    'feature_key' => $feature['key'],
                    'feature_value' => $feature['value'],
                ]);
            }
        }

        $this->showModal = false;
        $this->resetForm();
        session()->flash('success', $this->editingPlanId ? 'Plan updated.' : 'Plan created.');
    }

    protected function resetForm(): void
    {
        $this->editingPlanId = null;
        $this->name = '';
        $this->slug = '';
        $this->description = '';
        $this->monthlyPrice = '';
        $this->annualPrice = '';
        $this->trialDays = 14;
        $this->isActive = true;
        $this->sortOrder = 0;
        $this->features = [];
    }

    public function render(): mixed
    {
        $planService = app(PlanService::class);

        return view('livewire.admin.plan-manager', [
            'plans' => $planService->paginate(['search' => $this->search]),
        ]);
    }
}
