<?php

declare(strict_types=1);

namespace Tests\Feature\Crm;

use App\Livewire\Crm\LeadDetail;
use App\Livewire\Crm\LeadForm;
use App\Livewire\Crm\LeadIndex;
use App\Models\Lead;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class LeadTest extends TestCase
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
        $this->get(route('crm.leads.index'))->assertRedirect(route('login'));
    }

    public function test_renders_lead_list(): void
    {
        $this->actingAs($this->admin);

        Livewire::test(LeadIndex::class)
            ->assertStatus(200)
            ->assertSee('Leads');
    }

    public function test_search_filters_leads(): void
    {
        $this->actingAs($this->admin);

        Lead::factory()->create(['tenant_id' => $this->tenant->id, 'lead_name' => 'Alpha Lead', 'status' => 'new']);
        Lead::factory()->create(['tenant_id' => $this->tenant->id, 'lead_name' => 'Beta Lead', 'status' => 'new']);

        Livewire::test(LeadIndex::class)
            ->set('search', 'Alpha')
            ->assertSee('Alpha Lead')
            ->assertDontSee('Beta Lead');
    }

    public function test_status_filter_works(): void
    {
        $this->actingAs($this->admin);

        Lead::factory()->create(['tenant_id' => $this->tenant->id, 'lead_name' => 'New Lead', 'status' => 'new']);
        Lead::factory()->create(['tenant_id' => $this->tenant->id, 'lead_name' => 'Qualified Lead', 'status' => 'qualified']);

        Livewire::test(LeadIndex::class)
            ->set('statusFilter', 'new')
            ->assertSee('New Lead')
            ->assertDontSee('Qualified Lead');
    }

    public function test_can_create_lead(): void
    {
        $this->actingAs($this->admin);

        Livewire::test(LeadForm::class)
            ->set('lead_name', 'Test Lead')
            ->set('company_name', 'Test Company')
            ->set('email', 'lead@test.com')
            ->set('source', 'website')
            ->set('estimated_value', '50000')
            ->set('lead_score', '75')
            ->call('save')
            ->assertRedirect(route('crm.leads.index'));

        $this->assertDatabaseHas('leads', [
            'tenant_id' => $this->tenant->id,
            'lead_name' => 'Test Lead',
            'company_name' => 'Test Company',
            'email' => 'lead@test.com',
        ]);
    }

    public function test_can_edit_lead(): void
    {
        $this->actingAs($this->admin);

        $lead = Lead::factory()->create([
            'tenant_id' => $this->tenant->id,
            'lead_name' => 'Old Name',
            'status' => 'new',
        ]);

        Livewire::test(LeadForm::class, ['lead' => $lead])
            ->assertSet('lead_name', 'Old Name')
            ->set('lead_name', 'Updated Name')
            ->call('save')
            ->assertRedirect(route('crm.leads.index'));

        $this->assertDatabaseHas('leads', [
            'id' => $lead->id,
            'lead_name' => 'Updated Name',
        ]);
    }

    public function test_renders_lead_detail(): void
    {
        $this->actingAs($this->admin);

        $lead = Lead::factory()->create([
            'tenant_id' => $this->tenant->id,
            'lead_name' => 'Detail Lead',
            'status' => 'new',
        ]);

        Livewire::test(LeadDetail::class, ['lead' => $lead])
            ->assertStatus(200)
            ->assertSee('Detail Lead');
    }

    public function test_can_convert_lead(): void
    {
        $this->actingAs($this->admin);

        $lead = Lead::factory()->create([
            'tenant_id' => $this->tenant->id,
            'lead_name' => 'Convertible Lead',
            'company_name' => 'Convertible Co',
            'email' => 'convert@test.com',
            'status' => 'qualified',
        ]);

        Livewire::test(LeadDetail::class, ['lead' => $lead])
            ->call('openConvertModal')
            ->assertSet('showConvertModal', true)
            ->call('convert')
            ->assertDispatched('toast');

        $this->assertDatabaseHas('customers', [
            'tenant_id' => $this->tenant->id,
            'name' => 'Convertible Co',
        ]);

        $lead->refresh();
        $this->assertEquals('converted', $lead->status);
        $this->assertNotNull($lead->converted_at);
    }

    public function test_can_mark_lead_lost(): void
    {
        $this->actingAs($this->admin);

        $lead = Lead::factory()->create([
            'tenant_id' => $this->tenant->id,
            'lead_name' => 'Losable Lead',
            'status' => 'new',
        ]);

        Livewire::test(LeadDetail::class, ['lead' => $lead])
            ->call('openLostModal')
            ->set('lostReason', 'Not interested in our services')
            ->call('markLost')
            ->assertDispatched('toast');

        $lead->refresh();
        $this->assertEquals('lost', $lead->status);
    }
}
