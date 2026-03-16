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
        // POS module
        ['module' => 'pos', 'action' => 'access', 'name' => 'Access POS Terminal', 'slug' => 'pos.access'],
        ['module' => 'pos', 'action' => 'view',   'name' => 'View POS Transactions', 'slug' => 'pos.view'],
        ['module' => 'pos', 'action' => 'create', 'name' => 'Create POS Transaction', 'slug' => 'pos.create'],
        ['module' => 'pos', 'action' => 'edit',   'name' => 'Edit POS Transaction', 'slug' => 'pos.edit'],
        ['module' => 'pos', 'action' => 'delete', 'name' => 'Delete POS Transaction', 'slug' => 'pos.delete'],
        ['module' => 'pos', 'action' => 'manage-sessions', 'name' => 'Manage POS Sessions', 'slug' => 'pos.manage-sessions'],
        ['module' => 'pos', 'action' => 'close-session', 'name' => 'Close POS Session', 'slug' => 'pos.close-session'],
        ['module' => 'pos', 'action' => 'reprint-receipt', 'name' => 'Reprint POS Receipt', 'slug' => 'pos.reprint-receipt'],
        ['module' => 'pos', 'action' => 'refund', 'name' => 'Refund POS Transaction', 'slug' => 'pos.refund'],
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

        // Accounting — Chart of Accounts
        ['module' => 'accounts', 'action' => 'view',   'name' => 'View Accounts',   'slug' => 'accounts.view'],
        ['module' => 'accounts', 'action' => 'create', 'name' => 'Create Accounts', 'slug' => 'accounts.create'],
        ['module' => 'accounts', 'action' => 'edit',   'name' => 'Edit Accounts',   'slug' => 'accounts.edit'],

        // Accounting — Journal Entries
        ['module' => 'journal', 'action' => 'view',   'name' => 'View Journal Entries',   'slug' => 'journal.view'],
        ['module' => 'journal', 'action' => 'create', 'name' => 'Create Journal Entries', 'slug' => 'journal.create'],

        // Accounting — Reports
        ['module' => 'reports', 'action' => 'view', 'name' => 'View Financial Reports', 'slug' => 'reports.view'],

        // Accounting — Fiscal Years
        ['module' => 'fiscal-years', 'action' => 'view',   'name' => 'View Fiscal Years',   'slug' => 'fiscal-years.view'],
        ['module' => 'fiscal-years', 'action' => 'manage', 'name' => 'Manage Fiscal Years', 'slug' => 'fiscal-years.manage'],

        // Customers module
        ['module' => 'customers', 'action' => 'view',   'name' => 'View Customers',   'slug' => 'customers.view'],
        ['module' => 'customers', 'action' => 'create', 'name' => 'Create Customers', 'slug' => 'customers.create'],
        ['module' => 'customers', 'action' => 'edit',   'name' => 'Edit Customers',   'slug' => 'customers.edit'],
        ['module' => 'customers', 'action' => 'delete', 'name' => 'Delete Customers', 'slug' => 'customers.delete'],

        // Suppliers module
        ['module' => 'suppliers', 'action' => 'view',   'name' => 'View Suppliers',   'slug' => 'suppliers.view'],
        ['module' => 'suppliers', 'action' => 'create', 'name' => 'Create Suppliers', 'slug' => 'suppliers.create'],
        ['module' => 'suppliers', 'action' => 'edit',   'name' => 'Edit Suppliers',   'slug' => 'suppliers.edit'],
        ['module' => 'suppliers', 'action' => 'delete', 'name' => 'Delete Suppliers', 'slug' => 'suppliers.delete'],

        // Purchase Orders module
        ['module' => 'purchase-orders', 'action' => 'view',   'name' => 'View Purchase Orders',   'slug' => 'purchase-orders.view'],
        ['module' => 'purchase-orders', 'action' => 'create', 'name' => 'Create Purchase Orders', 'slug' => 'purchase-orders.create'],
        ['module' => 'purchase-orders', 'action' => 'manage', 'name' => 'Manage Purchase Orders', 'slug' => 'purchase-orders.manage'],

        // Sales Orders module
        ['module' => 'sales-orders', 'action' => 'view',   'name' => 'View Sales Orders',   'slug' => 'sales-orders.view'],
        ['module' => 'sales-orders', 'action' => 'create', 'name' => 'Create Sales Orders', 'slug' => 'sales-orders.create'],
        ['module' => 'sales-orders', 'action' => 'manage', 'name' => 'Manage Sales Orders', 'slug' => 'sales-orders.manage'],

        // CRM — Leads
        ['module' => 'leads', 'action' => 'view',    'name' => 'View Leads',    'slug' => 'leads.view'],
        ['module' => 'leads', 'action' => 'create',  'name' => 'Create Leads',  'slug' => 'leads.create'],
        ['module' => 'leads', 'action' => 'edit',    'name' => 'Edit Leads',    'slug' => 'leads.edit'],
        ['module' => 'leads', 'action' => 'delete',  'name' => 'Delete Leads',  'slug' => 'leads.delete'],
        ['module' => 'leads', 'action' => 'convert', 'name' => 'Convert Leads', 'slug' => 'leads.convert'],

        // CRM — Contacts
        ['module' => 'crm-contacts', 'action' => 'view',   'name' => 'View CRM Contacts',   'slug' => 'crm-contacts.view'],
        ['module' => 'crm-contacts', 'action' => 'create', 'name' => 'Create CRM Contacts', 'slug' => 'crm-contacts.create'],
        ['module' => 'crm-contacts', 'action' => 'edit',   'name' => 'Edit CRM Contacts',   'slug' => 'crm-contacts.edit'],
        ['module' => 'crm-contacts', 'action' => 'delete', 'name' => 'Delete CRM Contacts', 'slug' => 'crm-contacts.delete'],

        // CRM — Opportunities
        ['module' => 'opportunities', 'action' => 'view',   'name' => 'View Opportunities',   'slug' => 'opportunities.view'],
        ['module' => 'opportunities', 'action' => 'create', 'name' => 'Create Opportunities', 'slug' => 'opportunities.create'],
        ['module' => 'opportunities', 'action' => 'edit',   'name' => 'Edit Opportunities',   'slug' => 'opportunities.edit'],
        ['module' => 'opportunities', 'action' => 'delete', 'name' => 'Delete Opportunities', 'slug' => 'opportunities.delete'],
        ['module' => 'opportunities', 'action' => 'manage', 'name' => 'Manage Pipeline',      'slug' => 'opportunities.manage'],

        // CRM — Activities
        ['module' => 'crm-activities', 'action' => 'view',   'name' => 'View Activities',   'slug' => 'crm-activities.view'],
        ['module' => 'crm-activities', 'action' => 'create', 'name' => 'Create Activities', 'slug' => 'crm-activities.create'],
        ['module' => 'crm-activities', 'action' => 'edit',   'name' => 'Edit Activities',   'slug' => 'crm-activities.edit'],

        // CRM — Communications
        ['module' => 'crm-communications', 'action' => 'view',   'name' => 'View Communications',   'slug' => 'crm-communications.view'],
        ['module' => 'crm-communications', 'action' => 'create', 'name' => 'Create Communications', 'slug' => 'crm-communications.create'],

        // CRM — Pipeline Stages
        ['module' => 'pipeline-stages', 'action' => 'manage', 'name' => 'Manage Pipeline Stages', 'slug' => 'pipeline-stages.manage'],
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
