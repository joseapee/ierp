<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\ProductAttribute;
use App\Models\ProductAttributeValue;
use App\Models\Tenant;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class ProductAttributeSeeder extends Seeder
{
    public function run(): void
    {
        $tenant = Tenant::query()->first();
        $tid = $tenant?->id;

        $attributes = [
            'Size' => ['XS', 'S', 'M', 'L', 'XL', 'XXL'],
            'Color' => ['Red', 'Blue', 'Green', 'Black', 'White', 'Yellow'],
        ];

        foreach ($attributes as $attrName => $values) {
            $attribute = ProductAttribute::query()->create([
                'tenant_id' => $tid,
                'name' => $attrName,
                'slug' => Str::slug($attrName),
            ]);

            $order = 0;
            foreach ($values as $value) {
                ProductAttributeValue::query()->create([
                    'product_attribute_id' => $attribute->id,
                    'value' => $value,
                    'slug' => Str::slug($value),
                    'sort_order' => $order++,
                ]);
            }
        }
    }
}
