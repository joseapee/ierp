<?php

declare(strict_types=1);

namespace Tests\Unit\Services;

use App\Models\Product;
use App\Models\Tenant;
use App\Models\UnitOfMeasure;
use App\Services\BomService;
use App\Services\PricingService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PricingServiceTest extends TestCase
{
    use RefreshDatabase;

    private PricingService $service;

    private BomService $bomService;

    private Tenant $tenant;

    protected function setUp(): void
    {
        parent::setUp();
        $this->bomService = new BomService;
        $this->service = new PricingService($this->bomService);
        $this->tenant = Tenant::factory()->create();
        app()->instance('current.tenant', $this->tenant);
    }

    public function test_manual_pricing_returns_sell_price(): void
    {
        $unit = UnitOfMeasure::factory()->create(['tenant_id' => $this->tenant->id]);
        $product = Product::factory()->create([
            'tenant_id' => $this->tenant->id,
            'base_unit_id' => $unit->id,
            'pricing_mode' => 'manual',
            'cost_price' => 50.00,
            'sell_price' => 100.00,
        ]);

        $price = $this->service->computeSellPrice($product);

        $this->assertEquals(100.00, $price);
    }

    public function test_percentage_markup_pricing(): void
    {
        $unit = UnitOfMeasure::factory()->create(['tenant_id' => $this->tenant->id]);
        $product = Product::factory()->create([
            'tenant_id' => $this->tenant->id,
            'base_unit_id' => $unit->id,
            'pricing_mode' => 'percentage_markup',
            'cost_price' => 100.00,
            'markup_percentage' => 50.00,
        ]);

        $price = $this->service->computeSellPrice($product);

        // 100 * (1 + 50/100) = 150
        $this->assertEquals(150.00, $price);
    }

    public function test_fixed_profit_pricing(): void
    {
        $unit = UnitOfMeasure::factory()->create(['tenant_id' => $this->tenant->id]);
        $product = Product::factory()->create([
            'tenant_id' => $this->tenant->id,
            'base_unit_id' => $unit->id,
            'pricing_mode' => 'fixed_profit',
            'cost_price' => 100.00,
            'profit_amount' => 25.00,
        ]);

        $price = $this->service->computeSellPrice($product);

        // 100 + 25 = 125
        $this->assertEquals(125.00, $price);
    }

    public function test_compute_sell_price_with_cost_override(): void
    {
        $unit = UnitOfMeasure::factory()->create(['tenant_id' => $this->tenant->id]);
        $product = Product::factory()->create([
            'tenant_id' => $this->tenant->id,
            'base_unit_id' => $unit->id,
            'pricing_mode' => 'percentage_markup',
            'cost_price' => 100.00,
            'markup_percentage' => 20.00,
        ]);

        $price = $this->service->computeSellPrice($product, 200.00);

        // 200 * (1 + 20/100) = 240
        $this->assertEquals(240.00, $price);
    }

    public function test_recalculate_from_bom(): void
    {
        $unit = UnitOfMeasure::factory()->create(['tenant_id' => $this->tenant->id]);
        $product = Product::factory()->create([
            'tenant_id' => $this->tenant->id,
            'base_unit_id' => $unit->id,
            'type' => 'manufactured',
            'pricing_mode' => 'percentage_markup',
            'markup_percentage' => 50.00,
            'cost_price' => 0,
            'sell_price' => 0,
        ]);

        $rawMaterial = Product::factory()->create([
            'tenant_id' => $this->tenant->id,
            'base_unit_id' => $unit->id,
            'type' => 'standard',
            'is_purchasable' => true,
        ]);

        $bom = $this->bomService->create([
            'product_id' => $product->id,
            'name' => 'Test BOM',
            'yield_quantity' => 1,
            'items' => [
                ['product_id' => $rawMaterial->id, 'quantity' => 2, 'unit_cost' => 50.00],
            ],
        ]);
        $this->bomService->activate($bom);

        $updated = $this->service->recalculateFromBom($product->fresh());

        // BOM cost = 2 * 50 = 100, sell = 100 * 1.5 = 150
        $this->assertEquals(100.00, (float) $updated->cost_price);
        $this->assertEquals(150.00, (float) $updated->sell_price);
    }
}
