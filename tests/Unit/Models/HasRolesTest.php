<?php

declare(strict_types=1);

namespace Tests\Unit\Models;

use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HasRolesTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_be_assigned_a_role(): void
    {
        $user = User::factory()->create();
        $role = Role::factory()->create();

        $user->assignRole($role);

        $this->assertTrue($user->hasRole($role->slug));
    }

    public function test_user_can_have_role_removed(): void
    {
        $user = User::factory()->create();
        $role = Role::factory()->create();

        $user->assignRole($role);
        $user->removeRole($role);

        $this->assertFalse($user->hasRole($role->slug));
    }

    public function test_has_permission_returns_true_when_role_has_permission(): void
    {
        $user = User::factory()->create();
        $role = Role::factory()->create();
        $permission = Permission::factory()->create(['slug' => 'users.view']);

        $role->permissions()->attach($permission);
        $user->assignRole($role);

        $this->assertTrue($user->hasPermission('users.view'));
    }

    public function test_has_permission_returns_false_without_permission(): void
    {
        $user = User::factory()->create();
        $role = Role::factory()->create();
        $user->assignRole($role);

        $this->assertFalse($user->hasPermission('users.view'));
    }

    public function test_super_admin_bypasses_permission_check(): void
    {
        $user = User::factory()->create(['is_super_admin' => true]);

        $this->assertTrue($user->hasPermission('anything.here'));
    }

    public function test_get_all_permissions_returns_unique_slugs(): void
    {
        $user = User::factory()->create();
        $role1 = Role::factory()->create();
        $role2 = Role::factory()->create();
        $perm = Permission::factory()->create(['slug' => 'users.view']);

        $role1->permissions()->attach($perm);
        $role2->permissions()->attach($perm);
        $user->roles()->attach([$role1->id, $role2->id]);

        $permissions = $user->getAllPermissions();

        $this->assertCount(1, $permissions);
        $this->assertContains('users.view', $permissions);
    }
}
