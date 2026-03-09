<?php

declare(strict_types=1);

namespace Tests\Feature\Manufacturing;

use App\Livewire\Manufacturing\BomList;
use App\Models\Product;
use App\Models\Tenant;
use App\Models\UnitOfMeasure;
use App\Models\User;
use App\Services\BomService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class BomListTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    private Tenant $tenant;

    private UnitOfMeasure $unit;

    private Product $product;

    private Product $rawMaterial;

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
        $this->rawMaterial = Product::factory()->create([
            'tenant_id' => $this->tenant->id,
            'base_unit_id' => $this->unit->id,
            'type' => 'standard',
            'is_purchasable' => true,
        ]);
    }

    public function test_requires_authentication(): void
    {
        $this->get(route('manufacturing.boms.index'))->assertRedirect(route('login'));
    }

    public function test_renders_bom_list(): void
    {
        $this->actingAs($this->admin);

        Livewire::test(BomList::class)
            ->assertStatus(200)
            ->assertSee('Bill of Materials');
    }

    public function test_search_filters_boms(): void
    {
        $this->actingAs($this->admin);

        $service = app(BomService::class);
        $service->create([
            'product_id' => $this->product->id,
            'name' => 'Standard Recipe',
            'version' => '1.0',
            'items' => [
                ['product_id' => $this->rawMaterial->id, 'quantity' => 1, 'unit_cost' => 10],
            ],
        ]);
        $service->create([
            'product_id' => $this->product->id,
            'name' => 'Premium Recipe',
            'version' => '2.0',
            'items' => [
                ['product_id' => $this->rawMaterial->id, 'quantity' => 2, 'unit_cost' => 20],
            ],
        ]);

        Livewire::test(BomList::class)
            ->set('search', 'Standard')
            ->assertSee('Standard Recipe')
            ->assertDontSee('Premium Recipe');
    }

    public function test_status_filter_works(): void
    {
        $this->actingAs($this->admin);

        $service = app(BomService::class);
        $bom1 = $service->create([
            'product_id' => $this->product->id,
            'name' => 'Draft BOM',
            'version' => '1.0',
            'items' => [
                ['product_id' => $this->rawMaterial->id, 'quantity' => 1, 'unit_cost' => 10],
            ],
        ]);
        $bom2 = $service->create([
            'product_id' => $this->product->id,
            'name' => 'Active BOM',
            'version' => '2.0',
            'items' => [
                ['product_id' => $this->rawMaterial->id, 'quantity' => 1, 'unit_cost' => 10],
            ],
        ]);
        $service->activate($bom2);

        Livewire::test(BomList::class)
            ->set('statusFilter', 'active')
            ->assertSee('Active BOM')
            ->assertDontSee('Draft BOM');
    }

    public function test_delete_draft_bom_dispatches_toast(): void
    {
        $this->actingAs($this->admin);

        $bom = app(BomService::class)->create([
            'product_id' => $this->product->id,
            'name' => 'Delete Me',
            'items' => [
                ['product_id' => $this->rawMaterial->id, 'quantity' => 1, 'unit_cost' => 5],
            ],
        ]);

        Livewire::test(BomList::class)
            ->call('deleteBom', $bom->id)
            ->assertDispatched('toast');

        $this->assertSoftDeleted('boms', ['id' => $bom->id]);
    }

    public function test_activate_bom_dispatches_toast(): void
    {
        $this->actingAs($this->admin);

        $bom = app(BomService::class)->create([
            'product_id' => $this->product->id,
            'name' => 'Activate Me',
            'items' => [
                ['product_id' => $this->rawMaterial->id, 'quantity' => 1, 'unit_cost' => 5],
            ],
        ]);

        Livewire::test(BomList::class)
            ->call('activateBom', $bom->id)
            ->assertDispatched('toast');

        $this->assertEquals('active', $bom->fresh()->status);
    }
}
