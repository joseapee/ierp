<?php

declare(strict_types=1);

namespace Tests\Feature\Manufacturing;

use App\Livewire\Manufacturing\ProductionOrderList;
use App\Models\Product;
use App\Models\Tenant;
use App\Models\UnitOfMeasure;
use App\Models\User;
use App\Models\Warehouse;
use App\Services\BomService;
use App\Services\ProductionService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class ProductionOrderListTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    private Tenant $tenant;

    private UnitOfMeasure $unit;

    private Product $product;

    private Warehouse $warehouse;

    protected function setUp(): void
    {
        parent::setUp();
        $this->tenant = Tenant::factory()->create();
        app()->instance('current.tenant', $this->tenant);
        $this->admin = User::factory()->create([
            'tenant_id' => $this->tenant->id,
            'is_super_admin' => true,
        ]);
        $this->unit = UnitOfMeasure::factory()->create(['tenant_id' => $this->tenant->id]);
        $this->product = Product::factory()->create([
            'tenant_id' => $this->tenant->id,
            'base_unit_id' => $this->unit->id,
            'type' => 'manufactured',
        ]);
        $this->warehouse = Warehouse::factory()->create(['tenant_id' => $this->tenant->id]);
    }

    public function test_requires_authentication(): void
    {
        $this->get(route('manufacturing.orders.index'))->assertRedirect(route('login'));
    }

    public function test_renders_production_order_list(): void
    {
        $this->actingAs($this->admin);

        Livewire::test(ProductionOrderList::class)
            ->assertStatus(200)
            ->assertSee('Production Orders');
    }

    public function test_status_filter_works(): void
    {
        $this->actingAs($this->admin);

        $rawMaterial = Product::factory()->create([
            'tenant_id' => $this->tenant->id,
            'base_unit_id' => $this->unit->id,
            'type' => 'standard',
            'is_purchasable' => true,
        ]);

        $bom = app(BomService::class)->create([
            'product_id' => $this->product->id,
            'name' => 'Test BOM',
            'items' => [
                ['product_id' => $rawMaterial->id, 'quantity' => 1, 'unit_cost' => 10],
            ],
        ]);

        $service = app(ProductionService::class);
        $order1 = $service->createOrder([
            'order_number' => 'PO-000001',
            'product_id' => $this->product->id,
            'bom_id' => $bom->id,
            'warehouse_id' => $this->warehouse->id,
            'planned_quantity' => 5,
        ]);

        $order2 = $service->createOrder([
            'order_number' => 'PO-000002',
            'product_id' => $this->product->id,
            'bom_id' => $bom->id,
            'warehouse_id' => $this->warehouse->id,
            'planned_quantity' => 10,
        ]);
        $service->confirmOrder($order2);

        Livewire::test(ProductionOrderList::class)
            ->set('statusFilter', 'confirmed')
            ->assertSee('PO-000002')
            ->assertDontSee('PO-000001');
    }

    public function test_search_filters_orders(): void
    {
        $this->actingAs($this->admin);

        $rawMaterial = Product::factory()->create([
            'tenant_id' => $this->tenant->id,
            'base_unit_id' => $this->unit->id,
            'type' => 'standard',
            'is_purchasable' => true,
        ]);

        $bom = app(BomService::class)->create([
            'product_id' => $this->product->id,
            'name' => 'Test BOM',
            'items' => [
                ['product_id' => $rawMaterial->id, 'quantity' => 1, 'unit_cost' => 10],
            ],
        ]);

        $service = app(ProductionService::class);
        $service->createOrder([
            'order_number' => 'PO-ABC123',
            'product_id' => $this->product->id,
            'bom_id' => $bom->id,
            'warehouse_id' => $this->warehouse->id,
            'planned_quantity' => 5,
        ]);
        $service->createOrder([
            'order_number' => 'PO-XYZ789',
            'product_id' => $this->product->id,
            'bom_id' => $bom->id,
            'warehouse_id' => $this->warehouse->id,
            'planned_quantity' => 10,
        ]);

        Livewire::test(ProductionOrderList::class)
            ->set('search', 'ABC')
            ->assertSee('PO-ABC123')
            ->assertDontSee('PO-XYZ789');
    }
}
