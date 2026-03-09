<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\ProductAttribute;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/** @extends Factory<ProductAttribute> */
class ProductAttributeFactory extends Factory
{
    protected $model = ProductAttribute::class;

    /** @return array<string, mixed> */
    public function definition(): array
    {
        $name = fake()->unique()->randomElement(['Size', 'Color', 'Material', 'Weight', 'Style', 'Pattern', 'Finish', 'Grade']);

        return [
            'tenant_id' => null,
            'name' => $name,
            'slug' => Str::slug($name),
            'sort_order' => 0,
        ];
    }
}
