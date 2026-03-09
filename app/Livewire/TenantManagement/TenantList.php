<?php

declare(strict_types=1);

namespace App\Livewire\TenantManagement;

use App\Models\Tenant;
use App\Services\TenantService;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('components.layouts.app')]
class TenantList extends Component
{
    use WithPagination;

    #[Url]
    public string $search = '';

    #[Url]
    public string $statusFilter = '';

    #[Url]
    public string $planFilter = '';

    protected $listeners = [
        'tenantSaved' => '$refresh',
    ];

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedStatusFilter(): void
    {
        $this->resetPage();
    }

    public function updatedPlanFilter(): void
    {
        $this->resetPage();
    }

    public function toggleStatus(int $id): void
    {
        $tenant = Tenant::findOrFail($id);
        $service = app(TenantService::class);

        if ($tenant->isActive()) {
            $service->suspend($tenant);
            $this->dispatch('toast', message: "Tenant '{$tenant->name}' suspended.", type: 'warning');
        } else {
            $service->activate($tenant);
            $this->dispatch('toast', message: "Tenant '{$tenant->name}' activated.", type: 'success');
        }
    }

    public function render(): View
    {
        $tenants = app(TenantService::class)->paginate([
            'search' => $this->search,
            'status' => $this->statusFilter ?: null,
            'plan' => $this->planFilter ?: null,
        ]);

        return view('livewire.tenant-management.tenant-list', [
            'tenants' => $tenants,
        ]);
    }
}
