<?php

declare(strict_types=1);

namespace Tests\Feature\Sales;

use App\Livewire\Sales\CustomerForm;
use App\Livewire\Sales\CustomerIndex;
use App\Models\Customer;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class CustomerTest extends TestCase
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
        $this->get(route('sales.customers.index'))->assertRedirect(route('login'));
    }

    public function test_renders_customer_list(): void
    {
        $this->actingAs($this->admin);

        Livewire::test(CustomerIndex::class)
            ->assertStatus(200)
            ->assertSee('Customers');
    }

    public function test_search_filters_customers(): void
    {
        $this->actingAs($this->admin);

        Customer::factory()->create(['tenant_id' => $this->tenant->id, 'name' => 'Acme Corp']);
        Customer::factory()->create(['tenant_id' => $this->tenant->id, 'name' => 'Beta Industries']);

        Livewire::test(CustomerIndex::class)
            ->set('search', 'Acme')
            ->assertSee('Acme Corp')
            ->assertDontSee('Beta Industries');
    }

    public function test_active_filter_works(): void
    {
        $this->actingAs($this->admin);

        Customer::factory()->create(['tenant_id' => $this->tenant->id, 'name' => 'Active Co', 'is_active' => true]);
        Customer::factory()->inactive()->create(['tenant_id' => $this->tenant->id, 'name' => 'Inactive Co']);

        Livewire::test(CustomerIndex::class)
            ->set('activeFilter', '1')
            ->assertSee('Active Co')
            ->assertDontSee('Inactive Co');
    }

    public function test_can_create_customer(): void
    {
        $this->actingAs($this->admin);

        Livewire::test(CustomerForm::class)
            ->set('name', 'New Customer Ltd')
            ->set('email', 'info@newcustomer.com')
            ->set('phone', '08012345678')
            ->set('credit_limit', '500000')
            ->set('payment_terms', 30)
            ->call('save')
            ->assertRedirect(route('sales.customers.index'));

        $this->assertDatabaseHas('customers', [
            'tenant_id' => $this->tenant->id,
            'name' => 'New Customer Ltd',
            'email' => 'info@newcustomer.com',
        ]);
    }

    public function test_can_edit_customer(): void
    {
        $this->actingAs($this->admin);

        $customer = Customer::factory()->create(['tenant_id' => $this->tenant->id, 'name' => 'Old Name']);

        Livewire::test(CustomerForm::class, ['customer' => $customer])
            ->assertSet('name', 'Old Name')
            ->set('name', 'Updated Name')
            ->call('save')
            ->assertRedirect(route('sales.customers.index'));

        $this->assertDatabaseHas('customers', [
            'id' => $customer->id,
            'name' => 'Updated Name',
        ]);
    }
}
