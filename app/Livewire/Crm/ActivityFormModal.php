<?php

declare(strict_types=1);

namespace App\Livewire\Crm;

use App\Models\User;
use App\Services\CrmActivityService;
use Illuminate\Contracts\View\View;
use Livewire\Component;

class ActivityFormModal extends Component
{
    public bool $showModal = false;

    public ?int $editingId = null;

    public string $type = 'call';

    public string $subject = '';

    public string $description = '';

    public string $related_to_type = '';

    public int $related_to_id = 0;

    public string $assigned_to = '';

    public string $due_date = '';

    public string $notes = '';

    public function openModal(string $relatedType = '', int $relatedId = 0): void
    {
        $this->resetValidation();
        $this->reset(['editingId', 'type', 'subject', 'description', 'assigned_to', 'due_date', 'notes']);
        $this->type = 'call';
        $this->related_to_type = $relatedType;
        $this->related_to_id = $relatedId;
        $this->showModal = true;
    }

    public function save(): void
    {
        $this->validate([
            'type' => 'required|string',
            'subject' => 'required|string|max:255',
            'due_date' => 'nullable|date',
            'assigned_to' => 'nullable|exists:users,id',
        ]);

        $data = [
            'type' => $this->type,
            'subject' => $this->subject,
            'description' => $this->description ?: null,
            'related_to_type' => $this->related_to_type ?: null,
            'related_to_id' => $this->related_to_id ?: null,
            'assigned_to' => $this->assigned_to ? (int) $this->assigned_to : null,
            'due_date' => $this->due_date ?: null,
            'notes' => $this->notes ?: null,
            'status' => 'pending',
        ];

        $service = app(CrmActivityService::class);

        if ($this->editingId) {
            $activity = \App\Models\CrmActivity::findOrFail($this->editingId);
            $service->update($activity, $data);
            $this->dispatch('toast', message: 'Activity updated.', type: 'success');
        } else {
            $service->create($data);
            $this->dispatch('toast', message: 'Activity created.', type: 'success');
        }

        $this->showModal = false;
        $this->dispatch('activity-saved');
    }

    public function render(): View
    {
        return view('livewire.crm.activity-form-modal', [
            'users' => User::orderBy('name')->get(),
        ]);
    }
}
