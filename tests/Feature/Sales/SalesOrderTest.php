<?php

declare(strict_types=1);

namespace Tests\Feature\Sales;

use App\Livewire\Sales\SalesOrderDetail;
use App\Livewire\Sales\SalesOrderIndex;
use App\Models\Customer;
use App\Models\SalesOrder;
use App\Models\Tenant;
use App\Models\User;
use App\Models\Warehouse;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class SalesOrderTest extends TestCase
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
        $this->get(route('sales.orders.index'))->assertRedirect(route('login'));
    }

    public function test_renders_sales_order_list(): void
    {
        $this->actingAs($this->admin);

        Livewire::test(SalesOrderIndex::class)
            ->assertStatus(200)
            ->assertSee('Sales Orders');
    }

    public function test_status_filter_works(): void
    {
        $this->actingAs($this->admin);

        $customer = Customer::factory()->create(['tenant_id' => $this->tenant->id]);

        SalesOrder::factory()->create([
            'tenant_id' => $this->tenant->id,
            'customer_id' => $customer->id,
            'status' => 'draft',
            'order_number' => 'SO-DRAFT01',
        ]);

        SalesOrder::factory()->confirmed()->create([
            'tenant_id' => $this->tenant->id,
            'customer_id' => $customer->id,
            'order_number' => 'SO-CONF01',
            'confirmed_by' => $this->admin->id,
        ]);

        Livewire::test(SalesOrderIndex::class)
            ->set('statusFilter', 'draft')
            ->assertSee('SO-DRAFT01')
            ->assertDontSee('SO-CONF01');
    }

    public function test_customer_filter_works(): void
    {
        $this->actingAs($this->admin);

        $customerA = Customer::factory()->create(['tenant_id' => $this->tenant->id, 'name' => 'Customer A']);
        $customerB = Customer::factory()->create(['tenant_id' => $this->tenant->id, 'name' => 'Customer B']);

        SalesOrder::factory()->create([
            'tenant_id' => $this->tenant->id,
            'customer_id' => $customerA->id,
            'order_number' => 'SO-AAA001',
        ]);

        SalesOrder::factory()->create([
            'tenant_id' => $this->tenant->id,
            'customer_id' => $customerB->id,
            'order_number' => 'SO-BBB001',
        ]);

        Livewire::test(SalesOrderIndex::class)
            ->set('customerFilter', (string) $customerA->id)
            ->assertSee('SO-AAA001')
            ->assertDontSee('SO-BBB001');
    }

    public function test_renders_sales_order_detail(): void
    {
        $this->actingAs($this->admin);

        $customer = Customer::factory()->create(['tenant_id' => $this->tenant->id]);
        $warehouse = Warehouse::factory()->create(['tenant_id' => $this->tenant->id]);

        $so = SalesOrder::factory()->create([
            'tenant_id' => $this->tenant->id,
            'customer_id' => $customer->id,
            'order_number' => 'SO-000100',
        ]);

        Livewire::test(SalesOrderDetail::class, ['order' => $so])
            ->assertStatus(200)
            ->assertSee('SO-000100');
    }

    public function test_can_cancel_draft_sales_order(): void
    {
        $this->actingAs($this->admin);

        $customer = Customer::factory()->create(['tenant_id' => $this->tenant->id]);

        $so = SalesOrder::factory()->create([
            'tenant_id' => $this->tenant->id,
            'customer_id' => $customer->id,
            'status' => 'draft',
        ]);

        Livewire::test(SalesOrderDetail::class, ['order' => $so])
            ->call('openCancelModal')
            ->assertSet('showCancelModal', true)
            ->set('cancellationReason', 'Customer request')
            ->call('cancelOrder')
            ->assertDispatched('toast');

        $this->assertEquals('cancelled', $so->fresh()->status);
    }

    public function test_can_open_fulfill_modal(): void
    {
        $this->actingAs($this->admin);

        $customer = Customer::factory()->create(['tenant_id' => $this->tenant->id]);
        $warehouse = Warehouse::factory()->create(['tenant_id' => $this->tenant->id]);

        $so = SalesOrder::factory()->confirmed()->create([
            'tenant_id' => $this->tenant->id,
            'customer_id' => $customer->id,
            'confirmed_by' => $this->admin->id,
        ]);

        Livewire::test(SalesOrderDetail::class, ['order' => $so])
            ->call('openFulfillModal')
            ->assertSet('showFulfillModal', true);
    }
}
