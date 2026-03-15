<?php

declare(strict_types=1);

namespace Tests\Unit\Services;

use App\Models\Lead;
use App\Models\Tenant;
use App\Models\User;
use App\Services\LeadService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LeadServiceTest extends TestCase
{
    use RefreshDatabase;

    private LeadService $service;

    private Tenant $tenant;

    private User $admin;

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

        $this->service = new LeadService;
    }

    public function test_create_lead(): void
    {
        $lead = $this->service->create([
            'tenant_id' => $this->tenant->id,
            'lead_name' => 'John Doe',
            'company_name' => 'Acme Inc',
            'email' => 'john@acme.com',
            'source' => 'website',
            'status' => 'new',
            'estimated_value' => 50000,
        ]);

        $this->assertDatabaseHas('leads', [
            'lead_name' => 'John Doe',
            'status' => 'new',
            'source' => 'website',
        ]);
        $this->assertEquals('new', $lead->status);
    }

    public function test_update_lead(): void
    {
        $lead = Lead::factory()->create([
            'tenant_id' => $this->tenant->id,
            'lead_name' => 'Original Name',
        ]);

        $updated = $this->service->update($lead, ['lead_name' => 'Updated Name']);

        $this->assertEquals('Updated Name', $updated->lead_name);
    }

    public function test_convert_lead_creates_customer_and_contact(): void
    {
        $lead = Lead::factory()->create([
            'tenant_id' => $this->tenant->id,
            'lead_name' => 'Jane Smith',
            'company_name' => 'Beta Corp',
            'email' => 'jane@beta.com',
            'phone' => '555-1234',
            'status' => 'qualified',
        ]);

        $customer = $this->service->convert($lead, true);

        $this->assertEquals('converted', $lead->fresh()->status);
        $this->assertNotNull($lead->fresh()->converted_at);
        $this->assertEquals($customer->id, $lead->fresh()->converted_customer_id);

        $this->assertDatabaseHas('customers', [
            'name' => 'Beta Corp',
            'email' => 'jane@beta.com',
        ]);

        $this->assertDatabaseHas('crm_contacts', [
            'first_name' => 'Jane',
            'last_name' => 'Smith',
            'customer_id' => $customer->id,
            'is_primary' => true,
        ]);
    }

    public function test_convert_lead_without_contact(): void
    {
        $lead = Lead::factory()->create([
            'tenant_id' => $this->tenant->id,
            'lead_name' => 'Solo Lead',
            'company_name' => 'Solo Inc',
            'status' => 'qualified',
        ]);

        $customer = $this->service->convert($lead, false);

        $this->assertDatabaseHas('customers', ['name' => 'Solo Inc']);
        $this->assertDatabaseMissing('crm_contacts', ['customer_id' => $customer->id]);
    }

    public function test_convert_already_converted_throws_exception(): void
    {
        $lead = Lead::factory()->converted()->create([
            'tenant_id' => $this->tenant->id,
        ]);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('already converted');
        $this->service->convert($lead);
    }

    public function test_valid_status_transition(): void
    {
        $lead = Lead::factory()->create([
            'tenant_id' => $this->tenant->id,
            'status' => 'new',
        ]);

        $updated = $this->service->updateStatus($lead, 'contacted');
        $this->assertEquals('contacted', $updated->status);

        $updated = $this->service->updateStatus($updated->fresh(), 'qualified');
        $this->assertEquals('qualified', $updated->status);
    }

    public function test_invalid_status_transition_throws_exception(): void
    {
        $lead = Lead::factory()->create([
            'tenant_id' => $this->tenant->id,
            'status' => 'new',
        ]);

        $this->service->updateStatus($lead, 'contacted');

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Invalid status transition');
        $this->service->updateStatus($lead->fresh(), 'new');
    }

    public function test_mark_lost(): void
    {
        $lead = Lead::factory()->create([
            'tenant_id' => $this->tenant->id,
            'status' => 'qualified',
            'notes' => null,
        ]);

        $result = $this->service->markLost($lead, 'Budget constraints');

        $this->assertEquals('lost', $result->status);
        $this->assertStringContainsString('Budget constraints', $result->notes);
    }

    public function test_mark_converted_lead_as_lost_throws_exception(): void
    {
        $lead = Lead::factory()->converted()->create([
            'tenant_id' => $this->tenant->id,
        ]);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Cannot mark a converted lead as lost');
        $this->service->markLost($lead);
    }
}
