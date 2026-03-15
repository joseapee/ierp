<?php

declare(strict_types=1);

namespace Tests\Feature\Procurement;

use App\Livewire\Procurement\PurchaseOrderDetail;
use App\Livewire\Procurement\PurchaseOrderIndex;
use App\Models\PurchaseOrder;
use App\Models\Supplier;
use App\Models\Tenant;
use App\Models\User;
use App\Models\Warehouse;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class PurchaseOrderTest extends TestCase
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
        $this->get(route('procurement.purchase-orders.index'))->assertRedirect(route('login'));
    }

    public function test_renders_purchase_order_list(): void
    {
        $this->actingAs($this->admin);

        Livewire::test(PurchaseOrderIndex::class)
            ->assertStatus(200)
            ->assertSee('Purchase Orders');
    }

    public function test_status_filter_works(): void
    {
        $this->actingAs($this->admin);

        $supplier = Supplier::factory()->create(['tenant_id' => $this->tenant->id]);

        PurchaseOrder::factory()->create([
            'tenant_id' => $this->tenant->id,
            'supplier_id' => $supplier->id,
            'status' => 'draft',
            'order_number' => 'PO-DRAFT01',
        ]);

        PurchaseOrder::factory()->confirmed()->create([
            'tenant_id' => $this->tenant->id,
            'supplier_id' => $supplier->id,
            'order_number' => 'PO-CONF01',
            'confirmed_by' => $this->admin->id,
        ]);

        Livewire::test(PurchaseOrderIndex::class)
            ->set('statusFilter', 'draft')
            ->assertSee('PO-DRAFT01')
            ->assertDontSee('PO-CONF01');
    }

    public function test_supplier_filter_works(): void
    {
        $this->actingAs($this->admin);

        $supplierA = Supplier::factory()->create(['tenant_id' => $this->tenant->id, 'name' => 'Supplier A']);
        $supplierB = Supplier::factory()->create(['tenant_id' => $this->tenant->id, 'name' => 'Supplier B']);

        PurchaseOrder::factory()->create([
            'tenant_id' => $this->tenant->id,
            'supplier_id' => $supplierA->id,
            'order_number' => 'PO-AAA001',
        ]);

        PurchaseOrder::factory()->create([
            'tenant_id' => $this->tenant->id,
            'supplier_id' => $supplierB->id,
            'order_number' => 'PO-BBB001',
        ]);

        Livewire::test(PurchaseOrderIndex::class)
            ->set('supplierFilter', (string) $supplierA->id)
            ->assertSee('PO-AAA001')
            ->assertDontSee('PO-BBB001');
    }

    public function test_renders_purchase_order_detail(): void
    {
        $this->actingAs($this->admin);

        $supplier = Supplier::factory()->create(['tenant_id' => $this->tenant->id]);
        $warehouse = Warehouse::factory()->create(['tenant_id' => $this->tenant->id]);

        $po = PurchaseOrder::factory()->create([
            'tenant_id' => $this->tenant->id,
            'supplier_id' => $supplier->id,
            'order_number' => 'PO-000100',
        ]);

        Livewire::test(PurchaseOrderDetail::class, ['purchaseOrder' => $po])
            ->assertStatus(200)
            ->assertSee('PO-000100');
    }

    public function test_can_confirm_draft_purchase_order(): void
    {
        $this->actingAs($this->admin);

        $supplier = Supplier::factory()->create(['tenant_id' => $this->tenant->id]);

        $po = PurchaseOrder::factory()->create([
            'tenant_id' => $this->tenant->id,
            'supplier_id' => $supplier->id,
            'status' => 'draft',
        ]);

        Livewire::test(PurchaseOrderDetail::class, ['purchaseOrder' => $po])
            ->call('confirm')
            ->assertDispatched('toast');

        $this->assertEquals('confirmed', $po->fresh()->status);
    }

    public function test_can_cancel_draft_purchase_order(): void
    {
        $this->actingAs($this->admin);

        $supplier = Supplier::factory()->create(['tenant_id' => $this->tenant->id]);

        $po = PurchaseOrder::factory()->create([
            'tenant_id' => $this->tenant->id,
            'supplier_id' => $supplier->id,
            'status' => 'draft',
        ]);

        Livewire::test(PurchaseOrderDetail::class, ['purchaseOrder' => $po])
            ->call('openCancelModal')
            ->assertSet('showCancelModal', true)
            ->set('cancellationReason', 'No longer needed')
            ->call('cancelOrder')
            ->assertDispatched('toast');

        $this->assertEquals('cancelled', $po->fresh()->status);
    }
}
