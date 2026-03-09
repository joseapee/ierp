<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        // Tenant Admin — has all permissions
        $adminRole = Role::query()->updateOrCreate(
            ['slug' => 'tenant-admin', 'tenant_id' => null],
            [
                'name' => 'Tenant Admin',
                'description' => 'Full access to all tenant features',
                'is_system' => true,
            ]
        );

        $adminRole->permissions()->sync(
            Permission::query()->pluck('id')->all()
        );

        // Tenant User — view-only permissions
        $userRole = Role::query()->updateOrCreate(
            ['slug' => 'tenant-user', 'tenant_id' => null],
            [
                'name' => 'Tenant User',
                'description' => 'View-only access to tenant features',
                'is_system' => true,
            ]
        );

        $viewPermissions = Permission::query()
            ->where('action', 'view')
            ->pluck('id')
            ->all();

        $userRole->permissions()->sync($viewPermissions);
    }
}
