<?php

declare(strict_types=1);

namespace App\Livewire\Crm;

use App\Models\Lead;
use App\Models\User;
use App\Services\LeadService;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.app')]
class LeadForm extends Component
{
    public ?int $leadId = null;

    public string $lead_name = '';

    public string $company_name = '';

    public string $email = '';

    public string $phone = '';

    public string $source = 'manual';

    public string $industry = '';

    public string $status = 'new';

    public string $assigned_to = '';

    public string $estimated_value = '0';

    public string $lead_score = '0';

    public string $notes = '';

    public function mount(?Lead $lead = null): void
    {
        if ($lead && $lead->exists) {
            $this->leadId = $lead->id;
            $this->lead_name = $lead->lead_name;
            $this->company_name = $lead->company_name ?? '';
            $this->email = $lead->email ?? '';
            $this->phone = $lead->phone ?? '';
            $this->source = $lead->source ?? 'manual';
            $this->industry = $lead->industry ?? '';
            $this->status = $lead->status;
            $this->assigned_to = (string) ($lead->assigned_to ?? '');
            $this->estimated_value = (string) $lead->estimated_value;
            $this->lead_score = (string) $lead->lead_score;
            $this->notes = $lead->notes ?? '';
        }
    }

    public function save(): void
    {
        $this->validate([
            'lead_name' => 'required|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:50',
            'source' => 'required|string',
            'estimated_value' => 'required|numeric|min:0',
            'lead_score' => 'required|integer|min:0',
        ]);

        $data = [
            'lead_name' => $this->lead_name,
            'company_name' => $this->company_name ?: null,
            'email' => $this->email ?: null,
            'phone' => $this->phone ?: null,
            'source' => $this->source,
            'industry' => $this->industry ?: null,
            'status' => $this->status,
            'assigned_to' => $this->assigned_to ? (int) $this->assigned_to : null,
            'estimated_value' => (float) $this->estimated_value,
            'lead_score' => (int) $this->lead_score,
            'notes' => $this->notes ?: null,
        ];

        $service = app(LeadService::class);

        if ($this->leadId) {
            $lead = Lead::findOrFail($this->leadId);
            $service->update($lead, $data);
            $this->dispatch('toast', message: 'Lead updated successfully.', type: 'success');
        } else {
            $service->create($data);
            $this->dispatch('toast', message: 'Lead created successfully.', type: 'success');
        }

        $this->redirect(route('crm.leads.index'), navigate: true);
    }

    public function render(): View
    {
        return view('livewire.crm.lead-form', [
            'users' => User::orderBy('name')->get(),
        ]);
    }
}
