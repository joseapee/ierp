<?php

declare(strict_types=1);

namespace Tests\Unit\Services;

use App\Models\CrmActivity;
use App\Models\Tenant;
use App\Models\User;
use App\Services\CrmActivityService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CrmActivityServiceTest extends TestCase
{
    use RefreshDatabase;

    private CrmActivityService $service;

    private Tenant $tenant;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->tenant = Tenant::factory()->create();
        app()->instance('current.tenant', $this->tenant);
        $this->user = User::factory()->create(['tenant_id' => $this->tenant->id]);
        $this->service = new CrmActivityService;
    }

    public function test_create_activity(): void
    {
        $activity = $this->service->create([
            'tenant_id' => $this->tenant->id,
            'type' => 'call',
            'subject' => 'Follow-up call',
            'description' => 'Call client about proposal.',
            'assigned_to' => $this->user->id,
            'due_date' => now()->addDays(1),
            'status' => 'pending',
        ]);

        $this->assertDatabaseHas('crm_activities', [
            'id' => $activity->id,
            'subject' => 'Follow-up call',
            'type' => 'call',
            'status' => 'pending',
        ]);
    }

    public function test_update_activity(): void
    {
        $activity = CrmActivity::factory()->create([
            'tenant_id' => $this->tenant->id,
            'subject' => 'Old Subject',
            'status' => 'pending',
        ]);

        $updated = $this->service->update($activity, ['subject' => 'New Subject']);

        $this->assertEquals('New Subject', $updated->subject);
    }

    public function test_complete_activity(): void
    {
        $activity = CrmActivity::factory()->create([
            'tenant_id' => $this->tenant->id,
            'status' => 'pending',
        ]);

        $completed = $this->service->complete($activity);

        $this->assertEquals('completed', $completed->status);
        $this->assertNotNull($completed->completed_at);
    }

    public function test_cancel_activity(): void
    {
        $activity = CrmActivity::factory()->create([
            'tenant_id' => $this->tenant->id,
            'status' => 'pending',
        ]);

        $cancelled = $this->service->cancel($activity);

        $this->assertEquals('cancelled', $cancelled->status);
    }

    public function test_paginate_with_filters(): void
    {
        CrmActivity::factory()->create([
            'tenant_id' => $this->tenant->id,
            'subject' => 'Alpha Task',
            'type' => 'call',
            'status' => 'pending',
        ]);
        CrmActivity::factory()->create([
            'tenant_id' => $this->tenant->id,
            'subject' => 'Beta Task',
            'type' => 'meeting',
            'status' => 'pending',
        ]);

        $results = $this->service->paginate(['search' => 'Alpha']);
        $this->assertCount(1, $results);
        $this->assertEquals('Alpha Task', $results->first()->subject);

        $results = $this->service->paginate(['type' => 'meeting']);
        $this->assertCount(1, $results);
        $this->assertEquals('Beta Task', $results->first()->subject);
    }

    public function test_get_overdue(): void
    {
        CrmActivity::factory()->create([
            'tenant_id' => $this->tenant->id,
            'due_date' => now()->subDays(2),
            'status' => 'pending',
        ]);
        CrmActivity::factory()->completed()->create([
            'tenant_id' => $this->tenant->id,
            'due_date' => now()->subDays(1),
        ]);

        $overdue = $this->service->getOverdue();

        $this->assertCount(1, $overdue);
    }
}
