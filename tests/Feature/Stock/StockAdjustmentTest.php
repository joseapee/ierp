<?php

declare(strict_types=1);

namespace Tests\Feature\Stock;

use App\Livewire\Stock\StockAdjustmentList;
use App\Models\StockAdjustment;
use App\Models\Tenant;
use App\Models\User;
use App\Models\Warehouse;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class StockAdjustmentTest extends TestCase
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
        $this->get(route('stock.adjustments.index'))->assertRedirect(route('login'));
    }

    public function test_renders_adjustment_list(): void
    {
        $this->actingAs($this->admin);

        Livewire::test(StockAdjustmentList::class)
            ->assertStatus(200)
            ->assertSee('Stock Adjustments');
    }

    public function test_status_filter_works(): void
    {
        $this->actingAs($this->admin);

        $warehouse = Warehouse::factory()->create(['tenant_id' => $this->tenant->id]);

        StockAdjustment::factory()->create([
            'tenant_id' => $this->tenant->id,
            'warehouse_id' => $warehouse->id,
            'status' => 'draft',
            'adjustment_number' => 'ADJ-DRAFT',
            'adjusted_by' => $this->admin->id,
        ]);

        StockAdjustment::factory()->create([
            'tenant_id' => $this->tenant->id,
            'warehouse_id' => $warehouse->id,
            'status' => 'approved',
            'adjustment_number' => 'ADJ-APPROVED',
            'adjusted_by' => $this->admin->id,
        ]);

        Livewire::test(StockAdjustmentList::class)
            ->set('statusFilter', 'draft')
            ->assertSee('ADJ-DRAFT')
            ->assertDontSee('ADJ-APPROVED');
    }
}
