<?php

declare(strict_types=1);

namespace Tests\Feature\UserManagement;

use App\Livewire\UserManagement\UserList;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class UserListTest extends TestCase
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

    public function test_requires_authentication(): void
    {
        $this->get(route('users.index'))->assertRedirect(route('login'));
    }

    public function test_renders_user_list(): void
    {
        $this->actingAs($this->admin);

        Livewire::test(UserList::class)
            ->assertStatus(200)
            ->assertSee('User Management');
    }

    public function test_search_filters_users(): void
    {
        $this->actingAs($this->admin);

        User::factory()->create(['name' => 'Findable User', 'tenant_id' => $this->tenant->id]);
        User::factory()->create(['name' => 'Hidden User', 'tenant_id' => $this->tenant->id]);

        Livewire::test(UserList::class)
            ->set('search', 'Findable')
            ->assertSee('Findable User')
            ->assertDontSee('Hidden User');
    }

    public function test_delete_user_dispatches_toast(): void
    {
        $this->actingAs($this->admin);

        $user = User::factory()->create(['tenant_id' => $this->tenant->id]);

        Livewire::test(UserList::class)
            ->call('deleteUser', $user->id)
            ->assertDispatched('toast');

        $this->assertDatabaseMissing('users', ['id' => $user->id]);
    }
}
