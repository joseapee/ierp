<?php

declare(strict_types=1);

namespace Tests\Unit\Services;

use App\Models\Bom;
use App\Models\Product;
use App\Models\StockBatch;
use App\Models\Tenant;
use App\Models\UnitOfMeasure;
use App\Models\Warehouse;
use App\Services\BomService;
use App\Services\PricingService;
use App\Services\ProductionService;
use App\Services\StockLedgerService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use RuntimeException;
use Tests\TestCase;

class ProductionServiceTest extends TestCase
{
    use RefreshDatabase;

    private ProductionService $service;

    private BomService $bomService;

    private Tenant $tenant;

    private Product $product;

    private Product $rawMaterial;

    private Warehouse $warehouse;

    private Bom $bom;

    protected function setUp(): void
    {
        parent::setUp();
        $ledger = new StockLedgerService;
        $this->bomService = new BomService;
        $pricing = new PricingService($this->bomService);
        $this->service = new ProductionService($ledger, $this->bomService, $pricing);

        $this->tenant = Tenant::factory()->create();
        app()->instance('current.tenant', $this->tenant);
        $unit = UnitOfMeasure::factory()->create(['tenant_id' => $this->tenant->id]);
        $this->warehouse = Warehouse::factory()->create(['tenant_id' => $this->tenant->id]);

        $this->product = Product::factory()->create([
            'tenant_id' => $this->tenant->id,
            'base_unit_id' => $unit->id,
            'type' => 'manufactured',
        ]);

        $this->rawMaterial = Product::factory()->create([
            'tenant_id' => $this->tenant->id,
            'base_unit_id' => $unit->id,
            'type' => 'standard',
            'is_purchasable' => true,
        ]);

        $this->bom = $this->bomService->create([
            'product_id' => $this->product->id,
            'name' => 'Test BOM',
            'version' => '1.0',
            'yield_quantity' => 1,
            'items' => [
                ['product_id' => $this->rawMaterial->id, 'quantity' => 2, 'unit_cost' => 10.00],
            ],
        ]);
        $this->bomService->activate($this->bom);
    }

    public function test_create_order(): void
    {
        $order = $this->service->createOrder([
            'order_number' => 'MO-000001',
            'product_id' => $this->product->id,
            'bom_id' => $this->bom->id,
            'warehouse_id' => $this->warehouse->id,
            'planned_quantity' => 10,
        ]);

        $this->assertDatabaseHas('production_orders', [
            'order_number' => 'MO-000001',
            'status' => 'draft',
            'planned_quantity' => 10,
        ]);
        $this->assertNotNull($order->wipInventory);
    }

    public function test_confirm_order(): void
    {
        $order = $this->service->createOrder([
            'order_number' => 'MO-000002',
            'product_id' => $this->product->id,
            'bom_id' => $this->bom->id,
            'warehouse_id' => $this->warehouse->id,
            'planned_quantity' => 5,
        ]);

        $confirmed = $this->service->confirmOrder($order);

        $this->assertEquals('confirmed', $confirmed->status);
    }

    public function test_start_production(): void
    {
        $order = $this->service->createOrder([
            'order_number' => 'PO-000003',
            'product_id' => $this->product->id,
            'bom_id' => $this->bom->id,
            'warehouse_id' => $this->warehouse->id,
            'planned_quantity' => 5,
        ]);

        $this->service->confirmOrder($order);
        $started = $this->service->startProduction($order->fresh());

        $this->assertEquals('in_progress', $started->status);
        $this->assertNotNull($started->actual_start_date);
    }

    public function test_cannot_start_draft_order(): void
    {
        $order = $this->service->createOrder([
            'order_number' => 'PO-000004',
            'product_id' => $this->product->id,
            'bom_id' => $this->bom->id,
            'warehouse_id' => $this->warehouse->id,
            'planned_quantity' => 5,
        ]);

        $this->expectException(RuntimeException::class);
        $this->service->startProduction($order);
    }

    public function test_consume_material(): void
    {
        $order = $this->service->createOrder([
            'order_number' => 'PO-000005',
            'product_id' => $this->product->id,
            'bom_id' => $this->bom->id,
            'warehouse_id' => $this->warehouse->id,
            'planned_quantity' => 5,
        ]);

        $this->service->confirmOrder($order);
        $this->service->startProduction($order->fresh());

        // Create stock batch for raw material
        $batch = StockBatch::create([
            'tenant_id' => $this->tenant->id,
            'product_id' => $this->rawMaterial->id,
            'warehouse_id' => $this->warehouse->id,
            'initial_quantity' => 100,
            'remaining_quantity' => 100,
            'unit_cost' => 10.00,
            'status' => 'available',
        ]);

        $consumption = $this->service->consumeMaterial($order->fresh(), [
            'product_id' => $this->rawMaterial->id,
            'warehouse_id' => $this->warehouse->id,
            'stock_batch_id' => $batch->id,
            'planned_quantity' => 10,
            'actual_quantity' => 10,
            'unit_cost' => 10.00,
        ]);

        $this->assertDatabaseHas('material_consumptions', [
            'production_order_id' => $order->id,
            'product_id' => $this->rawMaterial->id,
            'actual_quantity' => 10,
        ]);

        // Batch remaining should decrease
        $this->assertEquals(90, (float) $batch->fresh()->remaining_quantity);

        // Ledger entry should exist
        $this->assertDatabaseHas('stock_ledger', [
            'product_id' => $this->rawMaterial->id,
            'movement_type' => 'production_issue',
        ]);
    }

