<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<ProductVariant> */
class ProductVariantFactory extends Factory
{
    protected $model = ProductVariant::class;

    /** @return array<string, mixed> */
    public function definition(): array
    {
        return [
            'product_id' => Product::factory(),
            'sku' => strtoupper(fake()->unique()->bothify('VAR-####-??')),
            'barcode' => fake()->optional()->ean13(),
            'name' => fake()->words(3, true),
            'cost_price_override' => null,
            'sell_price_override' => null,
            'image' => null,
            'is_active' => true,
            'sort_order' => 0,
        ];
    }
}
