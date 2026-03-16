<?php

declare(strict_types=1);

namespace Tests\Feature\Middleware;

use App\Models\Plan;
use App\Models\PlanFeature;
use App\Models\Subscription;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CheckFeatureTest extends TestCase
{
    use RefreshDatabase;

    private function createTenantWithFeatures(array $features): array
    {
        $plan = Plan::factory()->create();
        foreach ($features as $key => $value) {
            PlanFeature::factory()->for($plan)->create([
                'feature_key' => $key,
                'feature_value' => $value,
            ]);
        }
        $tenant = Tenant::factory()->setupComplete()->create(['plan_id' => $plan->id]);
        Subscription::factory()->active()->for($tenant)->for($plan)->create();
        $user = User::factory()->create(['tenant_id' => $tenant->id]);

        return [$tenant, $user, $plan];
    }

    public function test_enabled_feature_passes(): void
    {
        [$tenant, $user] = $this->createTenantWithFeatures([
            'manufacturing_enabled' => 'true',
        ]);

        $this->actingAs($user)
            ->get(route('manufacturing.boms.index'))
            ->assertStatus(403); // 403 from permission check — proves feature middleware passed
    }

    public function test_disabled_feature_returns_403(): void
    {
        [$tenant, $user] = $this->createTenantWithFeatures([
            'manufacturing_enabled' => 'false',
        ]);

        $response = $this->actingAs($user)
            ->get(route('manufacturing.boms.index'));

        $response->assertStatus(403);
        $this->assertStringContainsString('not available', $response->content());
    }

    public function test_missing_feature_returns_403(): void
    {
        [$tenant, $user] = $this->createTenantWithFeatures([
            'crm_enabled' => 'true',
            // manufacturing_enabled not set
        ]);

        $response = $this->actingAs($user)
            ->get(route('manufacturing.boms.index'));

        $response->assertStatus(403);
    }

    public function test_super_admin_bypasses_feature_check(): void
    {
        $admin = User::factory()->create([
            'is_super_admin' => true,
            'tenant_id' => null,
        ]);

        $this->actingAs($admin)
            ->get(route('manufacturing.boms.index'))
            ->assertOk();
    }
}
