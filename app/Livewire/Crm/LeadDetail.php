<?php

declare(strict_types=1);

namespace App\Livewire\Crm;

use App\Models\Lead;
use App\Services\LeadService;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.app')]
class LeadDetail extends Component
{
    public Lead $lead;

    public bool $showConvertModal = false;

    public bool $createContact = true;

    public bool $showLostModal = false;

    public string $lostReason = '';

    public function mount(Lead $lead): void
    {
        $this->lead = $lead->load(['assignedUser', 'convertedCustomer']);
    }

    public function updateStatus(string $status): void
    {
        $service = app(LeadService::class);

        try {
            $this->lead = $service->updateStatus($this->lead, $status);
            $this->lead->load(['assignedUser', 'convertedCustomer']);
            $this->dispatch('toast', message: 'Lead status updated.', type: 'success');
        } catch (\RuntimeException $e) {
            $this->dispatch('toast', message: $e->getMessage(), type: 'error');
        }
    }

    public function openConvertModal(): void
    {
        $this->createContact = true;
        $this->showConvertModal = true;
    }

    public function convert(): void
    {
        $service = app(LeadService::class);

        try {
            $service->convert($this->lead, $this->createContact);
            $this->lead = $this->lead->fresh(['assignedUser', 'convertedCustomer']);
            $this->showConvertModal = false;
            $this->dispatch('toast', message: 'Lead converted successfully.', type: 'success');
        } catch (\RuntimeException $e) {
            $this->dispatch('toast', message: $e->getMessage(), type: 'error');
        }
    }

    public function openLostModal(): void
    {
        $this->lostReason = '';
        $this->showLostModal = true;
    }

    public function markLost(): void
    {
        $this->validate(['lostReason' => 'required|string|min:3']);

        $service = app(LeadService::class);

        try {
            $this->lead = $service->markLost($this->lead, $this->lostReason);
            $this->lead->load(['assignedUser', 'convertedCustomer']);
            $this->showLostModal = false;
            $this->dispatch('toast', message: 'Lead marked as lost.', type: 'success');
        } catch (\RuntimeException $e) {
            $this->dispatch('toast', message: $e->getMessage(), type: 'error');
        }
    }

    public function render(): View
    {
        return view('livewire.crm.lead-detail');
    }
}
