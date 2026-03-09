<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Tenant;
use App\Models\Warehouse;
use Illuminate\Database\Seeder;

class WarehouseSeeder extends Seeder
{
    public function run(): void
    {
        $tenant = Tenant::query()->first();

        Warehouse::query()->create([
            'tenant_id' => $tenant?->id,
            'name' => 'Main Warehouse',
            'code' => 'WH-MAIN',
            'address' => '123 Industrial Rd',
            'city' => 'Lagos',
            'is_active' => true,
            'is_default' => true,
        ]);
    }
}
