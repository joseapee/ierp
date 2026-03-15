<?php

declare(strict_types=1);

namespace Tests\Feature\Crm;

use App\Livewire\Crm\ContactForm;
use App\Livewire\Crm\ContactIndex;
use App\Models\CrmContact;
use App\Models\Customer;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class ContactTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    private Tenant $tenant;

    private Customer $customer;

    protected function setUp(): void
    {
        parent::setUp();
        $this->tenant = Tenant::factory()->create();
        app()->instance('current.tenant', $this->tenant);
        $this->admin = User::factory()->create([
            'tenant_id' => $this->tenant->id,
            'is_super_admin' => true,
        ]);
        $this->customer = Customer::factory()->create(['tenant_id' => $this->tenant->id]);
    }

    public function test_requires_authentication(): void
    {
        $this->get(route('crm.contacts.index'))->assertRedirect(route('login'));
    }

    public function test_renders_contact_list(): void
    {
        $this->actingAs($this->admin);

        Livewire::test(ContactIndex::class)
            ->assertStatus(200)
            ->assertSee('Contacts');
    }

    public function test_search_filters_contacts(): void
    {
        $this->actingAs($this->admin);

        CrmContact::factory()->create([
            'tenant_id' => $this->tenant->id,
            'first_name' => 'John',
            'last_name' => 'Doe',
            'customer_id' => $this->customer->id,
        ]);
        CrmContact::factory()->create([
            'tenant_id' => $this->tenant->id,
            'first_name' => 'Jane',
            'last_name' => 'Smith',
            'customer_id' => $this->customer->id,
        ]);

        Livewire::test(ContactIndex::class)
            ->set('search', 'John')
            ->assertSee('John')
            ->assertDontSee('Jane');
    }

    public function test_customer_filter_works(): void
    {
        $this->actingAs($this->admin);

        $customer2 = Customer::factory()->create(['tenant_id' => $this->tenant->id, 'name' => 'Other Corp']);

        CrmContact::factory()->create([
            'tenant_id' => $this->tenant->id,
            'first_name' => 'Alice',
            'last_name' => 'A',
            'customer_id' => $this->customer->id,
        ]);
        CrmContact::factory()->create([
            'tenant_id' => $this->tenant->id,
            'first_name' => 'Bob',
            'last_name' => 'B',
            'customer_id' => $customer2->id,
        ]);

        Livewire::test(ContactIndex::class)
            ->set('customerFilter', (string) $this->customer->id)
            ->assertSee('Alice')
            ->assertDontSee('Bob');
    }

    public function test_can_create_contact(): void
    {
        $this->actingAs($this->admin);

        Livewire::test(ContactForm::class)
            ->set('first_name', 'New')
            ->set('last_name', 'Contact')
            ->set('email', 'new@contact.com')
            ->set('customer_id', (string) $this->customer->id)
            ->set('job_title', 'CEO')
            ->call('save')
            ->assertRedirect(route('crm.contacts.index'));

        $this->assertDatabaseHas('crm_contacts', [
            'tenant_id' => $this->tenant->id,
            'first_name' => 'New',
            'last_name' => 'Contact',
            'email' => 'new@contact.com',
        ]);
    }

    public function test_can_edit_contact(): void
    {
        $this->actingAs($this->admin);

        $contact = CrmContact::factory()->create([
            'tenant_id' => $this->tenant->id,
            'first_name' => 'Old',
            'last_name' => 'Name',
            'customer_id' => $this->customer->id,
        ]);

        Livewire::test(ContactForm::class, ['contact' => $contact])
            ->assertSet('first_name', 'Old')
            ->set('first_name', 'Updated')
            ->call('save')
            ->assertRedirect(route('crm.contacts.index'));

        $this->assertDatabaseHas('crm_contacts', [
            'id' => $contact->id,
            'first_name' => 'Updated',
        ]);
    }

    public function test_validation_requires_names(): void
    {
        $this->actingAs($this->admin);

        Livewire::test(ContactForm::class)
            ->set('first_name', '')
            ->set('last_name', '')
            ->call('save')
            ->assertHasErrors(['first_name', 'last_name']);
    }
}
