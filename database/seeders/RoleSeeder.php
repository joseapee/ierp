<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use App\Models\Tenant;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        $tenants = Tenant::all();
        $allPermissions = Permission::query()->pluck('id', 'slug');

        $roleConfigs = $this->tenantRoles();

        foreach ($tenants as $tenant) {

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
    }

    public static function tenantRoles(): array
    {
        $allPermissions = Permission::query()->pluck('id', 'slug');

        return [
            [
                'slug' => 'owner',
                'name' => 'Owner',
                'description' => 'Tenant owner with full access to all features',
                'permissions' => $allPermissions->keys()->all(), // all permissions
            ],
            [
                'slug' => 'general-manager',
                'name' => 'General Manager',
                'description' => 'Full access to all tenant features',
                'permissions' => $allPermissions->keys()->all(), // all permissions
            ],
            [
                'slug' => 'accountant',
                'name' => 'Accountant',
                'description' => 'Handles accounting, finance, and reporting',
                'permissions' => [
                    'accounts.view', 'accounts.create', 'accounts.edit',
                    'journal.view', 'journal.create',
                    'reports.view',
                    'fiscal-years.view', 'fiscal-years.manage',
                    'purchase-orders.view', 'purchase-orders.manage',
                    'sales-orders.view', 'sales-orders.manage',
                ],
            ],
            [
                'slug' => 'store-keeper',
                'name' => 'Store Keeper',
                'description' => 'Manages inventory and stock',
                'permissions' => [
                    'warehouses.view', 'warehouses.create', 'warehouses.edit',
                    'stock.view', 'stock.adjust', 'stock.transfer', 'stock.approve-adjustments',
                    'products.view', 'products.create', 'products.edit',
                    'categories.view', 'categories.create', 'categories.edit',
                    'units.view',
                    'pos.access', 'pos.view', 'pos.create', 'pos.manage-sessions',
                ],
            ],
            [
                'slug' => 'sales-rep',
                'name' => 'Sales Rep',
                'description' => 'Handles sales and POS operations',
                'permissions' => [
                    'customers.view', 'customers.create',
                    'sales-orders.view', 'sales-orders.create',
                    'pos.access', 'pos.view', 'pos.create',
                ],
            ],
            [
                'slug' => 'hr-manager',
                'name' => 'HR Manager',
                'description' => 'Manages users and roles',
                'permissions' => [
                    'users.view', 'users.create', 'users.edit', 'users.delete',
                    'roles.view', 'roles.create', 'roles.edit', 'roles.delete', 'roles.manage-permissions',
                ],
            ],
            [
                'slug' => 'customer-reps',
                'name' => 'Customer Reps',
                'description' => 'Handles customer support and CRM',
                'permissions' => [
                    'customers.view', 'customers.create',
                    'crm-contacts.view', 'crm-contacts.create',
                    'leads.view', 'leads.create',
                    'crm-activities.view', 'crm-activities.create',
                    'crm-communications.view', 'crm-communications.create',
                ],
            ],
        ];
    }
}
