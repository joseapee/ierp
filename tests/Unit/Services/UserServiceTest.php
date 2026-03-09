<?php

declare(strict_types=1);

namespace Tests\Unit\Services;

use App\Models\Role;
use App\Models\User;
use App\Services\UserService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserServiceTest extends TestCase
{
    use RefreshDatabase;

    private UserService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new UserService;
    }

    public function test_paginate_returns_paginated_results(): void
    {
        User::factory()->count(20)->create();

        $result = $this->service->paginate([], 10);

        $this->assertCount(10, $result->items());
        $this->assertEquals(20, $result->total());
    }

    public function test_paginate_filters_by_search(): void
    {
        User::factory()->create(['name' => 'Alice Smith', 'email' => 'alice@example.com']);
        User::factory()->create(['name' => 'Bob Jones', 'email' => 'bob@example.com']);

        $result = $this->service->paginate(['search' => 'alice']);

        $this->assertCount(1, $result->items());
        $this->assertEquals('Alice Smith', $result->items()[0]->name);
    }

    public function test_create_user_with_roles(): void
    {
        $role = Role::factory()->create();

        $user = $this->service->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password123',
            'role_ids' => [$role->id],
        ]);

        $this->assertDatabaseHas('users', ['email' => 'test@example.com']);
        $this->assertTrue($user->roles->contains($role));
    }

    public function test_update_user_syncs_roles(): void
    {
        $user = User::factory()->create();
        $oldRole = Role::factory()->create();
        $newRole = Role::factory()->create();
        $user->roles()->attach($oldRole);

        $this->service->update($user, [
            'name' => 'Updated',
            'email' => $user->email,
            'role_ids' => [$newRole->id],
        ]);

        $user->refresh();
        $this->assertEquals('Updated', $user->name);
        $this->assertFalse($user->roles->contains($oldRole));
        $this->assertTrue($user->roles->contains($newRole));
    }

    public function test_delete_user(): void
    {
        $user = User::factory()->create();

        $result = $this->service->delete($user);

        $this->assertTrue($result);
        $this->assertDatabaseMissing('users', ['id' => $user->id]);
    }
}
