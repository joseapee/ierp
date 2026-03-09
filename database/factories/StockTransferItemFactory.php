<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Product;
use App\Models\StockTransfer;
use App\Models\StockTransferItem;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<StockTransferItem> */
class StockTransferItemFactory extends Factory
{
    protected $model = StockTransferItem::class;

    /** @return array<string, mixed> */
    public function definition(): array
    {
        return [
            'stock_transfer_id' => StockTransfer::factory(),
            'product_id' => Product::factory(),
            'product_variant_id' => null,
            'stock_batch_id' => null,
            'quantity' => fake()->randomFloat(4, 1, 50),
            'unit_cost' => fake()->randomFloat(4, 1, 200),
        ];
    }
}
