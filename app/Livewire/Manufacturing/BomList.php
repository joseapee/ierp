<?php

declare(strict_types=1);

namespace App\Livewire\Manufacturing;

use App\Models\Bom;
use App\Services\BomService;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('components.layouts.app')]
class BomList extends Component
{
    use WithPagination;

    #[Url]
    public string $search = '';

    #[Url]
    public string $statusFilter = '';

    protected $listeners = [
        'bomSaved' => '$refresh',
    ];

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedStatusFilter(): void
    {
        $this->resetPage();
    }

    public function deleteBom(int $id): void
    {
        $bom = Bom::findOrFail($id);
        app(BomService::class)->delete($bom);
        $this->dispatch('toast', message: 'BOM deleted successfully.', type: 'success');
    }

    public function activateBom(int $id): void
    {
        $bom = Bom::findOrFail($id);
        app(BomService::class)->activate($bom);
        $this->dispatch('toast', message: 'BOM activated successfully.', type: 'success');
    }

    public function duplicateBom(int $id): void
    {
        $bom = Bom::findOrFail($id);
        $newVersion = ((float) $bom->version + 0.1);
        app(BomService::class)->duplicate($bom, number_format($newVersion, 1));
        $this->dispatch('toast', message: 'BOM duplicated successfully.', type: 'success');
    }

    public function render(): View
    {
        $boms = app(BomService::class)->paginate([
            'search' => $this->search,
            'status' => $this->statusFilter ?: null,
        ]);

        return view('livewire.manufacturing.bom-list', [
            'boms' => $boms,
        ]);
    }
}
