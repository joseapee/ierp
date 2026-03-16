
<?php

use App\Http\Controllers\Billing\PaystackCallbackController;
use App\Http\Controllers\Billing\PaystackWebhookController;
use App\Http\Controllers\ProfileController;
use App\Livewire\Accounting\AccountForm;
use App\Livewire\Accounting\AccountIndex;
use App\Livewire\Accounting\BalanceSheet;
use App\Livewire\Accounting\FiscalYearIndex;
use App\Livewire\Accounting\JournalEntryForm;
use App\Livewire\Accounting\JournalEntryIndex;
use App\Livewire\Accounting\JournalEntryShow;
use App\Livewire\Accounting\ProfitAndLoss;
use App\Livewire\Accounting\TrialBalance;
use App\Livewire\Admin\PlanManager;
use App\Livewire\Admin\SubscriptionManager;
use App\Livewire\Billing\BillingDashboard;
use App\Livewire\Catalog\BrandList;
use App\Livewire\Catalog\CategoryList;
use App\Livewire\Catalog\ProductDetail;
use App\Livewire\Catalog\ProductList;
use App\Livewire\Catalog\ProductWizard;
use App\Livewire\Catalog\UnitOfMeasureList;
use App\Livewire\Crm\ActivityIndex;
use App\Livewire\Crm\ContactForm;
use App\Livewire\Crm\ContactIndex;
use App\Livewire\Crm\LeadDetail;
use App\Livewire\Crm\LeadForm;
use App\Livewire\Crm\LeadIndex;
use App\Livewire\Crm\OpportunityDetail;
use App\Livewire\Crm\OpportunityForm;
use App\Livewire\Crm\OpportunityIndex;
use App\Livewire\Crm\PipelineBoard;
use App\Livewire\Crm\PipelineStageManager;
use App\Livewire\Manufacturing\BomList;
use App\Livewire\Manufacturing\ProductionBoard;
use App\Livewire\Manufacturing\ProductionOrderDetail;
use App\Livewire\Manufacturing\ProductionOrderForm;
use App\Livewire\Manufacturing\ProductionOrderList;
use App\Livewire\Manufacturing\ProductionStageManager;
use App\Livewire\Procurement\PurchaseOrderDetail;
use App\Livewire\Procurement\PurchaseOrderForm;
use App\Livewire\Procurement\PurchaseOrderIndex;
use App\Livewire\Procurement\SupplierForm;
use App\Livewire\Procurement\SupplierIndex;
use App\Livewire\RoleManagement\PermissionMatrix;
use App\Livewire\RoleManagement\RoleList;
use App\Livewire\Sales\CustomerDetail;
use App\Livewire\Sales\CustomerForm;
use App\Livewire\Sales\CustomerIndex;
use App\Livewire\Sales\SalesOrderDetail;
use App\Livewire\Sales\SalesOrderForm;
use App\Livewire\Sales\SalesOrderIndex;
use App\Livewire\Setup\OnboardingWizard;
use App\Livewire\Setup\SetupWizard;
use App\Livewire\Stock\StockAdjustmentDetail;
use App\Livewire\Stock\StockAdjustmentForm;
use App\Livewire\Stock\StockAdjustmentList;
use App\Livewire\Stock\StockLedgerList;
use App\Livewire\Stock\StockTransferDetail;
use App\Livewire\Stock\StockTransferForm;
use App\Livewire\Stock\StockTransferList;
use App\Livewire\TenantManagement\TenantList;
use App\Livewire\UserManagement\UserList;
use App\Livewire\Warehouse\WarehouseDetail;
use App\Livewire\Warehouse\WarehouseList;
use Illuminate\Support\Facades\Route;

Route::get('/', fn () => redirect()->route('login'));

