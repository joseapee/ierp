<?php

declare(strict_types=1);

namespace Tests\Feature\Catalog;

use App\Livewire\Catalog\ProductList;
use App\Models\Product;
use App\Models\Tenant;
use App\Models\UnitOfMeasure;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class ProductListTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    private Tenant $tenant;

    private UnitOfMeasure $unit;

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
    }

    public function test_requires_authentication(): void
    {
        $this->get(route('products.index'))->assertRedirect(route('login'));
    }

    public function test_renders_product_list(): void
    {
        $this->actingAs($this->admin);

        Livewire::test(ProductList::class)
            ->assertStatus(200)
            ->assertSee('Products');
    }

    public function test_search_filters_products(): void
    {
        $this->actingAs($this->admin);

        Product::factory()->create(['name' => 'Widget Alpha', 'tenant_id' => $this->tenant->id, 'base_unit_id' => $this->unit->id]);
        Product::factory()->create(['name' => 'Gadget Beta', 'tenant_id' => $this->tenant->id, 'base_unit_id' => $this->unit->id]);

        Livewire::test(ProductList::class)
            ->set('search', 'Widget')
            ->assertSee('Widget Alpha')
            ->assertDontSee('Gadget Beta');
    }

    public function test_type_filter_works(): void
    {
        $this->actingAs($this->admin);

        Product::factory()->create(['name' => 'Standard Prod', 'type' => 'standard', 'tenant_id' => $this->tenant->id, 'base_unit_id' => $this->unit->id]);
        Product::factory()->create(['name' => 'Service Prod', 'type' => 'service', 'tenant_id' => $this->tenant->id, 'base_unit_id' => $this->unit->id]);

        Livewire::test(ProductList::class)
            ->set('typeFilter', 'service')
            ->assertSee('Service Prod')
            ->assertDontSee('Standard Prod');
    }

    public function test_delete_product_dispatches_toast(): void
    {
        $this->actingAs($this->admin);

        $product = Product::factory()->create(['tenant_id' => $this->tenant->id, 'base_unit_id' => $this->unit->id]);

        Livewire::test(ProductList::class)
            ->call('deleteProduct', $product->id)
            ->assertDispatched('toast');

        $this->assertSoftDeleted('products', ['id' => $product->id]);
    }
}
