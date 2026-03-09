<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use App\Models\Tenant;
use App\Models\UnitOfMeasure;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        $tenant = Tenant::query()->first();
        $tid = $tenant?->id;

        $piece = UnitOfMeasure::query()->where('abbreviation', 'pcs')->first();
        $kg = UnitOfMeasure::query()->where('abbreviation', 'kg')->first();

        if (! $piece || ! $kg) {
            return;
        }

        $category = Category::query()->where('slug', 'phones')->first();
        $brand = Brand::query()->first();

        Product::query()->create([
            'tenant_id' => $tid,
            'category_id' => $category?->id,
            'brand_id' => $brand?->id,
            'base_unit_id' => $piece->id,
            'name' => 'Smartphone X100',
            'slug' => 'smartphone-x100',
            'sku' => 'PRD-0001',
            'type' => 'standard',
            'description' => 'A high-end smartphone with advanced features.',
            'cost_price' => 250.0000,
            'sell_price' => 499.9900,
            'tax_rate' => 7.50,
            'valuation_method' => 'weighted_average',
            'reorder_level' => 10,
            'reorder_quantity' => 50,
        ]);

        $groceryCategory = Category::query()->where('slug', 'dairy')->first();

        Product::query()->create([
            'tenant_id' => $tid,
            'category_id' => $groceryCategory?->id,
            'brand_id' => null,
            'base_unit_id' => $kg->id,
            'name' => 'Fresh Milk',
            'slug' => 'fresh-milk',
            'sku' => 'PRD-0002',
            'type' => 'standard',
            'description' => 'Pasteurized whole milk.',
            'cost_price' => 1.5000,
            'sell_price' => 2.9900,
            'tax_rate' => 0,
            'valuation_method' => 'fifo',
            'reorder_level' => 20,
            'reorder_quantity' => 100,
        ]);

        $fashionCategory = Category::query()->where('slug', 'men')->first();

        Product::query()->create([
            'tenant_id' => $tid,
            'category_id' => $fashionCategory?->id,
            'brand_id' => $brand?->id,
            'base_unit_id' => $piece->id,
            'name' => 'Classic T-Shirt',
            'slug' => Str::slug('Classic T-Shirt'),
            'sku' => 'PRD-0003',
            'type' => 'variable',
            'description' => 'Comfortable cotton t-shirt available in multiple sizes and colors.',
            'cost_price' => 5.0000,
            'sell_price' => 19.9900,
            'tax_rate' => 7.50,
            'valuation_method' => 'weighted_average',
            'reorder_level' => 25,
            'reorder_quantity' => 100,
        ]);
    }
}
