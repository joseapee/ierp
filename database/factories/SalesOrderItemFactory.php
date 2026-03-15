<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Product;
use App\Models\SalesOrder;
use App\Models\SalesOrderItem;
use App\Models\Warehouse;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<SalesOrderItem> */
class SalesOrderItemFactory extends Factory
{
    protected $model = SalesOrderItem::class;

    /** @return array<string, mixed> */
    public function definition(): array
    {
        $quantity = fake()->randomFloat(4, 1, 50);
        $unitPrice = fake()->randomFloat(4, 50, 10000);
        $discountPercent = fake()->randomElement([0, 5, 10]);
        $taxRate = fake()->randomElement([0, 7.5]);
        $lineSubtotal = $quantity * $unitPrice * (1 - $discountPercent / 100);
        $taxAmount = $lineSubtotal * ($taxRate / 100);

        return [
            'tenant_id' => null,
            'sales_order_id' => SalesOrder::factory(),
            'product_id' => Product::factory(),
            'product_variant_id' => null,
            'description' => null,
            'quantity' => $quantity,
            'unit_price' => $unitPrice,
            'discount_percent' => $discountPercent,
            'tax_rate' => $taxRate,
            'tax_amount' => $taxAmount,
            'total' => $lineSubtotal + $taxAmount,
            'quantity_fulfilled' => 0,
            'warehouse_id' => Warehouse::factory(),
        ];
    }

    public function fullyFulfilled(): static
    {
        return $this->state(function (array $attributes) {
            return ['quantity_fulfilled' => $attributes['quantity']];
        });
    }
}
