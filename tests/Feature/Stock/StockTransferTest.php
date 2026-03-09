<?php

declare(strict_types=1);

namespace Tests\Feature\Stock;

use App\Livewire\Stock\StockTransferList;
use App\Models\StockTransfer;
use App\Models\Tenant;
use App\Models\User;
use App\Models\Warehouse;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class StockTransferTest extends TestCase
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
        $this->get(route('stock.transfers.index'))->assertRedirect(route('login'));
    }

    public function test_renders_transfer_list(): void
    {
        $this->actingAs($this->admin);

        Livewire::test(StockTransferList::class)
            ->assertStatus(200)
            ->assertSee('Stock Transfers');
    }

    public function test_status_filter_works(): void
    {
        $this->actingAs($this->admin);

        $wh1 = Warehouse::factory()->create(['tenant_id' => $this->tenant->id]);
        $wh2 = Warehouse::factory()->create(['tenant_id' => $this->tenant->id]);

        StockTransfer::factory()->create([
            'tenant_id' => $this->tenant->id,
            'from_warehouse_id' => $wh1->id,
            'to_warehouse_id' => $wh2->id,
            'status' => 'draft',
            'transfer_number' => 'TRF-DRAFT',
            'initiated_by' => $this->admin->id,
        ]);

        StockTransfer::factory()->create([
            'tenant_id' => $this->tenant->id,
            'from_warehouse_id' => $wh1->id,
            'to_warehouse_id' => $wh2->id,
            'status' => 'completed',
            'transfer_number' => 'TRF-DONE',
            'initiated_by' => $this->admin->id,
        ]);

        Livewire::test(StockTransferList::class)
            ->set('statusFilter', 'draft')
            ->assertSee('TRF-DRAFT')
            ->assertDontSee('TRF-DONE');
    }
}
