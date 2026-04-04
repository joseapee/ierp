<?php

declare(strict_types=1);

namespace Tests\Feature\UserManagement;

use App\Livewire\UserManagement\UserFormModal;
use App\Models\Role;
use App\Models\Tenant;
use App\Models\User;
use App\Notifications\UserCredentialsNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
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
            ->assertNotDispatched('userSaved')
            ->assertSet('showModal', false)
            ->assertSet('showCredentialsModal', true)
            ->assertSet('createdCredentials.email', 'newuser@example.com')
            ->assertSet('createdCredentials.password', 'password123');

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

    public function test_generate_password_fills_both_fields(): void
    {
        $this->actingAs($this->admin);

        $component = Livewire::test(UserFormModal::class)
            ->call('open')
            ->call('generatePassword');

        $password = $component->get('password');
        $this->assertNotEmpty($password);
        $this->assertEquals(16, strlen($password));
        $component->assertSet('password_confirmation', $password);
    }

    public function test_email_credentials_sends_notification(): void
    {
        Notification::fake();
        $this->actingAs($this->admin);

        $role = Role::factory()->create();

        Livewire::test(UserFormModal::class)
            ->call('open')
            ->set('name', 'Email Test User')
            ->set('email', 'emailtest@example.com')
            ->set('password', 'Secret1234!')
            ->set('password_confirmation', 'Secret1234!')
            ->set('role_ids', [$role->id])
            ->call('save')
            ->call('emailCredentials')
            ->assertDispatched('toast');

        $user = User::where('email', 'emailtest@example.com')->first();
        Notification::assertSentTo($user, UserCredentialsNotification::class);
    }

    public function test_close_credentials_modal_clears_state(): void
    {
        $this->actingAs($this->admin);

        $role = Role::factory()->create();

        Livewire::test(UserFormModal::class)
            ->call('open')
            ->set('name', 'Close Test')
            ->set('email', 'closetest@example.com')
            ->set('password', 'password123')
            ->set('password_confirmation', 'password123')
            ->set('role_ids', [$role->id])
            ->call('save')
            ->assertSet('showCredentialsModal', true)
            ->call('closeCredentialsModal')
            ->assertSet('showCredentialsModal', false)
            ->assertSet('createdCredentials', null)
            ->assertDispatched('userSaved');
    }
}
