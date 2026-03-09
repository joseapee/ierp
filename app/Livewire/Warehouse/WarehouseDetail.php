<?php

declare(strict_types=1);

namespace App\Livewire\Warehouse;

use App\Models\Warehouse;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.app')]
class WarehouseDetail extends Component
{
    public Warehouse $warehouse;

    protected $listeners = [
        'warehouseSaved' => '$refresh',
    ];

    public function mount(Warehouse $warehouse): void
    {
        $this->warehouse = $warehouse->load(['locations', 'stockBatches.product']);
    }

    public function render(): View
    {
        return view('livewire.warehouse.warehouse-detail');
    }
}
