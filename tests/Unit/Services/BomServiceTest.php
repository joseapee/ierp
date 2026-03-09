<?php

declare(strict_types=1);

namespace Tests\Unit\Services;

use App\Models\Product;
use App\Models\Tenant;
use App\Models\UnitOfMeasure;
use App\Services\BomService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use RuntimeException;
use Tests\TestCase;

class BomServiceTest extends TestCase
{
    use RefreshDatabase;

    private BomService $service;

    private Tenant $tenant;

    private Product $product;

    private Product $rawMaterial1;

    private Product $rawMaterial2;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new BomService;
        $this->tenant = Tenant::factory()->create();
        app()->instance('current.tenant', $this->tenant);
        $unit = UnitOfMeasure::factory()->create(['tenant_id' => $this->tenant->id]);
        $this->product = Product::factory()->create([
            'tenant_id' => $this->tenant->id,
            'base_unit_id' => $unit->id,
            'type' => 'manufactured',
        ]);
        $this->rawMaterial1 = Product::factory()->create([
            'tenant_id' => $this->tenant->id,
            'base_unit_id' => $unit->id,
            'type' => 'standard',
            'is_purchasable' => true,
        ]);
        $this->rawMaterial2 = Product::factory()->create([
            'tenant_id' => $this->tenant->id,
            'base_unit_id' => $unit->id,
            'type' => 'standard',
            'is_purchasable' => true,
        ]);
    }

    public function test_create_bom_with_items(): void
    {
        $bom = $this->service->create([
            'product_id' => $this->product->id,
            'name' => 'Standard Recipe',
            'version' => '1.0',
            'yield_quantity' => 1,
            'items' => [
                ['product_id' => $this->rawMaterial1->id, 'quantity' => 2, 'unit_cost' => 10.00],
                ['product_id' => $this->rawMaterial2->id, 'quantity' => 3, 'unit_cost' => 5.00],
            ],
        ]);

        $this->assertDatabaseHas('boms', ['name' => 'Standard Recipe', 'product_id' => $this->product->id]);
        $this->assertCount(2, $bom->items);
    }

    public function test_calculate_cost(): void
    {
        $bom = $this->service->create([
            'product_id' => $this->product->id,
            'name' => 'Cost Test',
            'yield_quantity' => 2,
            'items' => [
                ['product_id' => $this->rawMaterial1->id, 'quantity' => 4, 'unit_cost' => 10.00, 'wastage_percentage' => 10],
                ['product_id' => $this->rawMaterial2->id, 'quantity' => 2, 'unit_cost' => 5.00, 'wastage_percentage' => 0],
            ],
        ]);

        $cost = $this->service->calculateCost($bom);

        // (4 * 10 * 1.1 + 2 * 5 * 1.0) / 2 = (44 + 10) / 2 = 27
        $this->assertEquals(27.0, $cost);
    }

    public function test_activate_bom_deactivates_others(): void
    {
        $bom1 = $this->service->create([
            'product_id' => $this->product->id,
            'name' => 'BOM v1',
            'version' => '1.0',
            'items' => [
                ['product_id' => $this->rawMaterial1->id, 'quantity' => 1, 'unit_cost' => 10],
            ],
        ]);

        $this->service->activate($bom1);
        $this->assertEquals('active', $bom1->fresh()->status);

        $bom2 = $this->service->create([
            'product_id' => $this->product->id,
            'name' => 'BOM v2',
            'version' => '2.0',
            'items' => [
                ['product_id' => $this->rawMaterial1->id, 'quantity' => 2, 'unit_cost' => 10],
            ],
        ]);

        $this->service->activate($bom2);

        $this->assertEquals('inactive', $bom1->fresh()->status);
        $this->assertEquals('active', $bom2->fresh()->status);
    }

    public function test_delete_draft_bom(): void
    {
        $bom = $this->service->create([
            'product_id' => $this->product->id,
            'name' => 'Delete Test',
            'items' => [
                ['product_id' => $this->rawMaterial1->id, 'quantity' => 1, 'unit_cost' => 5],
            ],
        ]);

        $this->service->delete($bom);

        $this->assertSoftDeleted('boms', ['id' => $bom->id]);
    }

    public function test_cannot_delete_active_bom(): void
    {
        $bom = $this->service->create([
            'product_id' => $this->product->id,
            'name' => 'Active BOM',
            'items' => [
                ['product_id' => $this->rawMaterial1->id, 'quantity' => 1, 'unit_cost' => 5],
            ],
        ]);

        $this->service->activate($bom);

        $this->expectException(RuntimeException::class);
        $this->service->delete($bom->fresh());
    }

    public function test_duplicate_bom(): void
    {
        $bom = $this->service->create([
            'product_id' => $this->product->id,
            'name' => 'Original',
            'version' => '1.0',
            'items' => [
                ['product_id' => $this->rawMaterial1->id, 'quantity' => 3, 'unit_cost' => 10],
                ['product_id' => $this->rawMaterial2->id, 'quantity' => 1, 'unit_cost' => 5],
            ],
        ]);

        $duplicate = $this->service->duplicate($bom, '2.0');

        $this->assertEquals('2.0', $duplicate->version);
        $this->assertEquals('draft', $duplicate->status);
        $this->assertCount(2, $duplicate->items);
        $this->assertNotEquals($bom->id, $duplicate->id);
    }

    public function test_update_bom_replaces_items(): void
    {
        $bom = $this->service->create([
            'product_id' => $this->product->id,
            'name' => 'Update Test',
            'items' => [
                ['product_id' => $this->rawMaterial1->id, 'quantity' => 1, 'unit_cost' => 10],
                ['product_id' => $this->rawMaterial2->id, 'quantity' => 2, 'unit_cost' => 5],
            ],
        ]);

        $this->assertCount(2, $bom->items);

        $updated = $this->service->update($bom, [
            'name' => 'Updated BOM',
            'items' => [
                ['product_id' => $this->rawMaterial1->id, 'quantity' => 5, 'unit_cost' => 15],
            ],
        ]);

        $this->assertEquals('Updated BOM', $updated->name);
        $this->assertCount(1, $updated->items);
    }

    public function test_paginate_filters_by_status(): void
    {
        $bom1 = $this->service->create([
            'product_id' => $this->product->id,
            'name' => 'Draft BOM',
            'version' => '1.0',
            'items' => [
                ['product_id' => $this->rawMaterial1->id, 'quantity' => 1, 'unit_cost' => 5],
            ],
        ]);

        $bom2 = $this->service->create([
            'product_id' => $this->product->id,
            'name' => 'Active BOM',
            'version' => '2.0',
            'items' => [
                ['product_id' => $this->rawMaterial1->id, 'quantity' => 1, 'unit_cost' => 5],
            ],
        ]);
        $this->service->activate($bom2);

        $draftBoms = $this->service->paginate(['status' => 'draft']);
        $activeBoms = $this->service->paginate(['status' => 'active']);

        $this->assertEquals(1, $draftBoms->total());
        $this->assertEquals(1, $activeBoms->total());
    }
}