    public function test_complete_production_creates_finished_goods(): void
    {
        $order = $this->service->createOrder([
            'order_number' => 'PO-000006',
            'product_id' => $this->product->id,
            'bom_id' => $this->bom->id,
            'warehouse_id' => $this->warehouse->id,
            'planned_quantity' => 5,
        ]);

        $this->service->confirmOrder($order);
        $this->service->startProduction($order->fresh());

        // Consume some material
        StockBatch::create([
            'tenant_id' => $this->tenant->id,
            'product_id' => $this->rawMaterial->id,
            'warehouse_id' => $this->warehouse->id,
            'initial_quantity' => 100,
            'remaining_quantity' => 100,
            'unit_cost' => 10.00,
            'status' => 'available',
        ]);

        $this->service->consumeMaterial($order->fresh(), [
            'product_id' => $this->rawMaterial->id,
            'warehouse_id' => $this->warehouse->id,
            'planned_quantity' => 10,
            'actual_quantity' => 10,
            'unit_cost' => 10.00,
        ]);

        $completed = $this->service->completeProduction($order->fresh(), 5, 0);

        $this->assertEquals('completed', $completed->status);
        $this->assertEquals(5, (float) $completed->completed_quantity);

        // Should have production_receipt in ledger
        $this->assertDatabaseHas('stock_ledger', [
            'product_id' => $this->product->id,
            'movement_type' => 'production_receipt',
            'quantity' => 5,
        ]);

        // Should have created finished goods batch
        $this->assertDatabaseHas('stock_batches', [
            'product_id' => $this->product->id,
            'warehouse_id' => $this->warehouse->id,
            'initial_quantity' => 5,
            'status' => 'available',
        ]);

        // WIP should be completed
        $this->assertEquals('completed', $completed->wipInventory->status);
    }

    public function test_cancel_draft_order(): void
    {
        $order = $this->service->createOrder([
            'order_number' => 'PO-000007',
            'product_id' => $this->product->id,
            'bom_id' => $this->bom->id,
            'warehouse_id' => $this->warehouse->id,
            'planned_quantity' => 3,
        ]);

        $cancelled = $this->service->cancelOrder($order);

        $this->assertEquals('cancelled', $cancelled->status);
    }

    public function test_cannot_cancel_in_progress_order(): void
    {
        $order = $this->service->createOrder([
            'order_number' => 'PO-000008',
            'product_id' => $this->product->id,
            'bom_id' => $this->bom->id,
            'warehouse_id' => $this->warehouse->id,
            'planned_quantity' => 5,
        ]);

        $this->service->confirmOrder($order);
        $this->service->startProduction($order->fresh());

        $this->expectException(RuntimeException::class);
        $this->service->cancelOrder($order->fresh());
    }

    public function test_generate_order_number(): void
    {
        $number = $this->service->generateOrderNumber();
        $this->assertEquals('MO-000001', $number);

        $this->service->createOrder([
            'order_number' => $number,
            'product_id' => $this->product->id,
            'bom_id' => $this->bom->id,
            'warehouse_id' => $this->warehouse->id,
            'planned_quantity' => 1,
        ]);

        $number2 = $this->service->generateOrderNumber();
        $this->assertEquals('MO-000002', $number2);
    }

    public function test_create_and_manage_tasks(): void
    {
        $order = $this->service->createOrder([
            'order_number' => 'PO-000009',
            'product_id' => $this->product->id,
            'bom_id' => $this->bom->id,
            'warehouse_id' => $this->warehouse->id,
            'planned_quantity' => 5,
        ]);

        $task = $this->service->createTask($order, [
            'name' => 'Cut materials',
        ]);

        $this->assertDatabaseHas('production_tasks', [
            'production_order_id' => $order->id,
            'name' => 'Cut materials',
            'status' => 'pending',
        ]);

        $started = $this->service->startTask($task);
        $this->assertEquals('in_progress', $started->status);

        $completed = $this->service->completeTask($started);
        $this->assertEquals('completed', $completed->status);
        $this->assertNotNull($completed->completed_at);
    }
}
