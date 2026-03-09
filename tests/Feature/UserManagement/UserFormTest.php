<?php

declare(strict_types=1);

namespace Tests\Feature\UserManagement;

use App\Livewire\UserManagement\UserFormModal;
use App\Models\Role;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class UserFormTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    private Tenant $tenant;

    protected function setUp(): void
    {
        parent::setUp();

        $this->tenant = Tenant::factory()->create();
        app()->instance('current.tenant', $this->tenant);

        $this->admin = User::factory()->create([
            'tenant_id' => $this->tenant->id,
            'is_super_admin' => true,
        ]);
    }

    public function test_create_user_with_valid_data(): void
    {
        $this->actingAs($this->admin);

        $role = Role::factory()->create();

        Livewire::test(UserFormModal::class)
            ->call('open')
            ->set('name', 'New User')
            ->set('email', 'newuser@example.com')
            ->set('password', 'password123')
            ->set('password_confirmation', 'password123')
            ->set('role_ids', [$role->id])
            ->call('save')
            ->assertDispatched('userSaved')
            ->assertDispatched('toast');

        $this->assertDatabaseHas('users', ['email' => 'newuser@example.com']);
    }

    public function test_validation_fails_with_missing_name(): void
    {
        $this->actingAs($this->admin);

        Livewire::test(UserFormModal::class)
            ->call('open')
            ->set('email', 'newuser@example.com')
            ->set('password', 'password123')
            ->set('password_confirmation', 'password123')
            ->call('save')
            ->assertHasErrors(['name']);
    }

    public function test_edit_user_loads_existing_data(): void
    {
        $this->actingAs($this->admin);

        $user = User::factory()->create([
            'name' => 'Existing User',
            'email' => 'existing@example.com',
            'tenant_id' => $this->tenant->id,
        ]);

        Livewire::test(UserFormModal::class)
            ->call('open', $user->id)
            ->assertSet('name', 'Existing User')
            ->assertSet('email', 'existing@example.com')
            ->assertSet('userId', $user->id);
    }
}
