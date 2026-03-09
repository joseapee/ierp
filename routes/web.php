<?php

use App\Http\Controllers\ProfileController;
use App\Livewire\Catalog\BrandList;
use App\Livewire\Catalog\CategoryList;
use App\Livewire\Catalog\ProductDetail;
use App\Livewire\Catalog\ProductList;
use App\Livewire\Catalog\ProductWizard;
use App\Livewire\Catalog\UnitOfMeasureList;
use App\Livewire\Manufacturing\BomList;
use App\Livewire\Manufacturing\ProductionBoard;
use App\Livewire\Manufacturing\ProductionOrderDetail;
use App\Livewire\Manufacturing\ProductionOrderForm;
use App\Livewire\Manufacturing\ProductionOrderList;
use App\Livewire\Manufacturing\ProductionStageManager;
use App\Livewire\RoleManagement\PermissionMatrix;
use App\Livewire\RoleManagement\RoleList;
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

    Route::get('/dashboard', fn () => view('dashboard'))->name('dashboard');

    // Profile (Breeze default)
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

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

    // ── Catalog ──
    Route::middleware('permission:categories.view')
        ->prefix('categories')
        ->name('categories.')
        ->group(function (): void {
            Route::get('/', CategoryList::class)->name('index');
        });

    Route::middleware('permission:brands.view')
        ->prefix('brands')
        ->name('brands.')
        ->group(function (): void {
            Route::get('/', BrandList::class)->name('index');
        });

    Route::middleware('permission:units.view')
        ->prefix('units')
        ->name('units.')
        ->group(function (): void {
            Route::get('/', UnitOfMeasureList::class)->name('index');
        });

    Route::middleware('permission:products.view')
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
    Route::middleware('permission:warehouses.view')
        ->prefix('warehouses')
        ->name('warehouses.')
        ->group(function (): void {
            Route::get('/', WarehouseList::class)->name('index');
            Route::get('/{warehouse}', WarehouseDetail::class)->name('show');
        });

    // ── Stock ──
    Route::prefix('stock')->name('stock.')->group(function (): void {
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
    Route::prefix('manufacturing')->name('manufacturing.')->group(function (): void {
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
});

require __DIR__.'/auth.php';
