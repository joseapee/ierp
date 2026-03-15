<?php

declare(strict_types=1);

namespace App\Livewire\Sales;

use App\Models\Customer;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.app')]
class CustomerForm extends Component
{
    public ?int $customerId = null;

    public string $name = '';

    public string $email = '';

    public string $phone = '';

    public string $tax_id = '';

    public string $billing_address_line1 = '';

    public string $billing_address_line2 = '';

    public string $billing_city = '';

    public string $billing_state = '';

    public string $billing_postal_code = '';

    public string $billing_country = 'NG';

    public string $shipping_address_line1 = '';

    public string $shipping_address_line2 = '';

    public string $shipping_city = '';

    public string $shipping_state = '';

    public string $shipping_postal_code = '';

    public string $shipping_country = 'NG';

    public string $credit_limit = '0';

    public int $payment_terms = 30;

    public string $currency_code = 'NGN';

    public bool $is_active = true;

    public string $notes = '';

    public function mount(?Customer $customer = null): void
    {
        if ($customer && $customer->exists) {
            $this->customerId = $customer->id;
            $this->name = $customer->name;
            $this->email = $customer->email ?? '';
            $this->phone = $customer->phone ?? '';
            $this->tax_id = $customer->tax_id ?? '';
            $this->billing_address_line1 = $customer->billing_address_line1 ?? '';
            $this->billing_address_line2 = $customer->billing_address_line2 ?? '';
            $this->billing_city = $customer->billing_city ?? '';
            $this->billing_state = $customer->billing_state ?? '';
            $this->billing_postal_code = $customer->billing_postal_code ?? '';
            $this->billing_country = $customer->billing_country ?? 'NG';
            $this->shipping_address_line1 = $customer->shipping_address_line1 ?? '';
            $this->shipping_address_line2 = $customer->shipping_address_line2 ?? '';
            $this->shipping_city = $customer->shipping_city ?? '';
            $this->shipping_state = $customer->shipping_state ?? '';
            $this->shipping_postal_code = $customer->shipping_postal_code ?? '';
            $this->shipping_country = $customer->shipping_country ?? 'NG';
            $this->credit_limit = (string) $customer->credit_limit;
            $this->payment_terms = $customer->payment_terms ?? 30;
            $this->currency_code = $customer->currency_code ?? 'NGN';
            $this->is_active = $customer->is_active;
            $this->notes = $customer->notes ?? '';
        }
    }

    public function save(): void
    {
        $this->validate([
            'name' => 'required|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:50',
            'credit_limit' => 'required|numeric|min:0',
            'payment_terms' => 'required|integer|min:0',
        ]);

        $data = [
            'name' => $this->name,
            'email' => $this->email ?: null,
            'phone' => $this->phone ?: null,
            'tax_id' => $this->tax_id ?: null,
            'billing_address_line1' => $this->billing_address_line1 ?: null,
            'billing_address_line2' => $this->billing_address_line2 ?: null,
            'billing_city' => $this->billing_city ?: null,
            'billing_state' => $this->billing_state ?: null,
            'billing_postal_code' => $this->billing_postal_code ?: null,
            'billing_country' => $this->billing_country ?: null,
            'shipping_address_line1' => $this->shipping_address_line1 ?: null,
            'shipping_address_line2' => $this->shipping_address_line2 ?: null,
            'shipping_city' => $this->shipping_city ?: null,
            'shipping_state' => $this->shipping_state ?: null,
            'shipping_postal_code' => $this->shipping_postal_code ?: null,
            'shipping_country' => $this->shipping_country ?: null,
            'credit_limit' => (float) $this->credit_limit,
            'payment_terms' => $this->payment_terms,
            'currency_code' => $this->currency_code,
            'is_active' => $this->is_active,
            'notes' => $this->notes ?: null,
        ];

        if ($this->customerId) {
            Customer::findOrFail($this->customerId)->update($data);
            $this->dispatch('toast', message: 'Customer updated successfully.', type: 'success');
        } else {
            Customer::create($data);
            $this->dispatch('toast', message: 'Customer created successfully.', type: 'success');
        }

        $this->redirect(route('sales.customers.index'), navigate: true);
    }

    public function render(): View
    {
        return view('livewire.sales.customer-form');
    }
}
