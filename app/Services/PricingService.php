<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Product;

class PricingService
{
    public function __construct(
        private readonly BomService $bomService,
    ) {}

    /**
     * Compute the selling price for a product based on its pricing mode and cost.
     */
    public function computeSellPrice(Product $product, ?float $costOverride = null): float
    {
        $cost = $costOverride ?? (float) $product->cost_price;

        return match ($product->pricing_mode) {
            'percentage_markup' => $cost * (1 + ((float) $product->markup_percentage / 100)),
            'fixed_profit' => $cost + (float) $product->profit_amount,
            default => (float) $product->sell_price,
        };
    }

    /**
     * Recalculate cost from the active BOM and update the product's cost_price and sell_price.
     *
     * Returns the updated product.
     */
    public function recalculateFromBom(Product $product): Product
    {
        $activeBom = $product->activeBom;

        if (! $activeBom) {
            return $product;
        }

        $bomCost = $this->bomService->calculateCost($activeBom);

        $updates = ['cost_price' => $bomCost];

        if ($product->pricing_mode !== 'manual') {
            $updates['sell_price'] = $this->computeSellPrice($product, $bomCost);
        }

        $product->update($updates);

        return $product->fresh();
    }
}
