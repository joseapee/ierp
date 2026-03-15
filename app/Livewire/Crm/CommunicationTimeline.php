<?php

declare(strict_types=1);

namespace App\Livewire\Crm;

use App\Services\CrmCommunicationService;
use Illuminate\Contracts\View\View;
use Livewire\Component;

class CommunicationTimeline extends Component
{
    public ?int $customerId = null;

    public ?int $leadId = null;

    public bool $showModal = false;

    public string $type = 'email';

    public string $subject = '';

    public string $message = '';

    public string $contact_id = '';

    public function mount(?int $customerId = null, ?int $leadId = null): void
    {
        $this->customerId = $customerId;
        $this->leadId = $leadId;
    }

    public function openModal(): void
    {
        $this->resetValidation();
        $this->reset(['type', 'subject', 'message', 'contact_id']);
        $this->type = 'email';
        $this->showModal = true;
    }

    public function save(): void
    {
        $this->validate([
            'type' => 'required|string',
            'subject' => 'required|string|max:255',
            'message' => 'required|string',
        ]);

        $data = [
            'type' => $this->type,
            'subject' => $this->subject,
            'message' => $this->message,
            'customer_id' => $this->customerId,
            'lead_id' => $this->leadId,
            'contact_id' => $this->contact_id ? (int) $this->contact_id : null,
            'created_by' => auth()->id(),
        ];

        app(CrmCommunicationService::class)->create($data);

        $this->showModal = false;
        $this->dispatch('toast', message: 'Communication logged.', type: 'success');
    }

    public function render(): View
    {
        $service = app(CrmCommunicationService::class);

        if ($this->customerId) {
            $communications = $service->getCustomerTimeline($this->customerId);
        } elseif ($this->leadId) {
            $communications = $service->getLeadTimeline($this->leadId);
        } else {
            $communications = collect();
        }

        return view('livewire.crm.communication-timeline', [
            'communications' => $communications,
        ]);
    }
}