Route::middleware(['auth', 'verified'])->group(function (): void {

    // ── Setup & Onboarding (no setup.complete/subscription middleware) ──
    Route::get('/setup', SetupWizard::class)->name('setup');
    Route::get('/onboarding', OnboardingWizard::class)->name('onboarding');

    // ── Billing (accessible even without completed setup or active subscription) ──
    Route::get('/billing', BillingDashboard::class)->name('billing.index');
    Route::get('/billing/callback', [PaystackCallbackController::class, 'handle'])->name('billing.callback');

    // Profile (Breeze default — accessible without completed setup)
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // ── All routes below require completed setup (tenant created) ──
    Route::middleware('setup.complete')->group(function (): void {

        Route::get('/dashboard', fn () => view('dashboard'))->name('dashboard');

        // User Management
        Route::middleware('permission:users.view')
            ->prefix('users')
            ->name('users.')
            ->group(function (): void {
                Route::get('/', UserList::class)->name('index');
            });

        // Role Management
        Route::middleware('permission:roles.view')
            ->prefix('roles')
            ->name('roles.')
            ->group(function (): void {
                Route::get('/', RoleList::class)->name('index');
                Route::get('/permissions', PermissionMatrix::class)
                    ->middleware('permission:roles.manage-permissions')
                    ->name('permissions');
            });

        // Tenant Management (Super Admin only)
        Route::middleware('super.admin')
            ->prefix('admin/tenants')
            ->name('tenants.')
            ->group(function (): void {
                Route::get('/', TenantList::class)->name('index');
            });

        // Plan Management (Super Admin only)
        Route::middleware('super.admin')
            ->prefix('admin/plans')
            ->name('admin.plans.')
            ->group(function (): void {
                Route::get('/', PlanManager::class)->name('index');
            });

        // Subscription Management (Super Admin only)
        Route::middleware('super.admin')
            ->prefix('admin/subscriptions')
            ->name('admin.subscriptions.')
            ->group(function (): void {
                Route::get('/', SubscriptionManager::class)->name('index');
            });

        // ── Catalog ──
        Route::middleware('subscription')
            ->prefix('categories')
            ->name('categories.')
            ->group(function (): void {
                Route::get('/', CategoryList::class)
                    ->middleware('permission:categories.view')
                    ->name('index');
            });

        Route::middleware('subscription')
            ->prefix('brands')
            ->name('brands.')
            ->group(function (): void {
                Route::get('/', BrandList::class)
                    ->middleware('permission:brands.view')
                    ->name('index');
            });

        Route::middleware('subscription')
            ->prefix('units')
            ->name('units.')
            ->group(function (): void {
                Route::get('/', UnitOfMeasureList::class)
                    ->middleware('permission:units.view')
                    ->name('index');
            });

        Route::middleware(['subscription', 'permission:products.view'])
            ->prefix('products')
            ->name('products.')
            ->group(function (): void {
                Route::get('/', ProductList::class)->name('index');
                Route::get('/create', ProductWizard::class)
                    ->middleware('permission:products.create')
                    ->name('create');
                Route::get('/{product}/edit', ProductWizard::class)
                    ->middleware('permission:products.edit')
                    ->name('edit');
                Route::get('/{product}', ProductDetail::class)->name('show');
            });

        // ── Warehouse ──
        Route::middleware(['subscription', 'permission:warehouses.view'])
            ->prefix('warehouses')
            ->name('warehouses.')
            ->group(function (): void {
                Route::get('/', WarehouseList::class)->name('index');
                Route::get('/{warehouse}', WarehouseDetail::class)->name('show');
            });

        // ── Stock ──
        Route::middleware('subscription')
            ->prefix('stock')->name('stock.')->group(function (): void {
                Route::get('/ledger', StockLedgerList::class)
                    ->middleware('permission:stock.view')
                    ->name('ledger');
                Route::get('/adjustments', StockAdjustmentList::class)
                    ->middleware('permission:stock.adjust')
                    ->name('adjustments.index');
                Route::get('/adjustments/create', StockAdjustmentForm::class)
                    ->middleware('permission:stock.adjust')
                    ->name('adjustments.create');
                Route::get('/adjustments/{adjustment}', StockAdjustmentDetail::class)
                    ->middleware('permission:stock.view')
                    ->name('adjustments.show');
                Route::get('/transfers', StockTransferList::class)
                    ->middleware('permission:stock.transfer')
                    ->name('transfers.index');
                Route::get('/transfers/create', StockTransferForm::class)
                    ->middleware('permission:stock.transfer')
                    ->name('transfers.create');
                Route::get('/transfers/{transfer}', StockTransferDetail::class)
                    ->middleware('permission:stock.view')
                    ->name('transfers.show');
            });

        // ── Manufacturing ──
        Route::middleware(['subscription', 'feature:manufacturing_enabled'])
            ->prefix('manufacturing')->name('manufacturing.')->group(function (): void {
                Route::get('/boms', BomList::class)
                    ->middleware('permission:bom.view')
                    ->name('boms.index');
                Route::get('/orders', ProductionOrderList::class)
                    ->middleware('permission:production.view')
                    ->name('orders.index');
                Route::get('/orders/create', ProductionOrderForm::class)
                    ->middleware('permission:production.create')
                    ->name('orders.create');
                Route::get('/orders/{order}', ProductionOrderDetail::class)
                    ->middleware('permission:production.view')
                    ->name('orders.show');
                Route::get('/board', ProductionBoard::class)
                    ->middleware('permission:production.view')
                    ->name('board');
                Route::get('/stages', ProductionStageManager::class)
                    ->middleware('permission:production.manage')
                    ->name('stages.index');
            });

        // ── Accounting ──
        Route::middleware('subscription')
            ->prefix('accounting')->name('accounting.')->group(function (): void {
                Route::get('/accounts', AccountIndex::class)
                    ->middleware('permission:accounts.view')
                    ->name('accounts.index');
                Route::get('/accounts/create', AccountForm::class)
                    ->middleware('permission:accounts.create')
                    ->name('accounts.create');
                Route::get('/accounts/{account}/edit', AccountForm::class)
                    ->middleware('permission:accounts.edit')
                    ->name('accounts.edit');
                Route::get('/journal-entries', JournalEntryIndex::class)
                    ->middleware('permission:journal.view')
                    ->name('journal-entries.index');
                Route::get('/journal-entries/create', JournalEntryForm::class)
                    ->middleware('permission:journal.create')
                    ->name('journal-entries.create');
                Route::get('/journal-entries/{entry}', JournalEntryShow::class)
                    ->middleware('permission:journal.view')
                    ->name('journal-entries.show');
                Route::get('/reports/trial-balance', TrialBalance::class)
                    ->middleware('permission:reports.view')
                    ->name('reports.trial-balance');
                Route::get('/reports/balance-sheet', BalanceSheet::class)
                    ->middleware('permission:reports.view')
                    ->name('reports.balance-sheet');
                Route::get('/reports/profit-and-loss', ProfitAndLoss::class)
                    ->middleware('permission:reports.view')
                    ->name('reports.profit-and-loss');
                Route::get('/fiscal-years', FiscalYearIndex::class)
                    ->middleware('permission:fiscal-years.view')
                    ->name('fiscal-years.index');
            });

        // ── Sales ──
        Route::middleware('subscription')
            ->prefix('sales')->name('sales.')->group(function (): void {
                Route::get('/customers', CustomerIndex::class)
                    ->middleware('permission:customers.view')
                    ->name('customers.index');
                Route::get('/customers/create', CustomerForm::class)
                    ->middleware('permission:customers.create')
                    ->name('customers.create');
                Route::get('/customers/{customer}/edit', CustomerForm::class)
                    ->middleware('permission:customers.edit')
                    ->name('customers.edit');
                Route::get('/customers/{customer}', CustomerDetail::class)
                    ->middleware('permission:customers.view')
                    ->name('customers.show');
                Route::get('/orders', SalesOrderIndex::class)
                    ->middleware('permission:sales-orders.view')
                    ->name('orders.index');
                Route::get('/orders/create', SalesOrderForm::class)
                    ->middleware('permission:sales-orders.create')
                    ->name('orders.create');
                Route::get('/orders/{order}', SalesOrderDetail::class)
                    ->middleware('permission:sales-orders.view')
                    ->name('orders.show');
            });
        
            // ── POS ──
            Route::middleware(['subscription', 'permission:pos.access'])
                ->prefix('pos')
                ->name('pos.')
                ->group(function (): void {
                    Route::get('/', \App\Livewire\POS\PosTerminal::class)->name('terminal');
                });

        // ── CRM ──
        Route::middleware(['subscription', 'feature:crm_enabled'])
            ->prefix('crm')->name('crm.')->group(function (): void {
                Route::get('/leads', LeadIndex::class)
                    ->middleware('permission:leads.view')
                    ->name('leads.index');
                Route::get('/leads/create', LeadForm::class)
                    ->middleware('permission:leads.create')
                    ->name('leads.create');
                Route::get('/leads/{lead}/edit', LeadForm::class)
                    ->middleware('permission:leads.edit')
                    ->name('leads.edit');
                Route::get('/leads/{lead}', LeadDetail::class)
                    ->middleware('permission:leads.view')
                    ->name('leads.show');
                Route::get('/contacts', ContactIndex::class)
                    ->middleware('permission:crm-contacts.view')
                    ->name('contacts.index');
                Route::get('/contacts/create', ContactForm::class)
                    ->middleware('permission:crm-contacts.create')
                    ->name('contacts.create');
                Route::get('/contacts/{contact}/edit', ContactForm::class)
                    ->middleware('permission:crm-contacts.edit')
                    ->name('contacts.edit');
                Route::get('/opportunities', OpportunityIndex::class)
                    ->middleware('permission:opportunities.view')
                    ->name('opportunities.index');
                Route::get('/opportunities/create', OpportunityForm::class)
                    ->middleware('permission:opportunities.create')
                    ->name('opportunities.create');
                Route::get('/opportunities/{opportunity}/edit', OpportunityForm::class)
                    ->middleware('permission:opportunities.edit')
                    ->name('opportunities.edit');
                Route::get('/opportunities/{opportunity}', OpportunityDetail::class)
                    ->middleware('permission:opportunities.view')
                    ->name('opportunities.show');
                Route::get('/pipeline', PipelineBoard::class)
                    ->middleware('permission:opportunities.view')
                    ->name('pipeline.index');
                Route::get('/pipeline/stages', PipelineStageManager::class)
                    ->middleware('permission:pipeline-stages.manage')
                    ->name('pipeline.stages');
                Route::get('/activities', ActivityIndex::class)
                    ->middleware('permission:crm-activities.view')
                    ->name('activities.index');
            });

        // ── Procurement ──
        Route::middleware('subscription')
            ->prefix('procurement')->name('procurement.')->group(function (): void {
                Route::get('/suppliers', SupplierIndex::class)
                    ->middleware('permission:suppliers.view')
                    ->name('suppliers.index');
                Route::get('/suppliers/create', SupplierForm::class)
                    ->middleware('permission:suppliers.create')
                    ->name('suppliers.create');
                Route::get('/suppliers/{supplier}/edit', SupplierForm::class)
                    ->middleware('permission:suppliers.edit')
                    ->name('suppliers.edit');
                Route::get('/purchase-orders', PurchaseOrderIndex::class)
                    ->middleware('permission:purchase-orders.view')
                    ->name('purchase-orders.index');
                Route::get('/purchase-orders/create', PurchaseOrderForm::class)
                    ->middleware('permission:purchase-orders.create')
                    ->name('purchase-orders.create');
                Route::get('/purchase-orders/{purchaseOrder}', PurchaseOrderDetail::class)
                    ->middleware('permission:purchase-orders.view')
                    ->name('purchase-orders.show');
            });
    });
});

// ── Paystack Webhook (no auth — external webhook) ──
Route::post('/webhook/paystack', [PaystackWebhookController::class, 'handle'])->name('webhook.paystack');

require __DIR__.'/auth.php';
