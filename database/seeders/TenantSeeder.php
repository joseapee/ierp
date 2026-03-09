<?php

namespace Database\Seeders;

use App\Models\Tenant;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class TenantSeeder extends Seeder
{
    public function run(): void
    {
        // Create a demo tenant
        $tenant = Tenant::query()->updateOrCreate(
            ['slug' => 'demo'],
            [
                'name' => 'Demo Company',
                'plan' => 'pro',
                'status' => 'active',
            ]
        );

        // Create a super admin (tenant_id = null, bypasses all gates)
        User::query()->updateOrCreate(
            ['email' => 'superadmin@ierp.com'],
            [
                'name' => 'Super Admin',
                'password' => Hash::make('password'),
                'tenant_id' => null,
                'is_super_admin' => true,
                'is_active' => true,
            ]
        );

        // Create a demo tenant admin
        User::query()->updateOrCreate(
            ['email' => 'admin@demo.com'],
            [
                'name' => 'Demo Admin',
                'password' => Hash::make('password'),
                'tenant_id' => $tenant->id,
                'is_active' => true,
            ]
        );
    }
}
