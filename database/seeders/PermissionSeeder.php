<?php

namespace Database\Seeders;

use App\Models\Permission;
use Illuminate\Database\Seeder;

class PermissionSeeder extends Seeder
{
    /**
     * All system permissions, organized by module and action.
     *
     * @var array<int, array{module: string, action: string, name: string, slug: string}>
     */
    protected array $permissions = [
        // Users module
        ['module' => 'users', 'action' => 'view',   'name' => 'View Users',   'slug' => 'users.view'],
        ['module' => 'users', 'action' => 'create', 'name' => 'Create Users', 'slug' => 'users.create'],
        ['module' => 'users', 'action' => 'edit',   'name' => 'Edit Users',   'slug' => 'users.edit'],
        ['module' => 'users', 'action' => 'delete', 'name' => 'Delete Users', 'slug' => 'users.delete'],

        // Roles module
        ['module' => 'roles', 'action' => 'view',               'name' => 'View Roles',              'slug' => 'roles.view'],
        ['module' => 'roles', 'action' => 'create',             'name' => 'Create Roles',            'slug' => 'roles.create'],
        ['module' => 'roles', 'action' => 'edit',               'name' => 'Edit Roles',              'slug' => 'roles.edit'],
        ['module' => 'roles', 'action' => 'delete',             'name' => 'Delete Roles',            'slug' => 'roles.delete'],
        ['module' => 'roles', 'action' => 'manage-permissions', 'name' => 'Manage Role Permissions', 'slug' => 'roles.manage-permissions'],

        // Dashboard
        ['module' => 'dashboard', 'action' => 'view', 'name' => 'View Dashboard', 'slug' => 'dashboard.view'],

        // Categories module
        ['module' => 'categories', 'action' => 'view',   'name' => 'View Categories',   'slug' => 'categories.view'],
        ['module' => 'categories', 'action' => 'create', 'name' => 'Create Categories', 'slug' => 'categories.create'],
        ['module' => 'categories', 'action' => 'edit',   'name' => 'Edit Categories',   'slug' => 'categories.edit'],
        ['module' => 'categories', 'action' => 'delete', 'name' => 'Delete Categories', 'slug' => 'categories.delete'],

        // Brands module
        ['module' => 'brands', 'action' => 'view',   'name' => 'View Brands',   'slug' => 'brands.view'],
        ['module' => 'brands', 'action' => 'create', 'name' => 'Create Brands', 'slug' => 'brands.create'],
        ['module' => 'brands', 'action' => 'edit',   'name' => 'Edit Brands',   'slug' => 'brands.edit'],
        ['module' => 'brands', 'action' => 'delete', 'name' => 'Delete Brands', 'slug' => 'brands.delete'],

        // Units module
        ['module' => 'units', 'action' => 'view',   'name' => 'View Units',   'slug' => 'units.view'],
        ['module' => 'units', 'action' => 'create', 'name' => 'Create Units', 'slug' => 'units.create'],
        ['module' => 'units', 'action' => 'edit',   'name' => 'Edit Units',   'slug' => 'units.edit'],
        ['module' => 'units', 'action' => 'delete', 'name' => 'Delete Units', 'slug' => 'units.delete'],

        // Products module
        ['module' => 'products', 'action' => 'view',   'name' => 'View Products',   'slug' => 'products.view'],
        ['module' => 'products', 'action' => 'create', 'name' => 'Create Products', 'slug' => 'products.create'],
        ['module' => 'products', 'action' => 'edit',   'name' => 'Edit Products',   'slug' => 'products.edit'],
        ['module' => 'products', 'action' => 'delete', 'name' => 'Delete Products', 'slug' => 'products.delete'],

        // Warehouses module
        ['module' => 'warehouses', 'action' => 'view',   'name' => 'View Warehouses',   'slug' => 'warehouses.view'],
        ['module' => 'warehouses', 'action' => 'create', 'name' => 'Create Warehouses', 'slug' => 'warehouses.create'],
        ['module' => 'warehouses', 'action' => 'edit',   'name' => 'Edit Warehouses',   'slug' => 'warehouses.edit'],
        ['module' => 'warehouses', 'action' => 'delete', 'name' => 'Delete Warehouses', 'slug' => 'warehouses.delete'],

        // Stock module
        ['module' => 'stock', 'action' => 'view',                'name' => 'View Stock',              'slug' => 'stock.view'],
        ['module' => 'stock', 'action' => 'adjust',              'name' => 'Adjust Stock',             'slug' => 'stock.adjust'],
        ['module' => 'stock', 'action' => 'transfer',            'name' => 'Transfer Stock',           'slug' => 'stock.transfer'],
        ['module' => 'stock', 'action' => 'approve-adjustments', 'name' => 'Approve Stock Adjustments', 'slug' => 'stock.approve-adjustments'],

        // BOM module
        ['module' => 'bom', 'action' => 'view',   'name' => 'View BOMs',   'slug' => 'bom.view'],
        ['module' => 'bom', 'action' => 'create', 'name' => 'Create BOMs', 'slug' => 'bom.create'],
        ['module' => 'bom', 'action' => 'edit',   'name' => 'Edit BOMs',   'slug' => 'bom.edit'],
        ['module' => 'bom', 'action' => 'delete', 'name' => 'Delete BOMs', 'slug' => 'bom.delete'],

        // Production module
        ['module' => 'production', 'action' => 'view',    'name' => 'View Production Orders',    'slug' => 'production.view'],
        ['module' => 'production', 'action' => 'create',  'name' => 'Create Production Orders',  'slug' => 'production.create'],
        ['module' => 'production', 'action' => 'manage',  'name' => 'Manage Production Orders',  'slug' => 'production.manage'],
        ['module' => 'production', 'action' => 'approve', 'name' => 'Approve Production Orders', 'slug' => 'production.approve'],
    ];

    public function run(): void
    {
        foreach ($this->permissions as $permission) {
            Permission::query()->updateOrCreate(
                ['slug' => $permission['slug']],
                $permission
            );
        }
    }
}
