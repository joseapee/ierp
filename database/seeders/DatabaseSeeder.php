<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            PermissionSeeder::class,
            RoleSeeder::class,
            TenantSeeder::class,
            CategorySeeder::class,
            BrandSeeder::class,
            UnitOfMeasureSeeder::class,
            WarehouseSeeder::class,
            ProductAttributeSeeder::class,
            ProductSeeder::class,
            ProductionStageSeeder::class,
        ]);
    }
}
