<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\ProductAttribute;
use App\Models\ProductAttributeValue;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/** @extends Factory<ProductAttributeValue> */
class ProductAttributeValueFactory extends Factory
{
    protected $model = ProductAttributeValue::class;

    /** @return array<string, mixed> */
    public function definition(): array
    {
        $value = fake()->unique()->word();

        return [
            'product_attribute_id' => ProductAttribute::factory(),
            'value' => $value,
            'slug' => Str::slug($value),
            'sort_order' => 0,
        ];
    }
}
