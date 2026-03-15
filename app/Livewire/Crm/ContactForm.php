<?php

declare(strict_types=1);

namespace App\Livewire\Crm;

use App\Models\CrmContact;
use App\Models\Customer;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.app')]
class ContactForm extends Component
{
    public ?int $contactId = null;

    public string $customer_id = '';

    public string $first_name = '';

    public string $last_name = '';

    public string $email = '';

    public string $phone = '';

    public string $job_title = '';

    public string $department = '';

    public bool $is_primary = false;

    public string $notes = '';

    public function mount(?CrmContact $contact = null): void
    {
        if ($contact && $contact->exists) {
            $this->contactId = $contact->id;
            $this->customer_id = (string) ($contact->customer_id ?? '');
            $this->first_name = $contact->first_name;
            $this->last_name = $contact->last_name;
            $this->email = $contact->email ?? '';
            $this->phone = $contact->phone ?? '';
            $this->job_title = $contact->job_title ?? '';
            $this->department = $contact->department ?? '';
            $this->is_primary = $contact->is_primary;
            $this->notes = $contact->notes ?? '';
        }
    }

    public function save(): void
    {
        $this->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'customer_id' => 'nullable|exists:customers,id',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:50',
        ]);

        $data = [
            'first_name' => $this->first_name,
            'last_name' => $this->last_name,
            'customer_id' => $this->customer_id ? (int) $this->customer_id : null,
            'email' => $this->email ?: null,
            'phone' => $this->phone ?: null,
            'job_title' => $this->job_title ?: null,
            'department' => $this->department ?: null,
            'is_primary' => $this->is_primary,
            'notes' => $this->notes ?: null,
        ];

        if ($this->contactId) {
            CrmContact::findOrFail($this->contactId)->update($data);
            $this->dispatch('toast', message: 'Contact updated successfully.', type: 'success');
        } else {
            CrmContact::create($data);
            $this->dispatch('toast', message: 'Contact created successfully.', type: 'success');
        }

        $this->redirect(route('crm.contacts.index'), navigate: true);
    }

    public function render(): View
    {
        return view('livewire.crm.contact-form', [
            'customers' => Customer::orderBy('name')->get(),
        ]);
    }
}
