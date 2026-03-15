<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Product;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderItem;
use App\Models\Warehouse;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<PurchaseOrderItem> */
class PurchaseOrderItemFactory extends Factory
{
    protected $model = PurchaseOrderItem::class;

    /** @return array<string, mixed> */
    public function definition(): array
    {
        $quantity = fake()->randomFloat(4, 1, 100);
        $unitPrice = fake()->randomFloat(4, 10, 5000);
        $taxRate = fake()->randomElement([0, 7.5]);
        $lineSubtotal = $quantity * $unitPrice;
        $taxAmount = $lineSubtotal * ($taxRate / 100);

        return [
            'tenant_id' => null,
            'purchase_order_id' => PurchaseOrder::factory(),
            'product_id' => Product::factory(),
            'product_variant_id' => null,
            'description' => null,
            'quantity' => $quantity,
            'unit_price' => $unitPrice,
            'tax_rate' => $taxRate,
            'tax_amount' => $taxAmount,
            'total' => $lineSubtotal + $taxAmount,
            'quantity_received' => 0,
            'warehouse_id' => Warehouse::factory(),
        ];
    }

    public function fullyReceived(): static
    {
        return $this->state(function (array $attributes) {
            return ['quantity_received' => $attributes['quantity']];
        });
    }
}
