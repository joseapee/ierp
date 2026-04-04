<?php

declare(strict_types=1);

namespace App\Livewire;

use App\Services\DashboardService;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.app')]
class Dashboard extends Component
{
    /** @var array<string, mixed> */
    public array $metrics = [];

    /** @var array<int, array{month: string, label: string, revenue: float, expenses: float}> */
    public array $monthlyTrend = [];

    /** @var array<string, int> */
    public array $salesStatusDistribution = [];

    /** @var array<int, array{name: string, quantity: float, revenue: float}> */
    public array $topProducts = [];

    /** @var array<int, array{name: string, orders: int, total: float}> */
    public array $topCustomers = [];

    /** @var array<int, array{stage: string, count: int, value: float}> */
    public array $pipelineFunnel = [];

    /** @var array<int, string> */
    public array $userPermissions = [];

    public function mount(DashboardService $service): void
    {
        $user = auth()->user();
        $this->userPermissions = $user->is_super_admin
            ? ['sales-orders.view', 'purchase-orders.view', 'customers.view', 'stock.view', 'products.view', 'leads.view', 'production.view', 'accounts.view', 'users.view']
            : $user->getAllPermissions();

        $can = fn (string $slug): bool => in_array($slug, $this->userPermissions, true);

        $this->metrics = $service->getMetrics($this->userPermissions);

        if ($can('sales-orders.view')) {
            $this->monthlyTrend = $service->getMonthlyTrend();
            $this->salesStatusDistribution = $service->getSalesStatusDistribution();
            $this->topProducts = $service->getTopProducts();
        }

        if ($can('customers.view')) {
            $this->topCustomers = $service->getTopCustomers();
        }

        if ($can('leads.view')) {
            $this->pipelineFunnel = $service->getPipelineFunnel();
        }
    }

    public function render(): View
    {
        $can = fn (string $slug): bool => in_array($slug, $this->userPermissions, true);
        $dashboardService = app(DashboardService::class);

        return view('livewire.dashboard', [
            'recentSalesOrders' => $can('sales-orders.view') ? $dashboardService->getRecentSalesOrders() : collect(),
            'recentPurchaseOrders' => $can('purchase-orders.view') ? $dashboardService->getRecentPurchaseOrders() : collect(),
            'lowStockProducts' => $can('stock.view') ? $dashboardService->getLowStockProducts() : collect(),
        ]);
    }
}
