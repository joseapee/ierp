<?php

declare(strict_types=1);

namespace Tests\Unit\Services;

use App\Models\Account;
use App\Models\FiscalYear;
use App\Models\Product;
use App\Models\Supplier;
use App\Models\Tenant;
use App\Models\UnitOfMeasure;
use App\Models\User;
use App\Models\Warehouse;
use App\Services\JournalService;
use App\Services\PurchaseOrderService;
use App\Services\StockLedgerService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PurchaseOrderServiceTest extends TestCase
{
    use RefreshDatabase;

    private PurchaseOrderService $service;

    private Tenant $tenant;

    private User $admin;

    private Supplier $supplier;

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
        $this->supplier = Supplier::factory()->create(['tenant_id' => $this->tenant->id]);
        $this->warehouse = Warehouse::factory()->create(['tenant_id' => $this->tenant->id]);
        $this->product = Product::factory()->create([
            'tenant_id' => $this->tenant->id,
            'base_unit_id' => $unit->id,
        ]);

        $this->service = new PurchaseOrderService(
            new StockLedgerService,
            new JournalService,
        );
    }

    public function test_create_purchase_order(): void
    {
        $po = $this->service->create([
            'supplier_id' => $this->supplier->id,
            'order_date' => '2026-03-10',
            'items' => [
                [
                    'product_id' => $this->product->id,
                    'quantity' => 10,
                    'unit_price' => 100,
                    'tax_rate' => 7.5,
                    'warehouse_id' => $this->warehouse->id,
                ],
            ],
        ]);

        $this->assertEquals('draft', $po->status);
        $this->assertEquals(1000, (float) $po->subtotal);
        $this->assertEquals(75, (float) $po->tax_amount);
        $this->assertEquals(1075, (float) $po->total_amount);
        $this->assertCount(1, $po->items);
    }

    public function test_generate_order_number(): void
    {
        $po1 = $this->service->create([
            'supplier_id' => $this->supplier->id,
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

        $this->assertEquals('PO-000001', $po1->order_number);

        $po2 = $this->service->create([
            'supplier_id' => $this->supplier->id,
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

        $this->assertEquals('PO-000002', $po2->order_number);
    }

    public function test_confirm_draft_purchase_order(): void
    {
        $po = $this->service->create([
            'supplier_id' => $this->supplier->id,
            'order_date' => '2026-03-10',
            'items' => [
                [
                    'product_id' => $this->product->id,
                    'quantity' => 5,
                    'unit_price' => 200,
                    'warehouse_id' => $this->warehouse->id,
                ],
            ],
        ]);

        $confirmed = $this->service->confirm($po);

        $this->assertEquals('confirmed', $confirmed->status);
        $this->assertNotNull($confirmed->confirmed_at);
        $this->assertEquals($this->admin->id, $confirmed->confirmed_by);
    }

    public function test_confirm_non_draft_throws_exception(): void
    {
        $po = $this->service->create([
            'supplier_id' => $this->supplier->id,
            'order_date' => '2026-03-10',
            'items' => [
                [
                    'product_id' => $this->product->id,
                    'quantity' => 5,
                    'unit_price' => 200,
                    'warehouse_id' => $this->warehouse->id,
                ],
            ],
        ]);

        $this->service->confirm($po);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Only draft purchase orders can be confirmed.');
        $this->service->confirm($po);
    }

    public function test_receive_items_creates_stock_batch_and_ledger(): void
    {
        // Set up accounting prerequisites.
        FiscalYear::factory()->create(['tenant_id' => $this->tenant->id]);
        Account::factory()->asset()->create(['tenant_id' => $this->tenant->id, 'code' => '1300', 'name' => 'Inventory']);
        Account::factory()->liability()->create(['tenant_id' => $this->tenant->id, 'code' => '2000', 'name' => 'Accounts Payable']);

        $po = $this->service->create([
            'supplier_id' => $this->supplier->id,
            'order_date' => '2026-03-10',
            'items' => [
                [
                    'product_id' => $this->product->id,
                    'quantity' => 20,
                    'unit_price' => 50,
                    'tax_rate' => 0,
                    'warehouse_id' => $this->warehouse->id,
                ],
            ],
        ]);

        $this->service->confirm($po);

        $poItem = $po->items->first();

        $this->service->receiveItems($po, [
            ['purchase_order_item_id' => $poItem->id, 'quantity_received' => 20],
        ]);

        // Stock batch created.
        $this->assertDatabaseHas('stock_batches', [
            'product_id' => $this->product->id,
            'warehouse_id' => $this->warehouse->id,
            'initial_quantity' => 20,
            'remaining_quantity' => 20,
            'unit_cost' => 50,
            'status' => 'available',
        ]);

        // Stock ledger entry created.
        $this->assertDatabaseHas('stock_ledger', [
            'product_id' => $this->product->id,
            'warehouse_id' => $this->warehouse->id,
            'movement_type' => 'purchase_receipt',
            'quantity' => 20,
        ]);
    }

    public function test_receive_items_creates_journal_entry(): void
    {
        FiscalYear::factory()->create(['tenant_id' => $this->tenant->id]);
        $inventoryAccount = Account::factory()->asset()->create(['tenant_id' => $this->tenant->id, 'code' => '1300', 'name' => 'Inventory']);
        $apAccount = Account::factory()->liability()->create(['tenant_id' => $this->tenant->id, 'code' => '2000', 'name' => 'Accounts Payable']);

        $po = $this->service->create([
            'supplier_id' => $this->supplier->id,
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

        $this->service->confirm($po);

        $this->service->receiveItems($po, [
            ['purchase_order_item_id' => $po->items->first()->id, 'quantity_received' => 10],
        ]);

        // Journal entry: Debit Inventory 1000, Credit AP 1000.
        $this->assertDatabaseHas('journal_lines', [
            'account_id' => $inventoryAccount->id,
            'debit' => 1000,
            'credit' => 0,
        ]);

        $this->assertDatabaseHas('journal_lines', [
            'account_id' => $apAccount->id,
            'debit' => 0,
            'credit' => 1000,
        ]);
    }

    public function test_partial_receipt_sets_partially_received(): void
    {
        FiscalYear::factory()->create(['tenant_id' => $this->tenant->id]);
        Account::factory()->asset()->create(['tenant_id' => $this->tenant->id, 'code' => '1300', 'name' => 'Inventory']);
        Account::factory()->liability()->create(['tenant_id' => $this->tenant->id, 'code' => '2000', 'name' => 'Accounts Payable']);

        $po = $this->service->create([
            'supplier_id' => $this->supplier->id,
            'order_date' => '2026-03-10',
            'items' => [
                [
                    'product_id' => $this->product->id,
                    'quantity' => 20,
                    'unit_price' => 50,
                    'tax_rate' => 0,
                    'warehouse_id' => $this->warehouse->id,
                ],
            ],
        ]);

        $this->service->confirm($po);

        $result = $this->service->receiveItems($po, [
            ['purchase_order_item_id' => $po->items->first()->id, 'quantity_received' => 8],
        ]);

        $this->assertEquals('partially_received', $result->status);

        // Receive remainder.
        $result = $this->service->receiveItems($result, [
            ['purchase_order_item_id' => $po->items->first()->id, 'quantity_received' => 12],
        ]);

        $this->assertEquals('received', $result->status);
    }

    public function test_receive_items_rejects_excess_quantity(): void
    {
        FiscalYear::factory()->create(['tenant_id' => $this->tenant->id]);
        Account::factory()->asset()->create(['tenant_id' => $this->tenant->id, 'code' => '1300', 'name' => 'Inventory']);
        Account::factory()->liability()->create(['tenant_id' => $this->tenant->id, 'code' => '2000', 'name' => 'Accounts Payable']);

        $po = $this->service->create([
            'supplier_id' => $this->supplier->id,
            'order_date' => '2026-03-10',
            'items' => [
                [
                    'product_id' => $this->product->id,
                    'quantity' => 10,
                    'unit_price' => 50,
                    'tax_rate' => 0,
                    'warehouse_id' => $this->warehouse->id,
                ],
            ],
        ]);

        $this->service->confirm($po);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('exceeds remaining quantity');

        $this->service->receiveItems($po, [
            ['purchase_order_item_id' => $po->items->first()->id, 'quantity_received' => 15],
        ]);
    }

    public function test_receive_items_rejects_draft_order(): void
    {
        $po = $this->service->create([
            'supplier_id' => $this->supplier->id,
            'order_date' => '2026-03-10',
            'items' => [
                [
                    'product_id' => $this->product->id,
                    'quantity' => 10,
                    'unit_price' => 50,
                    'warehouse_id' => $this->warehouse->id,
                ],
            ],
        ]);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Only confirmed or partially received purchase orders');

        $this->service->receiveItems($po, [
            ['purchase_order_item_id' => $po->items->first()->id, 'quantity_received' => 5],
        ]);
    }

    public function test_cancel_draft_order(): void
    {
        $po = $this->service->create([
            'supplier_id' => $this->supplier->id,
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

        $cancelled = $this->service->cancel($po, 'Supplier issue');

        $this->assertEquals('cancelled', $cancelled->status);
        $this->assertEquals('Supplier issue', $cancelled->cancellation_reason);
    }

    public function test_cancel_partially_received_throws_exception(): void
    {
        FiscalYear::factory()->create(['tenant_id' => $this->tenant->id]);
        Account::factory()->asset()->create(['tenant_id' => $this->tenant->id, 'code' => '1300', 'name' => 'Inventory']);
        Account::factory()->liability()->create(['tenant_id' => $this->tenant->id, 'code' => '2000', 'name' => 'Accounts Payable']);

        $po = $this->service->create([
            'supplier_id' => $this->supplier->id,
            'order_date' => '2026-03-10',
            'items' => [
                [
                    'product_id' => $this->product->id,
                    'quantity' => 20,
                    'unit_price' => 50,
                    'tax_rate' => 0,
                    'warehouse_id' => $this->warehouse->id,
                ],
            ],
        ]);

        $this->service->confirm($po);
        $po = $this->service->receiveItems($po, [
            ['purchase_order_item_id' => $po->items->first()->id, 'quantity_received' => 5],
        ]);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Only draft or confirmed purchase orders can be cancelled.');
        $this->service->cancel($po, 'Too late');
    }
}
