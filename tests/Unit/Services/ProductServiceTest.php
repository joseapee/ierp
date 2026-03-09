<?php

declare(strict_types=1);

namespace Tests\Unit\Services;

use App\Models\Product;
use App\Models\ProductAttribute;
use App\Models\ProductAttributeValue;
use App\Models\Tenant;
use App\Models\UnitOfMeasure;
use App\Services\ProductService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductServiceTest extends TestCase
{
    use RefreshDatabase;

    private ProductService $service;

    private Tenant $tenant;

    private UnitOfMeasure $unit;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new ProductService;
        $this->tenant = Tenant::factory()->create();
        app()->instance('current.tenant', $this->tenant);
        $this->unit = UnitOfMeasure::factory()->create(['tenant_id' => $this->tenant->id]);
    }

    public function test_paginate_returns_paginated_products(): void
    {
        Product::factory()->count(20)->create(['tenant_id' => $this->tenant->id, 'base_unit_id' => $this->unit->id]);

        $result = $this->service->paginate([], 10);

        $this->assertCount(10, $result->items());
        $this->assertEquals(20, $result->total());
    }

    public function test_paginate_filters_by_search(): void
    {
        Product::factory()->create(['name' => 'Widget Alpha', 'tenant_id' => $this->tenant->id, 'base_unit_id' => $this->unit->id]);
        Product::factory()->create(['name' => 'Gadget Beta', 'tenant_id' => $this->tenant->id, 'base_unit_id' => $this->unit->id]);

        $result = $this->service->paginate(['search' => 'Widget']);

        $this->assertCount(1, $result->items());
        $this->assertEquals('Widget Alpha', $result->items()[0]->name);
    }

    public function test_paginate_filters_by_type(): void
    {
        Product::factory()->create(['type' => 'standard', 'tenant_id' => $this->tenant->id, 'base_unit_id' => $this->unit->id]);
        Product::factory()->create(['type' => 'service', 'tenant_id' => $this->tenant->id, 'base_unit_id' => $this->unit->id]);

        $result = $this->service->paginate(['type' => 'service']);

        $this->assertCount(1, $result->items());
        $this->assertEquals('service', $result->items()[0]->type);
    }

    public function test_create_product(): void
    {
        $product = $this->service->create([
            'name' => 'Test Product',
            'slug' => 'test-product',
            'sku' => 'TST-001',
            'type' => 'standard',
            'base_unit_id' => $this->unit->id,
            'cost_price' => 10.50,
            'sell_price' => 25.00,
        ]);

        $this->assertDatabaseHas('products', [
            'name' => 'Test Product',
            'sku' => 'TST-001',
            'tenant_id' => $this->tenant->id,
        ]);
    }

    public function test_create_variable_product_syncs_attributes(): void
    {
        $attribute = ProductAttribute::factory()->create(['tenant_id' => $this->tenant->id]);

        $product = $this->service->create([
            'name' => 'Variable Product',
            'slug' => 'variable-product',
            'sku' => 'VAR-001',
            'type' => 'variable',
            'base_unit_id' => $this->unit->id,
            'attribute_ids' => [$attribute->id],
        ]);

        $this->assertTrue($product->attributes->contains($attribute));
    }

    public function test_update_product(): void
    {
        $product = Product::factory()->create(['tenant_id' => $this->tenant->id, 'base_unit_id' => $this->unit->id]);

        $updated = $this->service->update($product, ['name' => 'Updated Product']);

        $this->assertEquals('Updated Product', $updated->name);
    }

    public function test_delete_product(): void
    {
        $product = Product::factory()->create(['tenant_id' => $this->tenant->id, 'base_unit_id' => $this->unit->id]);

        $result = $this->service->delete($product);

        $this->assertTrue($result);
        $this->assertSoftDeleted('products', ['id' => $product->id]);
    }

    public function test_generate_variants(): void
    {
        $product = Product::factory()->create([
            'type' => 'variable',
            'sku' => 'TST',
            'tenant_id' => $this->tenant->id,
            'base_unit_id' => $this->unit->id,
        ]);

        $sizeAttr = ProductAttribute::factory()->create(['name' => 'Size', 'tenant_id' => $this->tenant->id]);
        $small = ProductAttributeValue::factory()->create(['product_attribute_id' => $sizeAttr->id, 'value' => 'S', 'slug' => 's']);
        $medium = ProductAttributeValue::factory()->create(['product_attribute_id' => $sizeAttr->id, 'value' => 'M', 'slug' => 'm']);

        $colorAttr = ProductAttribute::factory()->create(['name' => 'Color', 'tenant_id' => $this->tenant->id]);
        $red = ProductAttributeValue::factory()->create(['product_attribute_id' => $colorAttr->id, 'value' => 'Red', 'slug' => 'red']);

        $this->service->generateVariants($product, [
            $sizeAttr->id => [$small->id, $medium->id],
            $colorAttr->id => [$red->id],
        ]);

        $this->assertCount(2, $product->variants);
    }
}
