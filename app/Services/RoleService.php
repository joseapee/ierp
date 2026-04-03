<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Permission;
use App\Models\Role;
use Database\Seeders\RoleSeeder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class RoleService
{
    /**
     * Get all roles for the current tenant scope.
     */
    public function all(): Collection
    {
        $tenantId = app()->bound('current.tenant') ? app('current.tenant')->id : null;

        return Role::query()
            ->where('tenant_id', $tenantId)
            ->withCount('users')
            ->with('permissions')
            ->orderBy('name')
            ->get();
    }

    /**
     * Seed system roles for a tenant if not present.
     */
    public function seedSystemRolesForTenant($tenant): void
    {
        $allPermissions = Permission::query()->pluck('id', 'slug');
        $roleConfigs = RoleSeeder::tenantRoles();

        foreach ($roleConfigs as $config) {

            $role = Role::query()->where([
                'slug' => $config['slug'],
                'tenant_id' => $tenant->id,
            ])->first();

            if (! $role) {
                $role = Role::create([
                    'slug' => $config['slug'],
                    'tenant_id' => $tenant->id,
                    'name' => $config['name'],
                    'description' => $config['description'],
                    'is_system' => true,
                ]);
            } else {
                $role->update([
                    'name' => $config['name'],
                    'description' => $config['description'],
                    'is_system' => true,
                ]);
            }

            $permissionIds = array_map(fn ($slug) => $allPermissions[$slug] ?? null, $config['permissions']);
            $role->permissions()->sync(array_filter($permissionIds));
        }
    }

    /**
     * Create a new role and attach permissions.
     *
     * @param  array{name: string, slug: string, description?: string, permission_ids?: int[]}  $data
     */
    public function create(array $data): Role
    {
        $tenantId = $data['tenant_id'] ?? (app()->bound('current.tenant') ? app('current.tenant')->id : null);

        return DB::transaction(function () use ($data, $tenantId): Role {
            $role = Role::create([
                'name' => $data['name'],
                'slug' => $data['slug'],
                'description' => $data['description'] ?? null,
                'tenant_id' => $tenantId,
            ]);

            if (! empty($data['permission_ids'])) {
                $role->permissions()->sync($data['permission_ids']);
            }

            return $role->load('permissions');
        });
    }

    /**
     * Update an existing role and sync permissions.
     *
     * @param  array{name?: string, slug?: string, description?: string, permission_ids?: int[]}  $data
     */
    public function update(Role $role, array $data): Role
    {
        $tenantId = $data['tenant_id'] ?? (app()->bound('current.tenant') ? app('current.tenant')->id : null);

        return DB::transaction(function () use ($role, $data, $tenantId): Role {
            $role->update(array_merge(
                collect($data)->only(['name', 'slug', 'description'])->toArray(),
                ['tenant_id' => $tenantId]
            ));

            if (array_key_exists('permission_ids', $data)) {
                $role->permissions()->sync($data['permission_ids'] ?? []);
            }

            return $role->load('permissions');
        });
    }

    /**
     * Delete a role (system roles cannot be deleted).
     */
    public function delete(Role $role): bool
    {
        if ($role->is_system) {
            return false;
        }

        return (bool) $role->delete();
    }

    /**
     * Sync permissions for a role.
     *
     * @param  int[]  $permissionIds
     */
    public function syncPermissions(Role $role, array $permissionIds): void
    {
        $role->permissions()->sync($permissionIds);
    }
}
