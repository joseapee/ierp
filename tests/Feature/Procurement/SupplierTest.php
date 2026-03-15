<?php

declare(strict_types=1);

namespace Tests\Feature\Procurement;

use App\Livewire\Procurement\SupplierForm;
use App\Livewire\Procurement\SupplierIndex;
use App\Models\Supplier;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class SupplierTest extends TestCase
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
        $this->get(route('procurement.suppliers.index'))->assertRedirect(route('login'));
    }

    public function test_renders_supplier_list(): void
    {
        $this->actingAs($this->admin);

        Livewire::test(SupplierIndex::class)
            ->assertStatus(200)
            ->assertSee('Suppliers');
    }

    public function test_search_filters_suppliers(): void
    {
        $this->actingAs($this->admin);

        Supplier::factory()->create(['tenant_id' => $this->tenant->id, 'name' => 'Alpha Supplies']);
        Supplier::factory()->create(['tenant_id' => $this->tenant->id, 'name' => 'Omega Parts']);

        Livewire::test(SupplierIndex::class)
            ->set('search', 'Alpha')
            ->assertSee('Alpha Supplies')
            ->assertDontSee('Omega Parts');
    }

    public function test_active_filter_works(): void
    {
        $this->actingAs($this->admin);

        Supplier::factory()->create(['tenant_id' => $this->tenant->id, 'name' => 'Active Supplier', 'is_active' => true]);
        Supplier::factory()->inactive()->create(['tenant_id' => $this->tenant->id, 'name' => 'Inactive Supplier']);

        Livewire::test(SupplierIndex::class)
            ->set('activeFilter', '1')
            ->assertSee('Active Supplier')
            ->assertDontSee('Inactive Supplier');
    }

    public function test_can_create_supplier(): void
    {
        $this->actingAs($this->admin);

        Livewire::test(SupplierForm::class)
            ->set('name', 'New Supplier Ltd')
            ->set('email', 'contact@newsupplier.com')
            ->set('phone', '08098765432')
            ->set('contact_person', 'John Doe')
            ->set('payment_terms', 45)
            ->call('save')
            ->assertRedirect(route('procurement.suppliers.index'));

        $this->assertDatabaseHas('suppliers', [
            'tenant_id' => $this->tenant->id,
            'name' => 'New Supplier Ltd',
            'contact_person' => 'John Doe',
        ]);
    }

    public function test_can_edit_supplier(): void
    {
        $this->actingAs($this->admin);

        $supplier = Supplier::factory()->create(['tenant_id' => $this->tenant->id, 'name' => 'Old Supplier']);

        Livewire::test(SupplierForm::class, ['supplier' => $supplier])
            ->assertSet('name', 'Old Supplier')
            ->set('name', 'Updated Supplier')
            ->call('save')
            ->assertRedirect(route('procurement.suppliers.index'));

        $this->assertDatabaseHas('suppliers', [
            'id' => $supplier->id,
            'name' => 'Updated Supplier',
        ]);
    }
}
