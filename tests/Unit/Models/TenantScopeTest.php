<?php

declare(strict_types=1);

namespace Tests\Unit\Models;

use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TenantScopeTest extends TestCase
{
    use RefreshDatabase;

    public function test_scope_filters_by_current_tenant(): void
    {
        $tenant1 = Tenant::factory()->create();
        $tenant2 = Tenant::factory()->create();

        User::factory()->create(['tenant_id' => $tenant1->id]);
        User::factory()->create(['tenant_id' => $tenant2->id]);

        app()->instance('current.tenant', $tenant1);

        $users = User::all();

        $this->assertCount(1, $users);
        $this->assertEquals($tenant1->id, $users->first()->tenant_id);
    }

    public function test_scope_does_not_filter_when_no_tenant_bound(): void
    {
        $tenant1 = Tenant::factory()->create();
        $tenant2 = Tenant::factory()->create();

        User::factory()->create(['tenant_id' => $tenant1->id]);
        User::factory()->create(['tenant_id' => $tenant2->id]);

        // Do NOT bind a current tenant
        $users = User::all();

        $this->assertCount(2, $users);
    }

    public function test_auto_sets_tenant_id_on_creation(): void
    {
        $tenant = Tenant::factory()->create();
        app()->instance('current.tenant', $tenant);

        $user = User::factory()->create(['tenant_id' => null]);

        $this->assertEquals($tenant->id, $user->fresh()->tenant_id);
    }
}
