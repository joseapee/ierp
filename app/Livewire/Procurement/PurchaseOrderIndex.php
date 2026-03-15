<?php

declare(strict_types=1);

namespace App\Livewire\Procurement;

use App\Models\PurchaseOrder;
use App\Models\Supplier;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('components.layouts.app')]
class PurchaseOrderIndex extends Component
{
    use WithPagination;

    #[Url]
    public string $search = '';

    #[Url]
    public string $statusFilter = '';

    #[Url]
    public string $supplierFilter = '';

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedStatusFilter(): void
    {
        $this->resetPage();
    }

    public function updatedSupplierFilter(): void
    {
        $this->resetPage();
    }

    public function render(): View
    {
        $orders = PurchaseOrder::query()
            ->with(['supplier'])
            ->when($this->search, fn ($q, $s) => $q->where(fn ($q) => $q
                ->where('order_number', 'like', "%{$s}%")
                ->orWhereHas('supplier', fn ($q) => $q->where('name', 'like', "%{$s}%"))
            ))
            ->when($this->statusFilter, fn ($q, $v) => $q->where('status', $v))
            ->when($this->supplierFilter, fn ($q, $v) => $q->where('supplier_id', $v))
            ->latest()
            ->paginate(15);

        return view('livewire.procurement.purchase-order-index', [
            'orders' => $orders,
            'suppliers' => Supplier::query()->active()->orderBy('name')->get(),
        ]);
    }
}
