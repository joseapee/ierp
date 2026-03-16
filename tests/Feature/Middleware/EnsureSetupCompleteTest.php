<?php

declare(strict_types=1);

namespace Tests\Feature\Middleware;

use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EnsureSetupCompleteTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_without_tenant_is_redirected_to_setup(): void
    {
        $user = User::factory()->create(['tenant_id' => null]);

        $this->actingAs($user)
            ->get(route('dashboard'))
            ->assertRedirect(route('setup'));
    }

    public function test_user_with_incomplete_setup_is_redirected_to_setup(): void
    {
        $tenant = Tenant::factory()->create(['setup_completed_at' => null]);
        $user = User::factory()->create(['tenant_id' => $tenant->id]);

        $this->actingAs($user)
            ->get(route('dashboard'))
            ->assertRedirect(route('setup'));
    }

    public function test_user_with_completed_setup_can_access_dashboard(): void
    {
        $tenant = Tenant::factory()->onboardingComplete()->create();
        $user = User::factory()->create(['tenant_id' => $tenant->id]);

        $this->actingAs($user)
            ->get(route('dashboard'))
            ->assertOk();
    }

    public function test_super_admin_bypasses_setup_check(): void
    {
        $admin = User::factory()->create([
            'is_super_admin' => true,
            'tenant_id' => null,
        ]);

        $this->actingAs($admin)
            ->get(route('dashboard'))
            ->assertOk();
    }

    public function test_setup_route_is_accessible_without_setup(): void
    {
        $user = User::factory()->create(['tenant_id' => null]);

        $this->actingAs($user)
            ->get(route('setup'))
            ->assertOk();
    }

    public function test_billing_route_is_accessible_without_setup(): void
    {
        $user = User::factory()->create(['tenant_id' => null]);

        $this->actingAs($user)
            ->get(route('billing.index'))
            ->assertOk();
    }
}
