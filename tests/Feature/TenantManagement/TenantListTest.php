<?php

declare(strict_types=1);

namespace Tests\Feature\TenantManagement;

use App\Livewire\TenantManagement\TenantList;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class TenantListTest extends TestCase
{
    use RefreshDatabase;

    public function test_super_admin_can_access_tenant_list(): void
    {
        $admin = User::factory()->create(['is_super_admin' => true, 'tenant_id' => null]);

        $this->actingAs($admin);

        Livewire::test(TenantList::class)
            ->assertStatus(200)
            ->assertSee('Tenant Management');
    }

    public function test_non_super_admin_is_forbidden(): void
    {
        $tenant = Tenant::factory()->create();
        app()->instance('current.tenant', $tenant);

        $user = User::factory()->create([
            'is_super_admin' => false,
            'tenant_id' => $tenant->id,
        ]);

        $this->actingAs($user)
            ->get(route('tenants.index'))
            ->assertForbidden();
    }

    public function test_toggle_status_suspends_active_tenant(): void
    {
        $admin = User::factory()->create(['is_super_admin' => true, 'tenant_id' => null]);
        $tenant = Tenant::factory()->create(['status' => 'active']);

        $this->actingAs($admin);

        Livewire::test(TenantList::class)
            ->call('toggleStatus', $tenant->id)
            ->assertDispatched('toast');

        $this->assertEquals('suspended', $tenant->fresh()->status);
    }

    public function test_toggle_status_activates_suspended_tenant(): void
    {
        $admin = User::factory()->create(['is_super_admin' => true, 'tenant_id' => null]);
        $tenant = Tenant::factory()->create(['status' => 'suspended']);

        $this->actingAs($admin);

        Livewire::test(TenantList::class)
            ->call('toggleStatus', $tenant->id)
            ->assertDispatched('toast');

        $this->assertEquals('active', $tenant->fresh()->status);
    }
}
