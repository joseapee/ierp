<?php

declare(strict_types=1);

namespace Tests\Unit\Services;

use App\Models\CrmPipelineStage;
use App\Models\Customer;
use App\Models\Opportunity;
use App\Models\Tenant;
use App\Models\User;
use App\Services\JournalService;
use App\Services\OpportunityService;
use App\Services\SalesOrderService;
use App\Services\StockLedgerService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OpportunityServiceTest extends TestCase
{
    use RefreshDatabase;

    private OpportunityService $service;

    private Tenant $tenant;

    private User $admin;

    private Customer $customer;

    private CrmPipelineStage $stage;

    protected function setUp(): void
    {
        parent::setUp();
        $this->tenant = Tenant::factory()->create();
        app()->instance('current.tenant', $this->tenant);
        $this->admin = User::factory()->create([
            'tenant_id' => $this->tenant->id,
            'is_super_admin' => true,
        ]);
        $this->actingAs($this->admin);

        $this->customer = Customer::factory()->create(['tenant_id' => $this->tenant->id]);
        $this->stage = CrmPipelineStage::factory()->create([
            'tenant_id' => $this->tenant->id,
            'win_probability' => 25,
            'display_order' => 10,
        ]);

        $salesOrderService = new SalesOrderService(new StockLedgerService, new JournalService);
        $this->service = new OpportunityService($salesOrderService);
    }

    public function test_create_opportunity_with_auto_probability(): void
    {
        $opp = $this->service->create([
            'tenant_id' => $this->tenant->id,
            'name' => 'Big Deal',
            'customer_id' => $this->customer->id,
            'pipeline_stage_id' => $this->stage->id,
            'expected_value' => 100000,
        ]);

        $this->assertEquals(25, (float) $opp->probability);
        $this->assertDatabaseHas('opportunities', ['name' => 'Big Deal']);
    }

    public function test_create_opportunity_with_explicit_probability(): void
    {
        $opp = $this->service->create([
            'tenant_id' => $this->tenant->id,
            'name' => 'Custom Prob',
            'customer_id' => $this->customer->id,
            'pipeline_stage_id' => $this->stage->id,
            'expected_value' => 50000,
            'probability' => 60,
        ]);

        $this->assertEquals(60, (float) $opp->probability);
    }

    public function test_move_to_stage_updates_probability(): void
    {
        $opp = Opportunity::factory()->create([
            'tenant_id' => $this->tenant->id,
            'customer_id' => $this->customer->id,
            'pipeline_stage_id' => $this->stage->id,
            'probability' => 25,
        ]);

        $newStage = CrmPipelineStage::factory()->create([
            'tenant_id' => $this->tenant->id,
            'win_probability' => 75,
            'display_order' => 20,
        ]);

        $moved = $this->service->moveToStage($opp, $newStage->id);

        $this->assertEquals($newStage->id, $moved->pipeline_stage_id);
        $this->assertEquals(75, (float) $moved->probability);
        $this->assertNull($moved->closed_at);
    }

    public function test_move_to_terminal_stage_sets_closed_at(): void
    {
        $opp = Opportunity::factory()->create([
            'tenant_id' => $this->tenant->id,
            'customer_id' => $this->customer->id,
            'pipeline_stage_id' => $this->stage->id,
        ]);

        $wonStage = CrmPipelineStage::factory()->won()->create([
            'tenant_id' => $this->tenant->id,
            'display_order' => 50,
        ]);

        $moved = $this->service->moveToStage($opp, $wonStage->id);

        $this->assertNotNull($moved->closed_at);
        $this->assertEquals(100, (float) $moved->probability);
    }

    public function test_mark_won(): void
    {
        $wonStage = CrmPipelineStage::factory()->won()->create([
            'tenant_id' => $this->tenant->id,
            'display_order' => 50,
        ]);

        $opp = Opportunity::factory()->create([
            'tenant_id' => $this->tenant->id,
            'customer_id' => $this->customer->id,
            'pipeline_stage_id' => $this->stage->id,
        ]);

        $result = $this->service->markWon($opp);

        $this->assertEquals($wonStage->id, $result->pipeline_stage_id);
        $this->assertEquals(100, (float) $result->probability);
        $this->assertNotNull($result->closed_at);
    }

    public function test_mark_won_without_configured_stage_throws(): void
    {
        $opp = Opportunity::factory()->create([
            'tenant_id' => $this->tenant->id,
            'customer_id' => $this->customer->id,
            'pipeline_stage_id' => $this->stage->id,
        ]);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('No "Won" pipeline stage configured.');
        $this->service->markWon($opp);
    }

    public function test_mark_lost(): void
    {
        $lostStage = CrmPipelineStage::factory()->lost()->create([
            'tenant_id' => $this->tenant->id,
            'display_order' => 60,
        ]);

        $opp = Opportunity::factory()->create([
            'tenant_id' => $this->tenant->id,
            'customer_id' => $this->customer->id,
            'pipeline_stage_id' => $this->stage->id,
        ]);

        $result = $this->service->markLost($opp, 'Price too high');

        $this->assertEquals($lostStage->id, $result->pipeline_stage_id);
        $this->assertEquals(0, (float) $result->probability);
        $this->assertEquals('Price too high', $result->lost_reason);
        $this->assertNotNull($result->closed_at);
    }

    public function test_mark_lost_without_configured_stage_throws(): void
    {
        $opp = Opportunity::factory()->create([
            'tenant_id' => $this->tenant->id,
            'customer_id' => $this->customer->id,
            'pipeline_stage_id' => $this->stage->id,
        ]);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('No "Lost" pipeline stage configured.');
        $this->service->markLost($opp);
    }

    public function test_get_board_data(): void
    {
        $stage2 = CrmPipelineStage::factory()->create([
            'tenant_id' => $this->tenant->id,
            'display_order' => 20,
        ]);

        Opportunity::factory()->count(3)->create([
            'tenant_id' => $this->tenant->id,
            'customer_id' => $this->customer->id,
            'pipeline_stage_id' => $this->stage->id,
        ]);

        Opportunity::factory()->count(2)->create([
            'tenant_id' => $this->tenant->id,
            'customer_id' => $this->customer->id,
            'pipeline_stage_id' => $stage2->id,
        ]);

        $boardData = $this->service->getBoardData();

        $this->assertCount(2, $boardData);
        $this->assertEquals(3, $boardData[0]['opportunities']->count());
        $this->assertEquals(2, $boardData[1]['opportunities']->count());
    }

    public function test_update_opportunity(): void
    {
        $opp = Opportunity::factory()->create([
            'tenant_id' => $this->tenant->id,
            'customer_id' => $this->customer->id,
            'pipeline_stage_id' => $this->stage->id,
            'name' => 'Original',
        ]);

        $updated = $this->service->update($opp, ['name' => 'Updated']);

        $this->assertEquals('Updated', $updated->name);
    }
}
