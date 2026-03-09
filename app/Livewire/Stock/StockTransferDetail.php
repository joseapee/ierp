<?php

declare(strict_types=1);

namespace App\Livewire\Stock;

use App\Models\StockTransfer;
use App\Services\StockMovementService;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.app')]
class StockTransferDetail extends Component
{
    public StockTransfer $transfer;

    public function mount(StockTransfer $transfer): void
    {
        $this->transfer = $transfer->load(['fromWarehouse', 'toWarehouse', 'initiatedBy', 'completedBy', 'items.product', 'items.productVariant']);
    }

    public function ship(): void
    {
        app(StockMovementService::class)->shipTransfer($this->transfer);
        $this->transfer->refresh();
        $this->dispatch('toast', message: 'Transfer shipped. Stock deducted from source.', type: 'success');
    }

    public function complete(): void
    {
        app(StockMovementService::class)->completeTransfer($this->transfer, auth()->id());
        $this->transfer->refresh();
        $this->dispatch('toast', message: 'Transfer completed. Stock added to destination.', type: 'success');
    }

    public function cancel(): void
    {
        app(StockMovementService::class)->cancelTransfer($this->transfer);
        $this->transfer->refresh();
        $this->dispatch('toast', message: 'Transfer cancelled.', type: 'success');
    }

    public function render(): View
    {
        return view('livewire.stock.stock-transfer-detail');
    }
}
