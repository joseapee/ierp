<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Product;
use App\Models\UnitOfMeasure;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/** @extends Factory<Product> */
class ProductFactory extends Factory
{
    protected $model = Product::class;

    /** @return array<string, mixed> */
    public function definition(): array
    {
        $name = fake()->unique()->words(3, true);

        return [
            'tenant_id' => null,
            'category_id' => null,
            'brand_id' => null,
            'base_unit_id' => UnitOfMeasure::factory(),
            'name' => $name,
            'slug' => Str::slug($name),
            'sku' => strtoupper(fake()->unique()->bothify('PRD-####-??')),
            'type' => 'standard',
            'description' => fake()->optional()->paragraph(),
            'short_description' => fake()->optional()->sentence(),
            'image' => null,
            'barcode' => fake()->optional()->ean13(),
            'cost_price' => fake()->randomFloat(4, 1, 500),
            'sell_price' => fake()->randomFloat(4, 5, 1000),
            'pricing_mode' => 'manual',
            'markup_percentage' => null,
            'profit_amount' => null,
            'tax_rate' => fake()->randomElement([0, 5, 7.5, 10, 15]),
            'valuation_method' => 'weighted_average',
            'reorder_level' => fake()->randomFloat(0, 5, 50),
            'reorder_quantity' => fake()->randomFloat(0, 10, 100),
            'is_active' => true,
            'is_purchasable' => true,
            'is_sellable' => true,
            'is_stockable' => true,
        ];
    }

    public function variable(): static
    {
        return $this->state(['type' => 'variable']);
    }

    public function service(): static
    {
        return $this->state([
            'type' => 'service',
            'is_stockable' => false,
        ]);
    }

    public function manufactured(): static
    {
        return $this->state([
            'type' => 'manufactured',
            'is_purchasable' => false,
            'is_sellable' => true,
            'pricing_mode' => 'percentage_markup',
            'markup_percentage' => 50,
        ]);
    }
}
