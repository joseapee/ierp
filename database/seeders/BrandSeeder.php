<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Brand;
use App\Models\Tenant;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class BrandSeeder extends Seeder
{
    public function run(): void
    {
        $tenant = Tenant::query()->first();

        $brands = ['Generic', 'Premium Line', 'EcoBrand', 'TechPro', 'ValueMax'];

        foreach ($brands as $name) {
            Brand::query()->create([
                'tenant_id' => $tenant?->id,
                'name' => $name,
                'slug' => Str::slug($name),
            ]);
        }
    }
}
