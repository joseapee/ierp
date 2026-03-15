<?php

declare(strict_types=1);

namespace App\Livewire\Procurement;

use App\Models\Supplier;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.app')]
class SupplierForm extends Component
{
    public ?int $supplierId = null;

    public string $name = '';

    public string $email = '';

    public string $phone = '';

    public string $contact_person = '';

    public string $tax_id = '';

    public string $address_line1 = '';

    public string $address_line2 = '';

    public string $city = '';

    public string $state = '';

    public string $postal_code = '';

    public string $country = 'NG';

    public int $payment_terms = 30;

    public string $lead_time_days = '';

    public string $currency_code = 'NGN';

    public bool $is_active = true;

    public string $notes = '';

    public function mount(?Supplier $supplier = null): void
    {
        if ($supplier && $supplier->exists) {
            $this->supplierId = $supplier->id;
            $this->name = $supplier->name;
            $this->email = $supplier->email ?? '';
            $this->phone = $supplier->phone ?? '';
            $this->contact_person = $supplier->contact_person ?? '';
            $this->tax_id = $supplier->tax_id ?? '';
            $this->address_line1 = $supplier->address_line1 ?? '';
            $this->address_line2 = $supplier->address_line2 ?? '';
            $this->city = $supplier->city ?? '';
            $this->state = $supplier->state ?? '';
            $this->postal_code = $supplier->postal_code ?? '';
            $this->country = $supplier->country ?? 'NG';
            $this->payment_terms = $supplier->payment_terms ?? 30;
            $this->lead_time_days = $supplier->lead_time_days !== null ? (string) $supplier->lead_time_days : '';
            $this->currency_code = $supplier->currency_code ?? 'NGN';
            $this->is_active = $supplier->is_active;
            $this->notes = $supplier->notes ?? '';
        }
    }

    public function save(): void
    {
        $this->validate([
            'name' => 'required|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:50',
            'payment_terms' => 'required|integer|min:0',
        ]);

        $data = [
            'name' => $this->name,
            'email' => $this->email ?: null,
            'phone' => $this->phone ?: null,
            'contact_person' => $this->contact_person ?: null,
            'tax_id' => $this->tax_id ?: null,
            'address_line1' => $this->address_line1 ?: null,
            'address_line2' => $this->address_line2 ?: null,
            'city' => $this->city ?: null,
            'state' => $this->state ?: null,
            'postal_code' => $this->postal_code ?: null,
            'country' => $this->country ?: null,
            'payment_terms' => $this->payment_terms,
            'lead_time_days' => $this->lead_time_days !== '' ? (int) $this->lead_time_days : null,
            'currency_code' => $this->currency_code,
            'is_active' => $this->is_active,
            'notes' => $this->notes ?: null,
        ];

        if ($this->supplierId) {
            Supplier::findOrFail($this->supplierId)->update($data);
            $this->dispatch('toast', message: 'Supplier updated successfully.', type: 'success');
        } else {
            Supplier::create($data);
            $this->dispatch('toast', message: 'Supplier created successfully.', type: 'success');
        }

        $this->redirect(route('procurement.suppliers.index'), navigate: true);
    }

    public function render(): View
    {
        return view('livewire.procurement.supplier-form');
    }
}
