<?php

declare(strict_types=1);

namespace App\Livewire\Procurement;

use App\Models\PurchaseOrder;
use App\Services\PurchaseOrderService;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.app')]
class PurchaseOrderDetail extends Component
{
    public PurchaseOrder $purchaseOrder;

    public bool $showReceiveModal = false;

    public bool $showCancelModal = false;

    public string $cancellationReason = '';

    /** @var array<int, array{purchase_order_item_id: int, quantity_received: string, max: float}> */
    public array $receiveItems = [];

    public function mount(PurchaseOrder $purchaseOrder): void
    {
        $this->purchaseOrder = $purchaseOrder->load(['supplier', 'items.product', 'items.warehouse']);
    }

    public function confirm(): void
    {
        $service = app(PurchaseOrderService::class);
        $this->purchaseOrder = $service->confirm($this->purchaseOrder);
        $this->dispatch('toast', message: 'Purchase order confirmed.', type: 'success');
    }

    public function openReceiveModal(): void
    {
        $this->receiveItems = [];

        foreach ($this->purchaseOrder->items as $item) {
            $remaining = (float) $item->quantity - (float) $item->quantity_received;

            if ($remaining > 0) {
                $this->receiveItems[] = [
                    'purchase_order_item_id' => $item->id,
                    'quantity_received' => (string) $remaining,
                    'max' => $remaining,
                ];
            }
        }

        $this->showReceiveModal = true;
    }

    public function receiveItems(): void
    {
        $itemsToReceive = collect($this->receiveItems)
            ->filter(fn ($item) => (float) $item['quantity_received'] > 0)
            ->map(fn ($item) => [
                'purchase_order_item_id' => $item['purchase_order_item_id'],
                'quantity_received' => (float) $item['quantity_received'],
            ])
            ->values()
            ->all();

        if (empty($itemsToReceive)) {
            $this->dispatch('toast', message: 'No quantities to receive.', type: 'error');

            return;
        }

        $service = app(PurchaseOrderService::class);
        $this->purchaseOrder = $service->receiveItems($this->purchaseOrder, $itemsToReceive);
        $this->purchaseOrder->load(['supplier', 'items.product', 'items.warehouse']);
        $this->showReceiveModal = false;
        $this->dispatch('toast', message: 'Items received successfully.', type: 'success');
    }

    public function openCancelModal(): void
    {
        $this->cancellationReason = '';
        $this->showCancelModal = true;
    }

    public function cancelOrder(): void
    {
        $this->validate(['cancellationReason' => 'required|string|min:3']);

        $service = app(PurchaseOrderService::class);
        $this->purchaseOrder = $service->cancel($this->purchaseOrder, $this->cancellationReason);
        $this->showCancelModal = false;
        $this->dispatch('toast', message: 'Purchase order cancelled.', type: 'success');
    }

    public function render(): View
    {
        return view('livewire.procurement.purchase-order-detail');
    }
}
