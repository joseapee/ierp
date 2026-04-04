<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Account;
use App\Models\Customer;
use App\Models\JournalEntry;
use App\Models\JournalLine;
use App\Models\Lead;
use App\Models\Opportunity;
use App\Models\Product;
use App\Models\ProductionOrder;
use App\Models\PurchaseOrder;
use App\Models\SalesOrder;
use App\Models\StockLedger;
use App\Models\Supplier;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class DashboardService
{
    /**
     * Gather KPI metrics for the dashboard, filtered by the given permission slugs.
     *
     * @param  array<int, string>  $permissions  Permission slugs the current user holds
     * @return array<string, mixed>
     */
    public function getMetrics(array $permissions): array
    {
        $now = Carbon::now();
        $startOfMonth = $now->copy()->startOfMonth();
        $startOfLastMonth = $now->copy()->subMonth()->startOfMonth();
        $endOfLastMonth = $now->copy()->subMonth()->endOfMonth();

        $can = fn (string $slug): bool => in_array($slug, $permissions, true);

        $metrics = [];

        if ($can('sales-orders.view')) {
            $metrics['sales'] = $this->getSalesMetrics($startOfMonth, $startOfLastMonth, $endOfLastMonth);
        }

        if ($can('purchase-orders.view')) {
            $metrics['procurement'] = $this->getProcurementMetrics($startOfMonth, $startOfLastMonth, $endOfLastMonth);
        }

        if ($can('stock.view') || $can('products.view')) {
            $metrics['inventory'] = $this->getInventoryMetrics();
        }

        if ($can('leads.view')) {
            $metrics['crm'] = $this->getCrmMetrics($startOfMonth, $startOfLastMonth, $endOfLastMonth);
        }

        if ($can('production.view')) {
            $metrics['manufacturing'] = $this->getManufacturingMetrics($startOfMonth);
        }

        if ($can('accounts.view')) {
            $metrics['accounting'] = $this->getAccountingMetrics($startOfMonth);
        }

        if ($can('users.view')) {
            $metrics['users'] = $this->getUserMetrics();
        }

        return $metrics;
    }

    /**
     * Get monthly revenue data for the past 12 months (for charts).
     *
     * @return array<int, array{month: string, revenue: float, expenses: float}>
     */
    public function getMonthlyTrend(): array
    {
        $data = [];
        for ($i = 11; $i >= 0; $i--) {
            $date = Carbon::now()->subMonths($i);
            $start = $date->copy()->startOfMonth();
            $end = $date->copy()->endOfMonth();

            $revenue = (float) SalesOrder::query()
                ->whereNotIn('status', ['draft', 'cancelled'])
                ->whereBetween('order_date', [$start, $end])
                ->sum('total_amount');

            $expenses = (float) PurchaseOrder::query()
                ->whereNotIn('status', ['draft', 'cancelled'])
                ->whereBetween('order_date', [$start, $end])
                ->sum('total_amount');

            $data[] = [
                'month' => $date->format('M Y'),
                'label' => $date->format('M'),
                'revenue' => round($revenue, 2),
                'expenses' => round($expenses, 2),
            ];
        }

        return $data;
    }

    /**
     * Get sales order status distribution for donut chart.
     *
     * @return array<string, int>
     */
    public function getSalesStatusDistribution(): array
    {
        return SalesOrder::query()
            ->select('status', DB::raw('count(*) as count'))
            ->groupBy('status')
            ->pluck('count', 'status')
            ->toArray();
    }

    /**
     * Get top 5 products by sales quantity this month.
     *
     * @return array<int, array{name: string, quantity: float, revenue: float}>
     */
    public function getTopProducts(): array
    {
        $startOfMonth = Carbon::now()->startOfMonth();

        return DB::table('sales_order_items')
            ->join('sales_orders', 'sales_order_items.sales_order_id', '=', 'sales_orders.id')
            ->join('products', 'sales_order_items.product_id', '=', 'products.id')
            ->where('sales_orders.order_date', '>=', $startOfMonth)
            ->whereNotIn('sales_orders.status', ['draft', 'cancelled'])
            ->select(
                'products.name',
                DB::raw('SUM(sales_order_items.quantity) as total_quantity'),
                DB::raw('SUM(sales_order_items.total) as total_revenue')
            )
            ->groupBy('products.id', 'products.name')
            ->orderByDesc('total_revenue')
            ->limit(5)
            ->get()
            ->map(fn ($row) => [
                'name' => $row->name,
                'quantity' => round((float) $row->total_quantity, 2),
                'revenue' => round((float) $row->total_revenue, 2),
            ])
            ->toArray();
    }

    /**
     * Get top 5 customers by order value this month.
     *
     * @return array<int, array{name: string, orders: int, total: float}>
     */
    public function getTopCustomers(): array
    {
        $startOfMonth = Carbon::now()->startOfMonth();

        return SalesOrder::query()
            ->with('customer')
            ->select(
                'customer_id',
                DB::raw('COUNT(*) as order_count'),
                DB::raw('SUM(total_amount) as total_value')
            )
            ->where('order_date', '>=', $startOfMonth)
            ->whereNotIn('status', ['draft', 'cancelled'])
            ->groupBy('customer_id')
            ->orderByDesc('total_value')
            ->limit(5)
            ->get()
            ->map(fn ($row) => [
                'name' => $row->customer?->name ?? 'Unknown',
                'orders' => (int) $row->order_count,
                'total' => round((float) $row->total_value, 2),
            ])
            ->toArray();
    }

    /**
     * Get recent sales orders (last 10).
     */
    public function getRecentSalesOrders(): Collection
    {
        return SalesOrder::query()
            ->with('customer')
            ->latest('order_date')
            ->limit(10)
            ->get();
    }

    /**
     * Get recent purchase orders (last 10).
     */
    public function getRecentPurchaseOrders(): Collection
    {
        return PurchaseOrder::query()
            ->with('supplier')
            ->latest('order_date')
            ->limit(10)
            ->get();
    }

    /**
     * Get pipeline stage distribution for CRM funnel.
     *
     * @return array<int, array{stage: string, count: int, value: float}>
     */
    public function getPipelineFunnel(): array
    {
        return Opportunity::query()
            ->join('crm_pipeline_stages', 'opportunities.pipeline_stage_id', '=', 'crm_pipeline_stages.id')
            ->select(
                'crm_pipeline_stages.name as stage_name',
                'crm_pipeline_stages.display_order',
                DB::raw('COUNT(*) as count'),
                DB::raw('SUM(opportunities.expected_value) as total_value')
            )
            ->whereNull('opportunities.closed_at')
            ->groupBy('crm_pipeline_stages.id', 'crm_pipeline_stages.name', 'crm_pipeline_stages.display_order')
            ->orderBy('crm_pipeline_stages.display_order')
            ->get()
            ->map(fn ($row) => [
                'stage' => $row->stage_name,
                'count' => (int) $row->count,
                'value' => round((float) $row->total_value, 2),
            ])
            ->toArray();
    }

    /**
     * Get low-stock products (below reorder level).
     */
    public function getLowStockProducts(): Collection
    {
        return Product::query()
            ->where('is_stockable', true)
            ->where('is_active', true)
            ->where('reorder_level', '>', 0)
            ->select('products.*')
            ->selectSub(
                StockLedger::query()
                    ->selectRaw('COALESCE(SUM(quantity), 0)')
                    ->whereColumn('stock_ledger.product_id', 'products.id'),
                'current_stock'
            )
            ->whereRaw(
                '(SELECT COALESCE(SUM(quantity), 0) FROM stock_ledger WHERE stock_ledger.product_id = products.id) <= products.reorder_level'
            )
            ->orderByRaw(
                '(SELECT COALESCE(SUM(quantity), 0) FROM stock_ledger WHERE stock_ledger.product_id = products.id) ASC'
            )
            ->limit(10)
            ->get();
    }

    /**
     * @return array<string, mixed>
     */
    private function getSalesMetrics(Carbon $startOfMonth, Carbon $startOfLastMonth, Carbon $endOfLastMonth): array
    {
        $currentMonthSales = SalesOrder::query()
            ->where('order_date', '>=', $startOfMonth)
            ->whereNotIn('status', ['draft', 'cancelled']);

        $lastMonthSales = SalesOrder::query()
            ->whereBetween('order_date', [$startOfLastMonth, $endOfLastMonth])
            ->whereNotIn('status', ['draft', 'cancelled']);

        $currentRevenue = (float) $currentMonthSales->sum('total_amount');
        $lastRevenue = (float) $lastMonthSales->sum('total_amount');
        $revenueChange = $lastRevenue > 0 ? round((($currentRevenue - $lastRevenue) / $lastRevenue) * 100, 1) : 0;

        $currentCount = $currentMonthSales->count();
        $lastCount = $lastMonthSales->count();

        $totalCustomers = Customer::query()->active()->count();
        $pendingOrders = SalesOrder::query()->whereIn('status', ['draft', 'confirmed'])->count();

        return [
            'revenue' => $currentRevenue,
            'revenue_change' => $revenueChange,
            'order_count' => $currentCount,
            'last_month_count' => $lastCount,
            'total_customers' => $totalCustomers,
            'pending_orders' => $pendingOrders,
            'average_order_value' => $currentCount > 0 ? round($currentRevenue / $currentCount, 2) : 0,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function getProcurementMetrics(Carbon $startOfMonth, Carbon $startOfLastMonth, Carbon $endOfLastMonth): array
    {
        $currentExpenses = (float) PurchaseOrder::query()
            ->where('order_date', '>=', $startOfMonth)
            ->whereNotIn('status', ['draft', 'cancelled'])
            ->sum('total_amount');

        $lastExpenses = (float) PurchaseOrder::query()
            ->whereBetween('order_date', [$startOfLastMonth, $endOfLastMonth])
            ->whereNotIn('status', ['draft', 'cancelled'])
            ->sum('total_amount');

        $expenseChange = $lastExpenses > 0 ? round((($currentExpenses - $lastExpenses) / $lastExpenses) * 100, 1) : 0;

        $totalSuppliers = Supplier::query()->active()->count();
        $pendingPOs = PurchaseOrder::query()->whereIn('status', ['draft', 'confirmed'])->count();

        return [
            'expenses' => $currentExpenses,
            'expense_change' => $expenseChange,
            'total_suppliers' => $totalSuppliers,
            'pending_orders' => $pendingPOs,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function getInventoryMetrics(): array
    {
        $totalProducts = Product::query()->where('is_active', true)->count();
        $stockableProducts = Product::query()->where('is_stockable', true)->where('is_active', true)->count();

        $totalStockValue = (float) DB::table('stock_batches')
            ->where('status', 'available')
            ->selectRaw('SUM(remaining_quantity * unit_cost) as value')
            ->value('value') ?? 0;

        $lowStockCount = Product::query()
            ->where('is_stockable', true)
            ->where('is_active', true)
            ->where('reorder_level', '>', 0)
            ->whereRaw(
                '(SELECT COALESCE(SUM(quantity), 0) FROM stock_ledger WHERE stock_ledger.product_id = products.id) <= products.reorder_level'
            )
            ->count();

        return [
            'total_products' => $totalProducts,
            'stockable_products' => $stockableProducts,
            'total_stock_value' => round($totalStockValue, 2),
            'low_stock_count' => $lowStockCount,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function getCrmMetrics(Carbon $startOfMonth, Carbon $startOfLastMonth, Carbon $endOfLastMonth): array
    {
        $activeLeads = Lead::query()->active()->count();
        $newLeadsThisMonth = Lead::query()->where('created_at', '>=', $startOfMonth)->count();
        $newLeadsLastMonth = Lead::query()
            ->whereBetween('created_at', [$startOfLastMonth, $endOfLastMonth])
            ->count();
        $leadsChange = $newLeadsLastMonth > 0 ? round((($newLeadsThisMonth - $newLeadsLastMonth) / $newLeadsLastMonth) * 100, 1) : 0;

        $openOpportunities = Opportunity::query()->open()->count();
        $pipelineValue = (float) Opportunity::query()->open()->sum('expected_value');
        $weightedPipeline = (float) Opportunity::query()
            ->open()
            ->selectRaw('SUM(expected_value * probability / 100) as weighted')
            ->value('weighted') ?? 0;

        $convertedThisMonth = Lead::query()
            ->converted()
            ->where('converted_at', '>=', $startOfMonth)
            ->count();

        return [
            'active_leads' => $activeLeads,
            'new_leads_this_month' => $newLeadsThisMonth,
            'leads_change' => $leadsChange,
            'open_opportunities' => $openOpportunities,
            'pipeline_value' => round($pipelineValue, 2),
            'weighted_pipeline' => round($weightedPipeline, 2),
            'converted_this_month' => $convertedThisMonth,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function getManufacturingMetrics(Carbon $startOfMonth): array
    {
        $activeOrders = ProductionOrder::query()
            ->whereIn('status', ['confirmed', 'in_progress'])
            ->count();

        $completedThisMonth = ProductionOrder::query()
            ->where('status', 'completed')
            ->where('updated_at', '>=', $startOfMonth)
            ->count();

        $totalPlannedQty = (float) ProductionOrder::query()
            ->whereIn('status', ['confirmed', 'in_progress'])
            ->sum('planned_quantity');

        $totalCompletedQty = (float) ProductionOrder::query()
            ->whereIn('status', ['confirmed', 'in_progress', 'completed'])
            ->where('updated_at', '>=', $startOfMonth)
            ->sum('completed_quantity');

        return [
            'active_orders' => $activeOrders,
            'completed_this_month' => $completedThisMonth,
            'planned_quantity' => round($totalPlannedQty, 2),
            'completed_quantity' => round($totalCompletedQty, 2),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function getAccountingMetrics(Carbon $startOfMonth): array
    {
        $postedJournals = JournalEntry::query()
            ->where('status', 'posted')
            ->where('date', '>=', $startOfMonth)
            ->count();

        $totalDebit = (float) JournalLine::query()
            ->whereHas('journalEntry', fn ($q) => $q->where('status', 'posted')->where('date', '>=', $startOfMonth))
            ->sum('debit');

        $revenueAccounts = Account::query()->ofType('revenue')->pluck('id');
        $monthlyRevenue = (float) JournalLine::query()
            ->whereIn('account_id', $revenueAccounts)
            ->whereHas('journalEntry', fn ($q) => $q->where('status', 'posted')->where('date', '>=', $startOfMonth))
            ->sum('credit');

        $expenseAccounts = Account::query()->ofType('expense')->pluck('id');
        $monthlyExpenses = (float) JournalLine::query()
            ->whereIn('account_id', $expenseAccounts)
            ->whereHas('journalEntry', fn ($q) => $q->where('status', 'posted')->where('date', '>=', $startOfMonth))
            ->sum('debit');

        $receivableAccounts = Account::query()
            ->where('sub_type', 'accounts_receivable')
            ->pluck('id');
        $totalReceivable = (float) JournalLine::query()
            ->whereIn('account_id', $receivableAccounts)
            ->whereHas('journalEntry', fn ($q) => $q->where('status', 'posted'))
            ->selectRaw('SUM(debit) - SUM(credit) as balance')
            ->value('balance') ?? 0;

        $payableAccounts = Account::query()
            ->where('sub_type', 'accounts_payable')
            ->pluck('id');
        $totalPayable = (float) JournalLine::query()
            ->whereIn('account_id', $payableAccounts)
            ->whereHas('journalEntry', fn ($q) => $q->where('status', 'posted'))
            ->selectRaw('SUM(credit) - SUM(debit) as balance')
            ->value('balance') ?? 0;

        return [
            'posted_journals' => $postedJournals,
            'total_transactions' => round($totalDebit, 2),
            'monthly_revenue' => round($monthlyRevenue, 2),
            'monthly_expenses' => round($monthlyExpenses, 2),
            'net_income' => round($monthlyRevenue - $monthlyExpenses, 2),
            'accounts_receivable' => round(max(0, $totalReceivable), 2),
            'accounts_payable' => round(max(0, $totalPayable), 2),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function getUserMetrics(): array
    {
        $totalUsers = User::query()->where('is_active', true)->count();
        $recentLogins = User::query()
            ->whereNotNull('last_login_at')
            ->where('last_login_at', '>=', Carbon::now()->subDays(7))
            ->count();

        return [
            'total_active' => $totalUsers,
            'recent_logins' => $recentLogins,
        ];
    }
}
