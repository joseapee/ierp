<?php

declare(strict_types=1);

namespace Tests\Unit\Services;

use App\Models\Account;
use App\Models\Customer;
use App\Models\FiscalYear;
use App\Models\Product;
use App\Models\StockBatch;
use App\Models\Tenant;
use App\Models\UnitOfMeasure;
use App\Models\User;
use App\Models\Warehouse;
use App\Services\JournalService;
use App\Services\SalesOrderService;
use App\Services\StockLedgerService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SalesOrderServiceTest extends TestCase
{
    use RefreshDatabase;

    private SalesOrderService $service;

    private StockLedgerService $stockLedgerService;

    private Tenant $tenant;

    private User $admin;

    private Customer $customer;

    private Warehouse $warehouse;

    private Product $product;

    protected function setUp(): void
    {
        parent::setUp();
        $this->tenant = Tenant::factory()->create();
        app()->instance('current.tenant', $this->tenant);
        $this->admin = User::factory()->create([
            'tenant_id' => $this->tenant->id,
            'is_super_admin' => true,
        ]);
        $this->actingAs($this->admin);

        $unit = UnitOfMeasure::factory()->create(['tenant_id' => $this->tenant->id]);
        $this->customer = Customer::factory()->create(['tenant_id' => $this->tenant->id]);
        $this->warehouse = Warehouse::factory()->create(['tenant_id' => $this->tenant->id]);
        $this->product = Product::factory()->create([
            'tenant_id' => $this->tenant->id,
            'base_unit_id' => $unit->id,
        ]);

        $this->stockLedgerService = new StockLedgerService;
        $this->service = new SalesOrderService(
            $this->stockLedgerService,
            new JournalService,
        );
    }

    public function test_create_sales_order(): void
    {
        $so = $this->service->create([
            'customer_id' => $this->customer->id,
            'order_date' => '2026-03-10',
            'items' => [
                [
                    'product_id' => $this->product->id,
                    'quantity' => 5,
                    'unit_price' => 200,
                    'discount_percent' => 10,
                    'tax_rate' => 7.5,
                    'warehouse_id' => $this->warehouse->id,
                ],
            ],
        ]);

        $this->assertEquals('draft', $so->status);
        // 5 * 200 = 1000, discount 10% = 100, subtotal = 900, tax 7.5% = 67.5
        $this->assertEquals(900, (float) $so->subtotal);
        $this->assertEquals(67.5, (float) $so->tax_amount);
        $this->assertEquals(100, (float) $so->discount_amount);
        $this->assertEquals(967.5, (float) $so->total_amount);
        $this->assertCount(1, $so->items);
    }

    public function test_generate_order_number(): void
    {
        $so1 = $this->service->create([
            'customer_id' => $this->customer->id,
            'order_date' => '2026-03-10',
            'items' => [
                [
                    'product_id' => $this->product->id,
                    'quantity' => 1,
                    'unit_price' => 50,
                    'warehouse_id' => $this->warehouse->id,
                ],
            ],
        ]);

        $this->assertEquals('SO-000001', $so1->order_number);

        $so2 = $this->service->create([
            'customer_id' => $this->customer->id,
            'order_date' => '2026-03-11',
            'items' => [
                [
                    'product_id' => $this->product->id,
                    'quantity' => 1,
                    'unit_price' => 50,
                    'warehouse_id' => $this->warehouse->id,
                ],
            ],
        ]);

        $this->assertEquals('SO-000002', $so2->order_number);
    }

    public function test_confirm_validates_stock_availability(): void
    {
        $so = $this->service->create([
            'customer_id' => $this->customer->id,
            'order_date' => '2026-03-10',
            'items' => [
                [
                    'product_id' => $this->product->id,
                    'quantity' => 100,
                    'unit_price' => 50,
                    'warehouse_id' => $this->warehouse->id,
                ],
            ],
        ]);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Insufficient stock');
        $this->service->confirm($so);
    }

    public function test_confirm_succeeds_with_sufficient_stock(): void
    {
        // Seed stock.
        $this->stockLedgerService->record([
            'tenant_id' => $this->tenant->id,
            'product_id' => $this->product->id,
            'warehouse_id' => $this->warehouse->id,
            'movement_type' => 'opening_balance',
            'quantity' => 50,
            'unit_cost' => 10,
        ]);

        $so = $this->service->create([
            'customer_id' => $this->customer->id,
            'order_date' => '2026-03-10',
            'items' => [
                [
                    'product_id' => $this->product->id,
                    'quantity' => 30,
                    'unit_price' => 50,
                    'warehouse_id' => $this->warehouse->id,
                ],
            ],
        ]);

        $confirmed = $this->service->confirm($so);

        $this->assertEquals('confirmed', $confirmed->status);
        $this->assertNotNull($confirmed->confirmed_at);
    }

    public function test_fulfill_items_fifo_batch_deduction(): void
    {
        FiscalYear::factory()->create(['tenant_id' => $this->tenant->id]);
        Account::factory()->asset()->create(['tenant_id' => $this->tenant->id, 'code' => '1200', 'name' => 'Accounts Receivable']);
        Account::factory()->revenue()->create(['tenant_id' => $this->tenant->id, 'code' => '4000', 'name' => 'Sales Revenue']);
        Account::factory()->liability()->create(['tenant_id' => $this->tenant->id, 'code' => '2100', 'name' => 'VAT Payable']);
        Account::factory()->expense()->create(['tenant_id' => $this->tenant->id, 'code' => '5000', 'name' => 'COGS']);
        Account::factory()->asset()->create(['tenant_id' => $this->tenant->id, 'code' => '1300', 'name' => 'Inventory']);

        // Create two batches at different costs (FIFO order: batch1 first, batch2 second).
        $batch1 = StockBatch::create([
            'tenant_id' => $this->tenant->id,
            'product_id' => $this->product->id,
            'warehouse_id' => $this->warehouse->id,
            'initial_quantity' => 5,
            'remaining_quantity' => 5,
            'unit_cost' => 10,
            'status' => 'available',
            'created_at' => now()->subDay(),
        ]);

        $batch2 = StockBatch::create([
            'tenant_id' => $this->tenant->id,
            'product_id' => $this->product->id,
            'warehouse_id' => $this->warehouse->id,
            'initial_quantity' => 10,
            'remaining_quantity' => 10,
            'unit_cost' => 20,
            'status' => 'available',
            'created_at' => now(),
        ]);

        // Seed stock ledger so confirm() sees available balance.
        $this->stockLedgerService->record([
            'tenant_id' => $this->tenant->id,
            'product_id' => $this->product->id,
            'warehouse_id' => $this->warehouse->id,
            'movement_type' => 'opening_balance',
            'quantity' => 15,
            'unit_cost' => 10,
        ]);

        $so = $this->service->create([
            'customer_id' => $this->customer->id,
            'order_date' => '2026-03-10',
            'items' => [
                [
                    'product_id' => $this->product->id,
                    'quantity' => 7,
                    'unit_price' => 100,
                    'tax_rate' => 0,
                    'warehouse_id' => $this->warehouse->id,
                ],
            ],
        ]);

        $this->service->confirm($so);

        $result = $this->service->fulfillItems($so, [
            ['sales_order_item_id' => $so->items->first()->id, 'quantity_fulfilled' => 7],
        ]);

        // FIFO: 5 units from batch1 @10 = 50, 2 units from batch2 @20 = 40, total COGS = 90.
        $this->assertEquals(0, (float) $batch1->fresh()->remaining_quantity);
        $this->assertEquals(8, (float) $batch2->fresh()->remaining_quantity);

        $this->assertEquals('fulfilled', $result->status);
    }

    public function test_fulfill_items_creates_revenue_journal(): void
    {
        FiscalYear::factory()->create(['tenant_id' => $this->tenant->id]);
        $arAccount = Account::factory()->asset()->create(['tenant_id' => $this->tenant->id, 'code' => '1200', 'name' => 'Accounts Receivable']);
        $revenueAccount = Account::factory()->revenue()->create(['tenant_id' => $this->tenant->id, 'code' => '4000', 'name' => 'Sales Revenue']);
        Account::factory()->liability()->create(['tenant_id' => $this->tenant->id, 'code' => '2100', 'name' => 'VAT Payable']);
        Account::factory()->expense()->create(['tenant_id' => $this->tenant->id, 'code' => '5000', 'name' => 'COGS']);
        Account::factory()->asset()->create(['tenant_id' => $this->tenant->id, 'code' => '1300', 'name' => 'Inventory']);

        StockBatch::create([
            'tenant_id' => $this->tenant->id,
            'product_id' => $this->product->id,
            'warehouse_id' => $this->warehouse->id,
            'initial_quantity' => 10,
            'remaining_quantity' => 10,
            'unit_cost' => 50,
            'status' => 'available',
        ]);

        $this->stockLedgerService->record([
            'tenant_id' => $this->tenant->id,
            'product_id' => $this->product->id,
            'warehouse_id' => $this->warehouse->id,
            'movement_type' => 'opening_balance',
            'quantity' => 10,
            'unit_cost' => 50,
        ]);

        $so = $this->service->create([
            'customer_id' => $this->customer->id,
            'order_date' => '2026-03-10',
            'items' => [
                [
                    'product_id' => $this->product->id,
                    'quantity' => 3,
                    'unit_price' => 200,
                    'tax_rate' => 0,
                    'warehouse_id' => $this->warehouse->id,
                ],
            ],
        ]);

        $this->service->confirm($so);
        $this->service->fulfillItems($so, [
            ['sales_order_item_id' => $so->items->first()->id, 'quantity_fulfilled' => 3],
        ]);

        // Revenue journal: Debit AR 600, Credit Revenue 600.
        $this->assertDatabaseHas('journal_lines', [
            'account_id' => $arAccount->id,
            'debit' => 600,
            'credit' => 0,
        ]);

        $this->assertDatabaseHas('journal_lines', [
            'account_id' => $revenueAccount->id,
            'debit' => 0,
            'credit' => 600,
        ]);
    }

    public function test_fulfill_items_creates_cogs_journal_at_fifo_cost(): void
    {
        FiscalYear::factory()->create(['tenant_id' => $this->tenant->id]);
        Account::factory()->asset()->create(['tenant_id' => $this->tenant->id, 'code' => '1200', 'name' => 'AR']);
        Account::factory()->revenue()->create(['tenant_id' => $this->tenant->id, 'code' => '4000', 'name' => 'Revenue']);
        Account::factory()->liability()->create(['tenant_id' => $this->tenant->id, 'code' => '2100', 'name' => 'VAT']);
        $cogsAccount = Account::factory()->expense()->create(['tenant_id' => $this->tenant->id, 'code' => '5000', 'name' => 'COGS']);
        $inventoryAccount = Account::factory()->asset()->create(['tenant_id' => $this->tenant->id, 'code' => '1300', 'name' => 'Inventory']);

        // Two batches: 5@10, 10@20.
        StockBatch::create([
            'tenant_id' => $this->tenant->id,
            'product_id' => $this->product->id,
            'warehouse_id' => $this->warehouse->id,
            'initial_quantity' => 5,
            'remaining_quantity' => 5,
            'unit_cost' => 10,
            'status' => 'available',
            'created_at' => now()->subDay(),
        ]);

        StockBatch::create([
            'tenant_id' => $this->tenant->id,
            'product_id' => $this->product->id,
            'warehouse_id' => $this->warehouse->id,
            'initial_quantity' => 10,
            'remaining_quantity' => 10,
            'unit_cost' => 20,
            'status' => 'available',
            'created_at' => now(),
        ]);

        $this->stockLedgerService->record([
            'tenant_id' => $this->tenant->id,
            'product_id' => $this->product->id,
            'warehouse_id' => $this->warehouse->id,
            'movement_type' => 'opening_balance',
            'quantity' => 15,
            'unit_cost' => 10,
        ]);

        $so = $this->service->create([
            'customer_id' => $this->customer->id,
            'order_date' => '2026-03-10',
            'items' => [
                [
                    'product_id' => $this->product->id,
                    'quantity' => 7,
                    'unit_price' => 100,
                    'tax_rate' => 0,
                    'warehouse_id' => $this->warehouse->id,
                ],
            ],
        ]);

        $this->service->confirm($so);
        $this->service->fulfillItems($so, [
            ['sales_order_item_id' => $so->items->first()->id, 'quantity_fulfilled' => 7],
        ]);

        // COGS = 5*10 + 2*20 = 90.
        $this->assertDatabaseHas('journal_lines', [
            'account_id' => $cogsAccount->id,
            'debit' => 90,
            'credit' => 0,
        ]);

        $this->assertDatabaseHas('journal_lines', [
            'account_id' => $inventoryAccount->id,
            'debit' => 0,
            'credit' => 90,
        ]);
    }

    public function test_partial_fulfillment(): void
    {
        FiscalYear::factory()->create(['tenant_id' => $this->tenant->id]);
        Account::factory()->asset()->create(['tenant_id' => $this->tenant->id, 'code' => '1200', 'name' => 'AR']);
        Account::factory()->revenue()->create(['tenant_id' => $this->tenant->id, 'code' => '4000', 'name' => 'Revenue']);
        Account::factory()->liability()->create(['tenant_id' => $this->tenant->id, 'code' => '2100', 'name' => 'VAT']);
        Account::factory()->expense()->create(['tenant_id' => $this->tenant->id, 'code' => '5000', 'name' => 'COGS']);
        Account::factory()->asset()->create(['tenant_id' => $this->tenant->id, 'code' => '1300', 'name' => 'Inventory']);

        StockBatch::create([
            'tenant_id' => $this->tenant->id,
            'product_id' => $this->product->id,
            'warehouse_id' => $this->warehouse->id,
            'initial_quantity' => 20,
            'remaining_quantity' => 20,
            'unit_cost' => 10,
            'status' => 'available',
        ]);

        $this->stockLedgerService->record([
            'tenant_id' => $this->tenant->id,
            'product_id' => $this->product->id,
            'warehouse_id' => $this->warehouse->id,
            'movement_type' => 'opening_balance',
            'quantity' => 20,
            'unit_cost' => 10,
        ]);

        $so = $this->service->create([
            'customer_id' => $this->customer->id,
            'order_date' => '2026-03-10',
            'items' => [
                [
                    'product_id' => $this->product->id,
                    'quantity' => 10,
                    'unit_price' => 100,
                    'tax_rate' => 0,
                    'warehouse_id' => $this->warehouse->id,
                ],
            ],
        ]);

        $this->service->confirm($so);

        $result = $this->service->fulfillItems($so, [
            ['sales_order_item_id' => $so->items->first()->id, 'quantity_fulfilled' => 4],
        ]);

        $this->assertEquals('partially_fulfilled', $result->status);

        $result = $this->service->fulfillItems($result, [
            ['sales_order_item_id' => $so->items->first()->id, 'quantity_fulfilled' => 6],
        ]);

        $this->assertEquals('fulfilled', $result->status);
    }

    public function test_fulfill_creates_stock_ledger_sales_issue(): void
    {
        FiscalYear::factory()->create(['tenant_id' => $this->tenant->id]);
        Account::factory()->asset()->create(['tenant_id' => $this->tenant->id, 'code' => '1200', 'name' => 'AR']);
        Account::factory()->revenue()->create(['tenant_id' => $this->tenant->id, 'code' => '4000', 'name' => 'Revenue']);
        Account::factory()->liability()->create(['tenant_id' => $this->tenant->id, 'code' => '2100', 'name' => 'VAT']);
        Account::factory()->expense()->create(['tenant_id' => $this->tenant->id, 'code' => '5000', 'name' => 'COGS']);
        Account::factory()->asset()->create(['tenant_id' => $this->tenant->id, 'code' => '1300', 'name' => 'Inventory']);

        StockBatch::create([
            'tenant_id' => $this->tenant->id,
            'product_id' => $this->product->id,
            'warehouse_id' => $this->warehouse->id,
            'initial_quantity' => 10,
            'remaining_quantity' => 10,
            'unit_cost' => 25,
            'status' => 'available',
        ]);

        $this->stockLedgerService->record([
            'tenant_id' => $this->tenant->id,
            'product_id' => $this->product->id,
            'warehouse_id' => $this->warehouse->id,
            'movement_type' => 'opening_balance',
            'quantity' => 10,
            'unit_cost' => 25,
        ]);

        $so = $this->service->create([
            'customer_id' => $this->customer->id,
            'order_date' => '2026-03-10',
            'items' => [
                [
                    'product_id' => $this->product->id,
                    'quantity' => 3,
                    'unit_price' => 100,
                    'tax_rate' => 0,
                    'warehouse_id' => $this->warehouse->id,
                ],
            ],
        ]);

        $this->service->confirm($so);
        $this->service->fulfillItems($so, [
            ['sales_order_item_id' => $so->items->first()->id, 'quantity_fulfilled' => 3],
        ]);

        $this->assertDatabaseHas('stock_ledger', [
            'product_id' => $this->product->id,
            'warehouse_id' => $this->warehouse->id,
            'movement_type' => 'sales_issue',
            'quantity' => -3,
        ]);
    }

    public function test_cancel_draft_order(): void
    {
        $so = $this->service->create([
            'customer_id' => $this->customer->id,
            'order_date' => '2026-03-10',
            'items' => [
                [
                    'product_id' => $this->product->id,
                    'quantity' => 5,
                    'unit_price' => 100,
                    'warehouse_id' => $this->warehouse->id,
                ],
            ],
        ]);

        $cancelled = $this->service->cancel($so, 'Customer changed mind');

        $this->assertEquals('cancelled', $cancelled->status);
        $this->assertEquals('Customer changed mind', $cancelled->cancellation_reason);
    }

    public function test_cancel_fulfilled_throws_exception(): void
    {
        FiscalYear::factory()->create(['tenant_id' => $this->tenant->id]);
        Account::factory()->asset()->create(['tenant_id' => $this->tenant->id, 'code' => '1200', 'name' => 'AR']);
        Account::factory()->revenue()->create(['tenant_id' => $this->tenant->id, 'code' => '4000', 'name' => 'Revenue']);
        Account::factory()->liability()->create(['tenant_id' => $this->tenant->id, 'code' => '2100', 'name' => 'VAT']);
        Account::factory()->expense()->create(['tenant_id' => $this->tenant->id, 'code' => '5000', 'name' => 'COGS']);
        Account::factory()->asset()->create(['tenant_id' => $this->tenant->id, 'code' => '1300', 'name' => 'Inventory']);

        StockBatch::create([
            'tenant_id' => $this->tenant->id,
            'product_id' => $this->product->id,
            'warehouse_id' => $this->warehouse->id,
            'initial_quantity' => 10,
            'remaining_quantity' => 10,
            'unit_cost' => 10,
            'status' => 'available',
        ]);

        $this->stockLedgerService->record([
            'tenant_id' => $this->tenant->id,
            'product_id' => $this->product->id,
            'warehouse_id' => $this->warehouse->id,
            'movement_type' => 'opening_balance',
            'quantity' => 10,
            'unit_cost' => 10,
        ]);

        $so = $this->service->create([
            'customer_id' => $this->customer->id,
            'order_date' => '2026-03-10',
            'items' => [
                [
                    'product_id' => $this->product->id,
                    'quantity' => 5,
                    'unit_price' => 100,
                    'tax_rate' => 0,
                    'warehouse_id' => $this->warehouse->id,
                ],
            ],
        ]);

        $this->service->confirm($so);
        $so = $this->service->fulfillItems($so, [
            ['sales_order_item_id' => $so->items->first()->id, 'quantity_fulfilled' => 5],
        ]);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Only draft or confirmed sales orders can be cancelled.');
        $this->service->cancel($so, 'Too late');
    }

    public function test_fulfill_draft_throws_exception(): void
    {
        $so = $this->service->create([
            'customer_id' => $this->customer->id,
            'order_date' => '2026-03-10',
            'items' => [
                [
                    'product_id' => $this->product->id,
                    'quantity' => 5,
                    'unit_price' => 100,
                    'warehouse_id' => $this->warehouse->id,
                ],
            ],
        ]);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Only confirmed or partially fulfilled sales orders');

        $this->service->fulfillItems($so, [
            ['sales_order_item_id' => $so->items->first()->id, 'quantity_fulfilled' => 3],
        ]);
    }
}
