<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Product;
use App\Models\StockAdjustment;
use App\Models\StockAdjustmentItem;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<StockAdjustmentItem> */
class StockAdjustmentItemFactory extends Factory
{
    protected $model = StockAdjustmentItem::class;

    /** @return array<string, mixed> */
    public function definition(): array
    {
        return [
            'stock_adjustment_id' => StockAdjustment::factory(),
            'product_id' => Product::factory(),
            'product_variant_id' => null,
            'stock_batch_id' => null,
            'type' => fake()->randomElement(['addition', 'subtraction']),
            'quantity' => fake()->randomFloat(4, 1, 50),
            'unit_cost' => fake()->randomFloat(4, 1, 200),
            'reason' => fake()->optional()->sentence(),
        ];
    }
}
