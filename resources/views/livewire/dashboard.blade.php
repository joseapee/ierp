<div>
    @section('title', 'Dashboard')

    <x-page-header title="Dashboard" :breadcrumbs="[
        ['label' => 'Dashboard'],
    ]" />

    {{-- ═══════════ WELCOME CARD (always visible) ═══════════ --}}
    <div class="row mb-2">
        <div class="col-12">
            <div class="card custom-card overflow-hidden">
                <div class="card-body py-3">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <h5 class="fw-semibold mb-1">Welcome back, {{ auth()->user()->name }}!</h5>
                            <span class="text-muted fs-13">Here's what's happening with your business today.</span>
                        </div>
                        <div class="d-none d-md-block">
                            <span class="badge bg-primary-transparent fs-12">
                                <i class="ri-calendar-line me-1"></i> {{ now()->format('l, M d, Y') }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- ═══════════ KPI METRIC CARDS ═══════════ --}}
    <div class="row">
        {{-- Revenue (sales-orders.view) --}}
        @can('sales-orders.view')
        <div class="col-xxl-3 col-xl-6 col-lg-6 col-md-6">
            <div class="card custom-card overflow-hidden">
                <div class="card-body">
                    <div class="d-flex align-items-start justify-content-between mb-2">
                        <div>
                            <span class="d-block mb-1 text-muted fs-12">Monthly Revenue</span>
                            <h4 class="fw-semibold mb-0">{{ format_currency($metrics['sales']['revenue'] ?? 0) }}</h4>
                        </div>
                        <div class="avatar avatar-md bg-primary-transparent rounded">
                            <i class="ri-money-dollar-circle-line fs-20 text-primary"></i>
                        </div>
                    </div>
                    @php $rc = $metrics['sales']['revenue_change'] ?? 0; @endphp
                    <span class="badge bg-{{ $rc >= 0 ? 'success' : 'danger' }}-transparent fs-11">
                        <i class="ri-arrow-{{ $rc >= 0 ? 'up' : 'down' }}-s-line"></i> {{ abs($rc) }}%
                    </span>
                    <span class="text-muted fs-12 ms-1">vs last month</span>
                </div>
            </div>
        </div>
        @endcan

        {{-- Expenses (purchase-orders.view) --}}
        @can('purchase-orders.view')
        <div class="col-xxl-3 col-xl-6 col-lg-6 col-md-6">
            <div class="card custom-card overflow-hidden">
                <div class="card-body">
                    <div class="d-flex align-items-start justify-content-between mb-2">
                        <div>
                            <span class="d-block mb-1 text-muted fs-12">Monthly Expenses</span>
                            <h4 class="fw-semibold mb-0">{{ format_currency($metrics['procurement']['expenses'] ?? 0) }}</h4>
                        </div>
                        <div class="avatar avatar-md bg-danger-transparent rounded">
                            <i class="ri-shopping-bag-line fs-20 text-danger"></i>
                        </div>
                    </div>
                    @php $ec = $metrics['procurement']['expense_change'] ?? 0; @endphp
                    <span class="badge bg-{{ $ec <= 0 ? 'success' : 'danger' }}-transparent fs-11">
                        <i class="ri-arrow-{{ $ec <= 0 ? 'down' : 'up' }}-s-line"></i> {{ abs($ec) }}%
                    </span>
                    <span class="text-muted fs-12 ms-1">vs last month</span>
                </div>
            </div>
        </div>
        @endcan

        {{-- Customers (customers.view) --}}
        @can('customers.view')
        <div class="col-xxl-3 col-xl-6 col-lg-6 col-md-6">
            <div class="card custom-card overflow-hidden">
                <div class="card-body">
                    <div class="d-flex align-items-start justify-content-between mb-2">
                        <div>
                            <span class="d-block mb-1 text-muted fs-12">Active Customers</span>
                            <h4 class="fw-semibold mb-0">{{ number_format($metrics['sales']['total_customers'] ?? 0) }}</h4>
                        </div>
                        <div class="avatar avatar-md bg-success-transparent rounded">
                            <i class="ri-group-line fs-20 text-success"></i>
                        </div>
                    </div>
                    @can('sales-orders.view')
                    <span class="text-muted fs-12">
                        <i class="ri-shopping-cart-line"></i> {{ $metrics['sales']['pending_orders'] ?? 0 }} pending orders
                    </span>
                    @endcan
                </div>
            </div>
        </div>
        @endcan

        {{-- Inventory (stock.view) --}}
        @can('stock.view')
        <div class="col-xxl-3 col-xl-6 col-lg-6 col-md-6">
            <div class="card custom-card overflow-hidden">
                <div class="card-body">
                    <div class="d-flex align-items-start justify-content-between mb-2">
                        <div>
                            <span class="d-block mb-1 text-muted fs-12">Stock Value</span>
                            <h4 class="fw-semibold mb-0">{{ format_currency($metrics['inventory']['total_stock_value'] ?? 0) }}</h4>
                        </div>
                        <div class="avatar avatar-md bg-warning-transparent rounded">
                            <i class="ri-box-3-line fs-20 text-warning"></i>
                        </div>
                    </div>
                    <span class="text-muted fs-12">
                        <i class="ri-alarm-warning-line text-danger"></i>
                        <span class="text-danger fw-semibold">{{ $metrics['inventory']['low_stock_count'] ?? 0 }}</span> low stock alerts
                    </span>
                </div>
            </div>
        </div>
        @endcan
    </div>

    {{-- ═══════════ SECONDARY METRICS ROW ═══════════ --}}
    @canany(['products.view', 'sales-orders.view', 'purchase-orders.view', 'users.view'])
    <div class="row">
        {{-- Active Products (products.view) --}}
        @can('products.view')
        <div class="col-xxl-2 col-xl-4 col-lg-4 col-md-6">
            <div class="card custom-card">
                <div class="card-body text-center">
                    <i class="ri-apps-line fs-24 text-primary mb-1 d-block"></i>
                    <h5 class="fw-semibold mb-0">{{ number_format($metrics['inventory']['total_products'] ?? 0) }}</h5>
                    <span class="text-muted fs-12">Active Products</span>
                </div>
            </div>
        </div>
        @endcan

        {{-- Average Order Value (sales-orders.view) --}}
        @can('sales-orders.view')
        <div class="col-xxl-2 col-xl-4 col-lg-4 col-md-6">
            <div class="card custom-card">
                <div class="card-body text-center">
                    <i class="ri-line-chart-line fs-24 text-info mb-1 d-block"></i>
                    <h5 class="fw-semibold mb-0">{{ format_currency($metrics['sales']['average_order_value'] ?? 0) }}</h5>
                    <span class="text-muted fs-12">Avg. Order Value</span>
                </div>
            </div>
        </div>
        @endcan

        {{-- Suppliers (purchase-orders.view) --}}
        @can('purchase-orders.view')
        <div class="col-xxl-2 col-xl-4 col-lg-4 col-md-6">
            <div class="card custom-card">
                <div class="card-body text-center">
                    <i class="ri-truck-line fs-24 text-secondary mb-1 d-block"></i>
                    <h5 class="fw-semibold mb-0">{{ number_format($metrics['procurement']['total_suppliers'] ?? 0) }}</h5>
                    <span class="text-muted fs-12">Active Suppliers</span>
                </div>
            </div>
        </div>
        @endcan

        {{-- Users (users.view) --}}
        @can('users.view')
        <div class="col-xxl-2 col-xl-4 col-lg-4 col-md-6">
            <div class="card custom-card">
                <div class="card-body text-center">
                    <i class="ri-user-line fs-24 text-success mb-1 d-block"></i>
                    <h5 class="fw-semibold mb-0">{{ number_format($metrics['users']['total_active'] ?? 0) }}</h5>
                    <span class="text-muted fs-12">Active Users</span>
                </div>
            </div>
        </div>
        @endcan

        {{-- Sales Orders This Month (sales-orders.view) --}}
        @can('sales-orders.view')
        <div class="col-xxl-2 col-xl-4 col-lg-4 col-md-6">
            <div class="card custom-card">
                <div class="card-body text-center">
                    <i class="ri-file-list-3-line fs-24 text-teal mb-1 d-block"></i>
                    <h5 class="fw-semibold mb-0">{{ number_format($metrics['sales']['order_count'] ?? 0) }}</h5>
                    <span class="text-muted fs-12">Orders This Month</span>
                </div>
            </div>
        </div>
        @endcan

        {{-- Recent Logins (users.view) --}}
        @can('users.view')
        <div class="col-xxl-2 col-xl-4 col-lg-4 col-md-6">
            <div class="card custom-card">
                <div class="card-body text-center">
                    <i class="ri-login-circle-line fs-24 text-orange mb-1 d-block"></i>
                    <h5 class="fw-semibold mb-0">{{ number_format($metrics['users']['recent_logins'] ?? 0) }}</h5>
                    <span class="text-muted fs-12">Logins (7 days)</span>
                </div>
            </div>
        </div>
        @endcan
    </div>
    @endcanany

    {{-- ═══════════ CHARTS ROW (sales-orders.view) ═══════════ --}}
    @can('sales-orders.view')
    <div class="row">
        {{-- Revenue vs Expenses Trend --}}
        <div class="col-xxl-8 col-xl-7">
            <div class="card custom-card">
                <div class="card-header justify-content-between">
                    <div class="card-title">Revenue vs Expenses</div>
                    <span class="badge bg-light text-muted fs-11">Last 12 Months</span>
                </div>
                <div class="card-body">
                    <div id="revenueTrendChart" style="min-height: 350px;"></div>
                </div>
            </div>
        </div>

        {{-- Sales Order Status Distribution --}}
        <div class="col-xxl-4 col-xl-5">
            <div class="card custom-card">
                <div class="card-header justify-content-between">
                    <div class="card-title">Order Status</div>
                </div>
                <div class="card-body">
                    <div id="salesStatusChart" style="min-height: 350px;"></div>
                </div>
            </div>
        </div>
    </div>
    @endcan

    {{-- ═══════════ TOP PRODUCTS & TOP CUSTOMERS ═══════════ --}}
    @canany(['sales-orders.view', 'customers.view'])
    <div class="row">
        {{-- Top Products (sales-orders.view) --}}
        @can('sales-orders.view')
        <div class="col-xxl-6 col-xl-6">
            <div class="card custom-card">
                <div class="card-header justify-content-between">
                    <div class="card-title">Top Products (This Month)</div>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table text-nowrap mb-0">
                            <thead>
                                <tr>
                                    <th>Product</th>
                                    <th class="text-end">Qty Sold</th>
                                    <th class="text-end">Revenue</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($topProducts as $product)
                                    <tr>
                                        <td>
                                            <span class="fw-semibold">{{ $product['name'] }}</span>
                                        </td>
                                        <td class="text-end">{{ format_money($product['quantity']) }}</td>
                                        <td class="text-end">{{ format_currency($product['revenue']) }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="3" class="text-center text-muted py-3">No sales data this month</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        @endcan

        {{-- Top Customers (customers.view) --}}
        @can('customers.view')
        <div class="col-xxl-6 col-xl-6">
            <div class="card custom-card">
                <div class="card-header justify-content-between">
                    <div class="card-title">Top Customers (This Month)</div>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table text-nowrap mb-0">
                            <thead>
                                <tr>
                                    <th>Customer</th>
                                    <th class="text-end">Orders</th>
                                    <th class="text-end">Total Value</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($topCustomers as $customer)
                                    <tr>
                                        <td>
                                            <span class="fw-semibold">{{ $customer['name'] }}</span>
                                        </td>
                                        <td class="text-end">{{ $customer['orders'] }}</td>
                                        <td class="text-end">{{ format_currency($customer['total']) }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="3" class="text-center text-muted py-3">No customer data this month</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        @endcan
    </div>
    @endcanany

    {{-- ═══════════ RECENT ORDERS ═══════════ --}}
    @canany(['sales-orders.view', 'purchase-orders.view'])
    <div class="row">
        {{-- Recent Sales Orders (sales-orders.view) --}}
        @can('sales-orders.view')
        <div class="col-xxl-6 col-xl-6">
            <div class="card custom-card">
                <div class="card-header justify-content-between">
                    <div class="card-title">Recent Sales Orders</div>
                    <a href="{{ route('sales.orders.index') }}" class="btn btn-sm btn-primary-light btn-wave" wire:navigate>
                        View All <i class="ri-arrow-right-line ms-1"></i>
                    </a>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table text-nowrap mb-0">
                            <thead>
                                <tr>
                                    <th>Order #</th>
                                    <th>Customer</th>
                                    <th>Amount</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($recentSalesOrders as $order)
                                    <tr>
                                        <td><span class="fw-semibold">{{ $order->order_number }}</span></td>
                                        <td>{{ $order->customer?->name ?? '—' }}</td>
                                        <td>{{ format_currency($order->total_amount) }}</td>
                                        <td>
                                            @php
                                                $statusColors = [
                                                    'draft' => 'warning',
                                                    'confirmed' => 'info',
                                                    'fulfilled' => 'success',
                                                    'cancelled' => 'danger',
                                                ];
                                            @endphp
                                            <span class="badge bg-{{ $statusColors[$order->status] ?? 'secondary' }}-transparent">
                                                {{ ucfirst($order->status) }}
                                            </span>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="text-center text-muted py-3">No sales orders yet</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        @endcan

        {{-- Recent Purchase Orders (purchase-orders.view) --}}
        @can('purchase-orders.view')
        <div class="col-xxl-6 col-xl-6">
            <div class="card custom-card">
                <div class="card-header justify-content-between">
                    <div class="card-title">Recent Purchase Orders</div>
                    <a href="{{ route('procurement.purchase-orders.index') }}" class="btn btn-sm btn-primary-light btn-wave" wire:navigate>
                        View All <i class="ri-arrow-right-line ms-1"></i>
                    </a>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table text-nowrap mb-0">
                            <thead>
                                <tr>
                                    <th>Order #</th>
                                    <th>Supplier</th>
                                    <th>Amount</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($recentPurchaseOrders as $po)
                                    <tr>
                                        <td><span class="fw-semibold">{{ $po->order_number }}</span></td>
                                        <td>{{ $po->supplier?->name ?? '—' }}</td>
                                        <td>{{ format_currency($po->total_amount) }}</td>
                                        <td>
                                            @php
                                                $poStatusColors = [
                                                    'draft' => 'warning',
                                                    'confirmed' => 'info',
                                                    'received' => 'success',
                                                    'cancelled' => 'danger',
                                                ];
                                            @endphp
                                            <span class="badge bg-{{ $poStatusColors[$po->status] ?? 'secondary' }}-transparent">
                                                {{ ucfirst($po->status) }}
                                            </span>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="text-center text-muted py-3">No purchase orders yet</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        @endcan
    </div>
    @endcanany

    {{-- ═══════════ CRM SECTION (leads.view) ═══════════ --}}
    @can('leads.view')
    <div class="row">
        {{-- CRM Summary Cards --}}
        <div class="col-xxl-4 col-xl-6">
            <div class="card custom-card">
                <div class="card-header">
                    <div class="card-title">CRM Overview</div>
                </div>
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between mb-3 pb-3 border-bottom">
                        <div class="d-flex align-items-center gap-2">
                            <div class="avatar avatar-sm bg-primary-transparent rounded">
                                <i class="ri-user-add-line text-primary"></i>
                            </div>
                            <div>
                                <span class="d-block fw-semibold">Active Leads</span>
                                <span class="text-muted fs-12">{{ $metrics['crm']['new_leads_this_month'] ?? 0 }} new this month</span>
                            </div>
                        </div>
                        <h5 class="mb-0 fw-semibold">{{ number_format($metrics['crm']['active_leads'] ?? 0) }}</h5>
                    </div>
                    <div class="d-flex align-items-center justify-content-between mb-3 pb-3 border-bottom">
                        <div class="d-flex align-items-center gap-2">
                            <div class="avatar avatar-sm bg-success-transparent rounded">
                                <i class="ri-briefcase-line text-success"></i>
                            </div>
                            <div>
                                <span class="d-block fw-semibold">Open Opportunities</span>
                                <span class="text-muted fs-12">{{ $metrics['crm']['converted_this_month'] ?? 0 }} converted this month</span>
                            </div>
                        </div>
                        <h5 class="mb-0 fw-semibold">{{ number_format($metrics['crm']['open_opportunities'] ?? 0) }}</h5>
                    </div>
                    <div class="d-flex align-items-center justify-content-between mb-3 pb-3 border-bottom">
                        <div class="d-flex align-items-center gap-2">
                            <div class="avatar avatar-sm bg-info-transparent rounded">
                                <i class="ri-funds-line text-info"></i>
                            </div>
                            <div>
                                <span class="d-block fw-semibold">Pipeline Value</span>
                                <span class="text-muted fs-12">Expected total</span>
                            </div>
                        </div>
                        <h5 class="mb-0 fw-semibold">{{ format_currency($metrics['crm']['pipeline_value'] ?? 0) }}</h5>
                    </div>
                    <div class="d-flex align-items-center justify-content-between">
                        <div class="d-flex align-items-center gap-2">
                            <div class="avatar avatar-sm bg-warning-transparent rounded">
                                <i class="ri-scales-line text-warning"></i>
                            </div>
                            <div>
                                <span class="d-block fw-semibold">Weighted Pipeline</span>
                                <span class="text-muted fs-12">Probability-adjusted</span>
                            </div>
                        </div>
                        <h5 class="mb-0 fw-semibold">{{ format_currency($metrics['crm']['weighted_pipeline'] ?? 0) }}</h5>
                    </div>
                </div>
            </div>
        </div>

        {{-- Pipeline Funnel --}}
        <div class="col-xxl-8 col-xl-6">
            <div class="card custom-card">
                <div class="card-header">
                    <div class="card-title">Opportunity Pipeline</div>
                </div>
                <div class="card-body">
                    <div id="pipelineFunnelChart" style="min-height: 320px;"></div>
                </div>
            </div>
        </div>
    </div>
    @endcan

    {{-- ═══════════ MANUFACTURING SECTION (production.view) ═══════════ --}}
    @can('production.view')
    <div class="row">
        <div class="col-xxl-3 col-xl-6 col-md-6">
            <div class="card custom-card">
                <div class="card-body">
                    <div class="d-flex align-items-center gap-3">
                        <div class="avatar avatar-lg bg-primary-transparent rounded">
                            <i class="ri-settings-3-line fs-22 text-primary"></i>
                        </div>
                        <div>
                            <span class="text-muted fs-12 d-block">Active Production</span>
                            <h4 class="fw-semibold mb-0">{{ number_format($metrics['manufacturing']['active_orders'] ?? 0) }}</h4>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xxl-3 col-xl-6 col-md-6">
            <div class="card custom-card">
                <div class="card-body">
                    <div class="d-flex align-items-center gap-3">
                        <div class="avatar avatar-lg bg-success-transparent rounded">
                            <i class="ri-check-double-line fs-22 text-success"></i>
                        </div>
                        <div>
                            <span class="text-muted fs-12 d-block">Completed (Month)</span>
                            <h4 class="fw-semibold mb-0">{{ number_format($metrics['manufacturing']['completed_this_month'] ?? 0) }}</h4>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xxl-3 col-xl-6 col-md-6">
            <div class="card custom-card">
                <div class="card-body">
                    <div class="d-flex align-items-center gap-3">
                        <div class="avatar avatar-lg bg-info-transparent rounded">
                            <i class="ri-list-ordered fs-22 text-info"></i>
                        </div>
                        <div>
                            <span class="text-muted fs-12 d-block">Planned Qty</span>
                            <h4 class="fw-semibold mb-0">{{ format_money($metrics['manufacturing']['planned_quantity'] ?? 0) }}</h4>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xxl-3 col-xl-6 col-md-6">
            <div class="card custom-card">
                <div class="card-body">
                    <div class="d-flex align-items-center gap-3">
                        <div class="avatar avatar-lg bg-teal-transparent rounded">
                            <i class="ri-checkbox-circle-line fs-22 text-teal"></i>
                        </div>
                        <div>
                            <span class="text-muted fs-12 d-block">Completed Qty</span>
                            <h4 class="fw-semibold mb-0">{{ format_money($metrics['manufacturing']['completed_quantity'] ?? 0) }}</h4>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endcan

    {{-- ═══════════ ACCOUNTING & INVENTORY SECTION ═══════════ --}}
    @canany(['accounts.view', 'stock.view'])
    <div class="row">
        {{-- Accounting Summary (accounts.view) --}}
        @can('accounts.view')
        <div class="col-xxl-6 col-xl-6">
            <div class="card custom-card">
                <div class="card-header">
                    <div class="card-title">Accounting Summary (This Month)</div>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-6">
                            <div class="p-3 bg-primary-transparent rounded">
                                <span class="text-muted fs-12 d-block mb-1">Revenue (Journal)</span>
                                <h5 class="fw-semibold mb-0">{{ format_currency($metrics['accounting']['monthly_revenue'] ?? 0) }}</h5>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="p-3 bg-danger-transparent rounded">
                                <span class="text-muted fs-12 d-block mb-1">Expenses (Journal)</span>
                                <h5 class="fw-semibold mb-0">{{ format_currency($metrics['accounting']['monthly_expenses'] ?? 0) }}</h5>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="p-3 bg-success-transparent rounded">
                                <span class="text-muted fs-12 d-block mb-1">Net Income</span>
                                <h5 class="fw-semibold mb-0 {{ ($metrics['accounting']['net_income'] ?? 0) >= 0 ? 'text-success' : 'text-danger' }}">
                                    {{ format_currency($metrics['accounting']['net_income'] ?? 0) }}
                                </h5>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="p-3 bg-info-transparent rounded">
                                <span class="text-muted fs-12 d-block mb-1">Posted Journals</span>
                                <h5 class="fw-semibold mb-0">{{ number_format($metrics['accounting']['posted_journals'] ?? 0) }}</h5>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="p-3 bg-warning-transparent rounded">
                                <span class="text-muted fs-12 d-block mb-1">Accounts Receivable</span>
                                <h5 class="fw-semibold mb-0">{{ format_currency($metrics['accounting']['accounts_receivable'] ?? 0) }}</h5>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="p-3 bg-secondary-transparent rounded">
                                <span class="text-muted fs-12 d-block mb-1">Accounts Payable</span>
                                <h5 class="fw-semibold mb-0">{{ format_currency($metrics['accounting']['accounts_payable'] ?? 0) }}</h5>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @endcan

        {{-- Low Stock Alerts (stock.view) --}}
        @can('stock.view')
        <div class="col-xxl-6 col-xl-6">
            <div class="card custom-card">
                <div class="card-header justify-content-between">
                    <div class="card-title">
                        <i class="ri-alarm-warning-line text-danger me-1"></i> Low Stock Alerts
                    </div>
                    <span class="badge bg-danger-transparent">{{ $lowStockProducts->count() }} items</span>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table text-nowrap mb-0">
                            <thead>
                                <tr>
                                    <th>Product</th>
                                    <th class="text-end">Current Stock</th>
                                    <th class="text-end">Reorder Level</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($lowStockProducts as $product)
                                    <tr>
                                        <td><span class="fw-semibold">{{ $product->name }}</span></td>
                                        <td class="text-end">
                                            <span class="text-danger fw-semibold">{{ format_money($product->current_stock) }}</span>
                                        </td>
                                        <td class="text-end">{{ format_money($product->reorder_level) }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="3" class="text-center text-muted py-3">
                                            <i class="ri-check-line text-success"></i> All stock levels are healthy
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        @endcan
    </div>
    @endcanany

    @push('styles')
        <link rel="stylesheet" href="{{ asset('vyzor/libs/apexcharts/apexcharts.css') }}">
    @endpush

    @push('scripts')
        <script src="{{ asset('vyzor/libs/apexcharts/apexcharts.min.js') }}"></script>
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                'use strict';

                const monthlyTrend = @json($monthlyTrend);
                const salesStatus = @json($salesStatusDistribution);
                const pipelineFunnel = @json($pipelineFunnel);

                // ── Revenue vs Expenses Area Chart (sales-orders.view) ──
                if (document.querySelector('#revenueTrendChart') && monthlyTrend.length > 0) {
                    new ApexCharts(document.querySelector('#revenueTrendChart'), {
                        series: [
                            { name: 'Revenue', data: monthlyTrend.map(i => i.revenue) },
                            { name: 'Expenses', data: monthlyTrend.map(i => i.expenses) },
                        ],
                        chart: {
                            type: 'area',
                            height: 350,
                            toolbar: { show: false },
                            fontFamily: 'Montserrat, sans-serif',
                        },
                        colors: ['var(--primary-color)', 'rgb(255,73,205)'],
                        fill: {
                            type: 'gradient',
                            gradient: { shadeIntensity: 1, opacityFrom: 0.4, opacityTo: 0.1, stops: [0, 90, 100] },
                        },
                        dataLabels: { enabled: false },
                        stroke: { curve: 'smooth', width: 2 },
                        grid: { borderColor: '#f1f1f1', strokeDashArray: 3 },
                        xaxis: { categories: monthlyTrend.map(i => i.label) },
                        yaxis: {
                            labels: {
                                formatter: function (val) {
                                    if (val >= 1000000) return (val / 1000000).toFixed(1) + 'M';
                                    if (val >= 1000) return (val / 1000).toFixed(0) + 'K';
                                    return val.toFixed(0);
                                }
                            }
                        },
                        tooltip: {
                            y: { formatter: function (val) { return new Intl.NumberFormat().format(val); } }
                        },
                        legend: { position: 'top' },
                    }).render();
                }

                // ── Sales Status Donut Chart (sales-orders.view) ──
                if (document.querySelector('#salesStatusChart') && Object.keys(salesStatus).length > 0) {
                    const statusLabels = Object.keys(salesStatus).map(s => s.charAt(0).toUpperCase() + s.slice(1));
                    const statusValues = Object.values(salesStatus);
                    const statusColors = {
                        draft: '#f7b731',
                        confirmed: '#3498db',
                        fulfilled: '#2ed573',
                        cancelled: '#e74c3c',
                    };
                    const colors = Object.keys(salesStatus).map(s => statusColors[s] || '#6c757d');

                    new ApexCharts(document.querySelector('#salesStatusChart'), {
                        series: statusValues,
                        labels: statusLabels,
                        chart: { type: 'donut', height: 350, fontFamily: 'Montserrat, sans-serif' },
                        colors: colors,
                        dataLabels: { enabled: false },
                        legend: { position: 'bottom' },
                        stroke: { show: true, colors: ['#fff'], width: 2 },
                        plotOptions: {
                            pie: {
                                expandOnClick: false,
                                donut: {
                                    size: '75%',
                                    labels: {
                                        show: true,
                                        name: { show: true, fontSize: '16px', fontWeight: 600 },
                                        value: { show: true, fontSize: '20px', fontWeight: 700 },
                                        total: {
                                            show: true,
                                            label: 'Total Orders',
                                            fontSize: '13px',
                                            fontWeight: 400,
                                            color: '#6c757d',
                                        },
                                    },
                                },
                            },
                        },
                    }).render();
                }

                // ── Pipeline Funnel Bar Chart (leads.view) ──
                if (document.querySelector('#pipelineFunnelChart') && pipelineFunnel.length > 0) {
                    new ApexCharts(document.querySelector('#pipelineFunnelChart'), {
                        series: [
                            { name: 'Deals', data: pipelineFunnel.map(i => i.count) },
                            { name: 'Value', data: pipelineFunnel.map(i => i.value) },
                        ],
                        chart: {
                            type: 'bar',
                            height: 320,
                            toolbar: { show: false },
                            fontFamily: 'Montserrat, sans-serif',
                        },
                        plotOptions: { bar: { horizontal: false, columnWidth: '55%', borderRadius: 4 } },
                        colors: ['var(--primary-color)', 'rgb(253, 175, 34)'],
                        dataLabels: { enabled: false },
                        grid: { borderColor: '#f1f1f1', strokeDashArray: 3 },
                        xaxis: { categories: pipelineFunnel.map(i => i.stage) },
                        yaxis: [
                            { title: { text: 'Deals' } },
                            {
                                opposite: true,
                                title: { text: 'Value' },
                                labels: {
                                    formatter: function (val) {
                                        if (val >= 1000000) return (val / 1000000).toFixed(1) + 'M';
                                        if (val >= 1000) return (val / 1000).toFixed(0) + 'K';
                                        return val.toFixed(0);
                                    }
                                }
                            }
                        ],
                        legend: { position: 'top' },
                        tooltip: {
                            y: { formatter: function (val) { return new Intl.NumberFormat().format(val); } }
                        },
                    }).render();
                } else if (document.querySelector('#pipelineFunnelChart')) {
                    document.querySelector('#pipelineFunnelChart').innerHTML =
                        '<div class="text-center text-muted py-5"><i class="ri-bar-chart-box-line fs-30 d-block mb-2"></i>No pipeline data available</div>';
                }
            });
        </script>
    @endpush
</div>
