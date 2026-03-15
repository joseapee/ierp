<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\PurchaseOrder;
use App\Models\Supplier;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<PurchaseOrder> */
class PurchaseOrderFactory extends Factory
{
    protected $model = PurchaseOrder::class;

    /** @return array<string, mixed> */
    public function definition(): array
    {
        return [
            'tenant_id' => null,
            'supplier_id' => Supplier::factory(),
            'order_number' => strtoupper(fake()->unique()->bothify('PO-######')),
            'order_date' => fake()->date(),
            'expected_date' => fake()->optional()->dateTimeBetween('now', '+30 days')?->format('Y-m-d'),
            'status' => 'draft',
            'subtotal' => 0,
            'tax_amount' => 0,
            'total_amount' => 0,
            'notes' => null,
        ];
    }

    public function confirmed(): static
    {
        return $this->state([
            'status' => 'confirmed',
            'confirmed_by' => User::factory(),
            'confirmed_at' => now(),
        ]);
    }

    public function received(): static
    {
        return $this->state([
            'status' => 'received',
            'confirmed_by' => User::factory(),
            'confirmed_at' => now()->subDay(),
            'received_by' => User::factory(),
            'received_at' => now(),
        ]);
    }

    public function cancelled(): static
    {
        return $this->state([
            'status' => 'cancelled',
            'cancelled_by' => User::factory(),
            'cancelled_at' => now(),
            'cancellation_reason' => 'Cancelled for testing',
        ]);
    }
}
