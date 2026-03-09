<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Bom;
use App\Models\BomItem;
use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<BomItem> */
class BomItemFactory extends Factory
{
    protected $model = BomItem::class;

    /** @return array<string, mixed> */
    public function definition(): array
    {
        return [
            'bom_id' => Bom::factory(),
            'product_id' => Product::factory(),
            'product_variant_id' => null,
            'quantity' => fake()->randomFloat(4, 1, 50),
            'unit_cost' => fake()->randomFloat(4, 1, 200),
            'wastage_percentage' => fake()->randomFloat(2, 0, 10),
            'sort_order' => 0,
            'notes' => null,
        ];
    }
}
