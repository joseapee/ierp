<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Tenant;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $tenant = Tenant::query()->first();

        $categories = [
            'Electronics' => ['Phones', 'Laptops', 'Accessories'],
            'Fashion' => ['Men', 'Women', 'Kids'],
            'Grocery' => ['Dairy', 'Beverages', 'Snacks'],
            'Furniture' => ['Office', 'Home', 'Outdoor'],
            'Raw Materials' => ['Fabrics', 'Metals', 'Chemicals'],
        ];

        $order = 0;
        foreach ($categories as $parentName => $children) {
            $parent = Category::query()->create([
                'tenant_id' => $tenant?->id,
                'name' => $parentName,
                'slug' => \Illuminate\Support\Str::slug($parentName),
                'sort_order' => $order++,
            ]);

            foreach ($children as $childName) {
                Category::query()->create([
                    'tenant_id' => $tenant?->id,
                    'parent_id' => $parent->id,
                    'name' => $childName,
                    'slug' => \Illuminate\Support\Str::slug($childName),
                    'sort_order' => $order++,
                ]);
            }
        }
    }
}
