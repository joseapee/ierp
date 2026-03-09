<?php

declare(strict_types=1);

namespace Tests\Feature\Warehouse;

use App\Livewire\Warehouse\WarehouseList;
use App\Models\Tenant;
use App\Models\User;
use App\Models\Warehouse;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class WarehouseListTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    private Tenant $tenant;

    protected function setUp(): void
    {
        parent::setUp();
        $this->tenant = Tenant::factory()->create();
        app()->instance('current.tenant', $this->tenant);
        $this->admin = User::factory()->create([
            'tenant_id' => $this->tenant->id,
            'is_super_admin' => true,
        ]);
    }

    public function test_requires_authentication(): void
    {
        $this->get(route('warehouses.index'))->assertRedirect(route('login'));
    }

    public function test_renders_warehouse_list(): void
    {
        $this->actingAs($this->admin);

        Livewire::test(WarehouseList::class)
            ->assertStatus(200)
            ->assertSee('Warehouses');
    }

    public function test_search_filters_warehouses(): void
    {
        $this->actingAs($this->admin);

        Warehouse::factory()->create(['name' => 'Main Warehouse', 'tenant_id' => $this->tenant->id]);
        Warehouse::factory()->create(['name' => 'Secondary Hub', 'tenant_id' => $this->tenant->id]);

        Livewire::test(WarehouseList::class)
            ->set('search', 'Main')
            ->assertSee('Main Warehouse')
            ->assertDontSee('Secondary Hub');
    }

    public function test_delete_warehouse_dispatches_toast(): void
    {
        $this->actingAs($this->admin);

        $warehouse = Warehouse::factory()->create(['tenant_id' => $this->tenant->id]);

        Livewire::test(WarehouseList::class)
            ->call('deleteWarehouse', $warehouse->id)
            ->assertDispatched('toast');

        $this->assertSoftDeleted('warehouses', ['id' => $warehouse->id]);
    }
}
