<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Product;
use App\Models\StockLedger;
use App\Models\Warehouse;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<StockLedger> */
class StockLedgerFactory extends Factory
{
    protected $model = StockLedger::class;

    /** @return array<string, mixed> */
    public function definition(): array
    {
        $qty = fake()->randomFloat(4, 1, 100);
        $unitCost = fake()->randomFloat(4, 1, 200);

        return [
            'tenant_id' => null,
            'product_id' => Product::factory(),
            'product_variant_id' => null,
            'warehouse_id' => Warehouse::factory(),
            'stock_batch_id' => null,
            'movement_type' => fake()->randomElement(['purchase_receipt', 'sales_issue', 'adjustment_in', 'adjustment_out']),
            'quantity' => $qty,
            'unit_cost' => $unitCost,
            'total_cost' => round($qty * $unitCost, 4),
            'running_balance' => $qty,
            'reference_type' => null,
            'reference_id' => null,
            'notes' => fake()->optional()->sentence(),
            'created_by' => null,
        ];
    }
}
