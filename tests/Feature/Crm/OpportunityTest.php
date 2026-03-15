<?php

declare(strict_types=1);

namespace Tests\Feature\Crm;

use App\Livewire\Crm\OpportunityDetail;
use App\Livewire\Crm\OpportunityForm;
use App\Livewire\Crm\OpportunityIndex;
use App\Models\CrmPipelineStage;
use App\Models\Customer;
use App\Models\Opportunity;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class OpportunityTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    private Tenant $tenant;

    private CrmPipelineStage $stage;

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
        $this->stage = CrmPipelineStage::factory()->create([
            'tenant_id' => $this->tenant->id,
            'name' => 'Qualification',
            'win_probability' => 10.00,
            'display_order' => 1,
        ]);
    }

    public function test_requires_authentication(): void
    {
        $this->get(route('crm.opportunities.index'))->assertRedirect(route('login'));
    }

    public function test_renders_opportunity_list(): void
    {
        $this->actingAs($this->admin);

        Livewire::test(OpportunityIndex::class)
            ->assertStatus(200)
            ->assertSee('Opportunities');
    }

    public function test_search_filters_opportunities(): void
    {
        $this->actingAs($this->admin);

        Opportunity::factory()->create([
            'tenant_id' => $this->tenant->id,
            'name' => 'Alpha Deal',
            'customer_id' => $this->customer->id,
            'pipeline_stage_id' => $this->stage->id,
        ]);
        Opportunity::factory()->create([
            'tenant_id' => $this->tenant->id,
            'name' => 'Beta Deal',
            'customer_id' => $this->customer->id,
            'pipeline_stage_id' => $this->stage->id,
        ]);

        Livewire::test(OpportunityIndex::class)
            ->set('search', 'Alpha')
            ->assertSee('Alpha Deal')
            ->assertDontSee('Beta Deal');
    }

    public function test_can_create_opportunity(): void
    {
        $this->actingAs($this->admin);

        Livewire::test(OpportunityForm::class)
            ->set('name', 'New Deal')
            ->set('customer_id', (string) $this->customer->id)
            ->set('pipeline_stage_id', (string) $this->stage->id)
            ->set('expected_value', '100000')
            ->set('probability', '25')
            ->call('save')
            ->assertRedirect(route('crm.opportunities.index'));

        $this->assertDatabaseHas('opportunities', [
            'tenant_id' => $this->tenant->id,
            'name' => 'New Deal',
            'customer_id' => $this->customer->id,
        ]);
    }

    public function test_can_edit_opportunity(): void
    {
        $this->actingAs($this->admin);

        $opp = Opportunity::factory()->create([
            'tenant_id' => $this->tenant->id,
            'name' => 'Old Deal',
            'customer_id' => $this->customer->id,
            'pipeline_stage_id' => $this->stage->id,
        ]);

        Livewire::test(OpportunityForm::class, ['opportunity' => $opp])
            ->assertSet('name', 'Old Deal')
            ->set('name', 'Updated Deal')
            ->call('save')
            ->assertRedirect(route('crm.opportunities.index'));

        $this->assertDatabaseHas('opportunities', [
            'id' => $opp->id,
            'name' => 'Updated Deal',
        ]);
    }

    public function test_renders_opportunity_detail(): void
    {
        $this->actingAs($this->admin);

        $opp = Opportunity::factory()->create([
            'tenant_id' => $this->tenant->id,
            'name' => 'Detail Deal',
            'customer_id' => $this->customer->id,
            'pipeline_stage_id' => $this->stage->id,
        ]);

        Livewire::test(OpportunityDetail::class, ['opportunity' => $opp])
            ->assertStatus(200)
            ->assertSee('Detail Deal');
    }

    public function test_can_move_to_stage(): void
    {
        $this->actingAs($this->admin);

        $stage2 = CrmPipelineStage::factory()->create([
            'tenant_id' => $this->tenant->id,
            'name' => 'Proposal',
            'win_probability' => 50.00,
            'display_order' => 2,
        ]);

        $opp = Opportunity::factory()->create([
            'tenant_id' => $this->tenant->id,
            'name' => 'Moveable Deal',
            'customer_id' => $this->customer->id,
            'pipeline_stage_id' => $this->stage->id,
            'probability' => 10.00,
        ]);

        Livewire::test(OpportunityDetail::class, ['opportunity' => $opp])
            ->set('selectedStageId', (string) $stage2->id)
            ->call('moveToStage')
            ->assertDispatched('toast');

        $opp->refresh();
        $this->assertEquals($stage2->id, $opp->pipeline_stage_id);
    }

    public function test_can_mark_won(): void
    {
        $this->actingAs($this->admin);

        CrmPipelineStage::factory()->won()->create([
            'tenant_id' => $this->tenant->id,
            'name' => 'Closed Won',
            'display_order' => 5,
        ]);

        $opp = Opportunity::factory()->create([
            'tenant_id' => $this->tenant->id,
            'name' => 'Winnable Deal',
            'customer_id' => $this->customer->id,
            'pipeline_stage_id' => $this->stage->id,
        ]);

        Livewire::test(OpportunityDetail::class, ['opportunity' => $opp])
            ->call('openMarkWonModal')
            ->call('markWon')
            ->assertDispatched('toast');

        $opp->refresh();
        $this->assertNotNull($opp->closed_at);
    }

    public function test_can_mark_lost(): void
    {
        $this->actingAs($this->admin);

        CrmPipelineStage::factory()->lost()->create([
            'tenant_id' => $this->tenant->id,
            'name' => 'Closed Lost',
            'display_order' => 6,
        ]);

        $opp = Opportunity::factory()->create([
            'tenant_id' => $this->tenant->id,
            'name' => 'Losable Deal',
            'customer_id' => $this->customer->id,
            'pipeline_stage_id' => $this->stage->id,
        ]);

        Livewire::test(OpportunityDetail::class, ['opportunity' => $opp])
            ->call('openMarkLostModal')
            ->set('lost_reason', 'Budget constraints prevented deal')
            ->call('markLost')
            ->assertDispatched('toast');

        $opp->refresh();
        $this->assertEquals('Budget constraints prevented deal', $opp->lost_reason);
        $this->assertNotNull($opp->closed_at);
    }
}
