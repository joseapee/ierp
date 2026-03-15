<?php

declare(strict_types=1);

namespace App\Livewire\Sales;

use App\Models\SalesOrder;
use App\Services\SalesOrderService;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.app')]
class SalesOrderDetail extends Component
{
    public SalesOrder $order;

    public bool $showFulfillModal = false;

    public bool $showCancelModal = false;

    public string $cancellationReason = '';

    /** @var array<int, array{sales_order_item_id: int, quantity_fulfilled: string, max: float}> */
    public array $fulfillItems = [];

    public function mount(SalesOrder $order): void
    {
        $this->order = $order->load(['customer', 'items.product', 'items.warehouse']);
    }

    public function confirm(): void
    {
        $service = app(SalesOrderService::class);

        try {
            $this->order = $service->confirm($this->order);
            $this->dispatch('toast', message: 'Sales order confirmed.', type: 'success');
        } catch (\RuntimeException $e) {
            $this->dispatch('toast', message: $e->getMessage(), type: 'error');
        }
    }

    public function openFulfillModal(): void
    {
        $this->fulfillItems = [];

        foreach ($this->order->items as $item) {
            $remaining = (float) $item->quantity - (float) $item->quantity_fulfilled;

            if ($remaining > 0) {
                $this->fulfillItems[] = [
                    'sales_order_item_id' => $item->id,
                    'quantity_fulfilled' => (string) $remaining,
                    'max' => $remaining,
                ];
            }
        }

        $this->showFulfillModal = true;
    }

    public function fulfillItems(): void
    {
        $itemsToFulfill = collect($this->fulfillItems)
            ->filter(fn ($item) => (float) $item['quantity_fulfilled'] > 0)
            ->map(fn ($item) => [
                'sales_order_item_id' => $item['sales_order_item_id'],
                'quantity_fulfilled' => (float) $item['quantity_fulfilled'],
            ])
            ->values()
            ->all();

        if (empty($itemsToFulfill)) {
            $this->dispatch('toast', message: 'No quantities to fulfill.', type: 'error');

            return;
        }

        $service = app(SalesOrderService::class);

        try {
            $this->order = $service->fulfillItems($this->order, $itemsToFulfill);
            $this->order->load(['customer', 'items.product', 'items.warehouse']);
            $this->showFulfillModal = false;
            $this->dispatch('toast', message: 'Items fulfilled successfully.', type: 'success');
        } catch (\RuntimeException $e) {
            $this->dispatch('toast', message: $e->getMessage(), type: 'error');
        }
    }

    public function openCancelModal(): void
    {
        $this->cancellationReason = '';
        $this->showCancelModal = true;
    }

    public function cancelOrder(): void
    {
        $this->validate(['cancellationReason' => 'required|string|min:3']);

        $service = app(SalesOrderService::class);
        $this->order = $service->cancel($this->order, $this->cancellationReason);
        $this->showCancelModal = false;
        $this->dispatch('toast', message: 'Sales order cancelled.', type: 'success');
    }

    public function render(): View
    {
        return view('livewire.sales.sales-order-detail');
    }
}
