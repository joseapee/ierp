<?php

declare(strict_types=1);

namespace App\Livewire\Stock;

use App\Models\StockAdjustment;
use App\Services\StockMovementService;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.app')]
class StockAdjustmentDetail extends Component
{
    public StockAdjustment $adjustment;

    public function mount(StockAdjustment $adjustment): void
    {
        $this->adjustment = $adjustment->load(['warehouse', 'adjustedBy', 'approvedBy', 'items.product', 'items.productVariant']);
    }

    public function approve(): void
    {
        $this->authorize('approve', $this->adjustment);
        app(StockMovementService::class)->approveAdjustment($this->adjustment, auth()->id());
        $this->adjustment->refresh();
        $this->dispatch('toast', message: 'Adjustment approved and stock updated.', type: 'success');
    }

    public function render(): View
    {
        return view('livewire.stock.stock-adjustment-detail');
    }
}
