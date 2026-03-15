<?php

declare(strict_types=1);

namespace Tests\Feature\Admin;

use App\Models\Plan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class PlanManagerTest extends TestCase
{
    use RefreshDatabase;

    protected function createSuperAdmin(): User
    {
        return User::factory()->create([
            'is_super_admin' => true,
            'tenant_id' => null,
        ]);
    }

    public function test_non_super_admin_cannot_access(): void
    {
        $user = User::factory()->create(['tenant_id' => null]);

        $this->actingAs($user)
            ->get(route('admin.plans.index'))
            ->assertForbidden();
    }

    public function test_super_admin_can_view_plan_list(): void
    {
        $admin = $this->createSuperAdmin();
        Plan::factory()->create(['name' => 'Starter Plan']);

        $this->actingAs($admin)
            ->get(route('admin.plans.index'))
            ->assertOk()
            ->assertSee('Starter Plan');
    }

    public function test_super_admin_can_create_plan(): void
    {
        $admin = $this->createSuperAdmin();

        Livewire::actingAs($admin)
            ->test(\App\Livewire\Admin\PlanManager::class)
            ->call('openCreate')
            ->set('name', 'New Plan')
            ->set('slug', 'new-plan')
            ->set('monthlyPrice', '2500')
            ->set('annualPrice', '25000')
            ->set('trialDays', 14)
            ->call('save');

        $this->assertDatabaseHas('plans', [
            'name' => 'New Plan',
            'slug' => 'new-plan',
        ]);
    }

    public function test_super_admin_can_edit_plan(): void
    {
        $admin = $this->createSuperAdmin();
        $plan = Plan::factory()->create(['name' => 'Old Name']);

        Livewire::actingAs($admin)
            ->test(\App\Livewire\Admin\PlanManager::class)
            ->call('openEdit', $plan->id)
            ->set('name', 'Updated Name')
            ->call('save');

        $this->assertDatabaseHas('plans', [
            'id' => $plan->id,
            'name' => 'Updated Name',
        ]);
    }
}
