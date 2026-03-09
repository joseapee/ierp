<?php

declare(strict_types=1);

namespace Tests\Unit\Services;

use App\Models\Product;
use App\Models\Tenant;
use App\Models\UnitOfMeasure;
use App\Models\Warehouse;
use App\Services\StockLedgerService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StockLedgerServiceTest extends TestCase
{
    use RefreshDatabase;

    private StockLedgerService $service;

    private Tenant $tenant;

    private Warehouse $warehouse;

    private Product $product;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new StockLedgerService;
        $this->tenant = Tenant::factory()->create();
        app()->instance('current.tenant', $this->tenant);
        $unit = UnitOfMeasure::factory()->create(['tenant_id' => $this->tenant->id]);
        $this->warehouse = Warehouse::factory()->create(['tenant_id' => $this->tenant->id]);
        $this->product = Product::factory()->create(['tenant_id' => $this->tenant->id, 'base_unit_id' => $unit->id]);
    }

    public function test_record_creates_ledger_entry(): void
    {
        $entry = $this->service->record([
            'tenant_id' => $this->tenant->id,
            'product_id' => $this->product->id,
            'warehouse_id' => $this->warehouse->id,
            'movement_type' => 'opening_balance',
            'quantity' => 100,
            'unit_cost' => 10.50,
        ]);

        $this->assertDatabaseHas('stock_ledger', [
            'product_id' => $this->product->id,
            'warehouse_id' => $this->warehouse->id,
            'movement_type' => 'opening_balance',
            'quantity' => 100,
        ]);
    }

    public function test_record_calculates_running_balance(): void
    {
        $this->service->record([
            'tenant_id' => $this->tenant->id,
            'product_id' => $this->product->id,
            'warehouse_id' => $this->warehouse->id,
            'movement_type' => 'opening_balance',
            'quantity' => 100,
            'unit_cost' => 10,
        ]);

        $entry2 = $this->service->record([
            'tenant_id' => $this->tenant->id,
            'product_id' => $this->product->id,
            'warehouse_id' => $this->warehouse->id,
            'movement_type' => 'sales_issue',
            'quantity' => -30,
            'unit_cost' => 10,
        ]);

        $this->assertEquals(70, (float) $entry2->running_balance);
    }

    public function test_record_with_negative_quantity(): void
    {
        $entry = $this->service->record([
            'tenant_id' => $this->tenant->id,
            'product_id' => $this->product->id,
            'warehouse_id' => $this->warehouse->id,
            'movement_type' => 'adjustment_out',
            'quantity' => -50,
            'unit_cost' => 5.00,
        ]);

        $this->assertEquals(-50, (float) $entry->running_balance);
    }
}
