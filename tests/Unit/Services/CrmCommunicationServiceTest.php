<?php

declare(strict_types=1);

namespace Tests\Unit\Services;

use App\Models\CrmCommunication;
use App\Models\Customer;
use App\Models\Lead;
use App\Models\Tenant;
use App\Models\User;
use App\Services\CrmCommunicationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CrmCommunicationServiceTest extends TestCase
{
    use RefreshDatabase;

    private CrmCommunicationService $service;

    private Tenant $tenant;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->tenant = Tenant::factory()->create();
        app()->instance('current.tenant', $this->tenant);
        $this->user = User::factory()->create(['tenant_id' => $this->tenant->id]);
        $this->service = new CrmCommunicationService;
    }

    public function test_create_communication(): void
    {
        $customer = Customer::factory()->create(['tenant_id' => $this->tenant->id]);

        $comm = $this->service->create([
            'tenant_id' => $this->tenant->id,
            'customer_id' => $customer->id,
            'type' => 'email',
            'subject' => 'Welcome email',
            'message' => 'Welcome to our services.',
            'created_by' => $this->user->id,
        ]);

        $this->assertDatabaseHas('crm_communications', [
            'id' => $comm->id,
            'subject' => 'Welcome email',
            'type' => 'email',
        ]);
    }

    public function test_get_customer_timeline(): void
    {
        $customer = Customer::factory()->create(['tenant_id' => $this->tenant->id]);
        $otherCustomer = Customer::factory()->create(['tenant_id' => $this->tenant->id]);

        CrmCommunication::factory()->create([
            'tenant_id' => $this->tenant->id,
            'customer_id' => $customer->id,
            'subject' => 'Customer Comm',
        ]);
        CrmCommunication::factory()->create([
            'tenant_id' => $this->tenant->id,
            'customer_id' => $otherCustomer->id,
            'subject' => 'Other Comm',
        ]);

        $timeline = $this->service->getCustomerTimeline($customer->id);

        $this->assertCount(1, $timeline);
        $this->assertEquals('Customer Comm', $timeline->first()->subject);
    }

    public function test_get_lead_timeline(): void
    {
        $lead = Lead::factory()->create(['tenant_id' => $this->tenant->id, 'status' => 'new']);

        CrmCommunication::factory()->create([
            'tenant_id' => $this->tenant->id,
            'lead_id' => $lead->id,
            'customer_id' => null,
            'subject' => 'Lead Follow-up',
        ]);

        $timeline = $this->service->getLeadTimeline($lead->id);

        $this->assertCount(1, $timeline);
        $this->assertEquals('Lead Follow-up', $timeline->first()->subject);
    }

    public function test_paginate_with_filters(): void
    {
        $customer = Customer::factory()->create(['tenant_id' => $this->tenant->id]);

        CrmCommunication::factory()->create([
            'tenant_id' => $this->tenant->id,
            'customer_id' => $customer->id,
            'type' => 'email',
            'subject' => 'Email about proposal',
        ]);
        CrmCommunication::factory()->create([
            'tenant_id' => $this->tenant->id,
            'customer_id' => $customer->id,
            'type' => 'phone',
            'subject' => 'Phone discussion',
        ]);

        $results = $this->service->paginate(['search' => 'proposal']);
        $this->assertCount(1, $results);

        $results = $this->service->paginate(['type' => 'phone']);
        $this->assertCount(1, $results);
    }
}
