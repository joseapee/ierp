<?php

declare(strict_types=1);

namespace Tests\Feature\RoleManagement;

use App\Livewire\RoleManagement\RoleList;
use App\Models\Role;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class RoleListTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $tenant = Tenant::factory()->create();
        app()->instance('current.tenant', $tenant);

        $this->admin = User::factory()->create([
            'tenant_id' => $tenant->id,
            'is_super_admin' => true,
        ]);
    }

    public function test_renders_role_list(): void
    {
        $this->actingAs($this->admin);

        Livewire::test(RoleList::class)
            ->assertStatus(200)
            ->assertSee('Role Management');
    }

    public function test_system_role_cannot_be_deleted(): void
    {
        $this->actingAs($this->admin);

        $role = Role::factory()->create(['is_system' => true]);

        Livewire::test(RoleList::class)
            ->call('deleteRole', $role->id)
            ->assertDispatched('toast');

        $this->assertDatabaseHas('roles', ['id' => $role->id]);
    }

    public function test_custom_role_can_be_deleted(): void
    {
        $this->actingAs($this->admin);

        $role = Role::factory()->create(['is_system' => false]);

        Livewire::test(RoleList::class)
            ->call('deleteRole', $role->id)
            ->assertDispatched('toast');

        $this->assertDatabaseMissing('roles', ['id' => $role->id]);
    }
}
