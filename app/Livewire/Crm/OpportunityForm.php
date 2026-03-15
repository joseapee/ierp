<?php

declare(strict_types=1);

namespace App\Livewire\Crm;

use App\Models\CrmContact;
use App\Models\CrmPipelineStage;
use App\Models\Customer;
use App\Models\Opportunity;
use App\Models\User;
use App\Services\OpportunityService;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.app')]
class OpportunityForm extends Component
{
    public ?int $opportunityId = null;

    public string $name = '';

    public string $customer_id = '';

    public string $contact_id = '';

    public string $pipeline_stage_id = '';

    public string $expected_value = '0';

    public string $probability = '0';

    public string $expected_close_date = '';

    public string $assigned_to = '';

    public string $notes = '';

    public function mount(?Opportunity $opportunity = null): void
    {
        if ($opportunity && $opportunity->exists) {
            $this->opportunityId = $opportunity->id;
            $this->name = $opportunity->name;
            $this->customer_id = (string) $opportunity->customer_id;
            $this->contact_id = (string) ($opportunity->contact_id ?? '');
            $this->pipeline_stage_id = (string) $opportunity->pipeline_stage_id;
            $this->expected_value = (string) $opportunity->expected_value;
            $this->probability = (string) $opportunity->probability;
            $this->expected_close_date = $opportunity->expected_close_date?->format('Y-m-d') ?? '';
            $this->assigned_to = (string) ($opportunity->assigned_to ?? '');
            $this->notes = $opportunity->notes ?? '';
        }
    }

    public function save(): void
    {
        $this->validate([
            'name' => 'required|string|max:255',
            'customer_id' => 'required|exists:customers,id',
            'pipeline_stage_id' => 'required|exists:crm_pipeline_stages,id',
            'contact_id' => 'nullable|exists:crm_contacts,id',
            'expected_value' => 'required|numeric|min:0',
            'probability' => 'required|numeric|min:0|max:100',
            'expected_close_date' => 'nullable|date',
            'assigned_to' => 'nullable|exists:users,id',
        ]);

        $data = [
            'name' => $this->name,
            'customer_id' => (int) $this->customer_id,
            'contact_id' => $this->contact_id ? (int) $this->contact_id : null,
            'pipeline_stage_id' => (int) $this->pipeline_stage_id,
            'expected_value' => (float) $this->expected_value,
            'probability' => (float) $this->probability,
            'expected_close_date' => $this->expected_close_date ?: null,
            'assigned_to' => $this->assigned_to ? (int) $this->assigned_to : null,
            'notes' => $this->notes ?: null,
        ];

        $service = app(OpportunityService::class);

        if ($this->opportunityId) {
            $opportunity = Opportunity::findOrFail($this->opportunityId);
            $service->update($opportunity, $data);
            $this->dispatch('toast', message: 'Opportunity updated successfully.', type: 'success');
        } else {
            $service->create($data);
            $this->dispatch('toast', message: 'Opportunity created successfully.', type: 'success');
        }

        $this->redirect(route('crm.opportunities.index'), navigate: true);
    }

    public function render(): View
    {
        return view('livewire.crm.opportunity-form', [
            'customers' => Customer::orderBy('name')->get(),
            'contacts' => CrmContact::orderBy('first_name')->get(),
            'stages' => CrmPipelineStage::active()->ordered()->get(),
            'users' => User::orderBy('name')->get(),
        ]);
    }
}
