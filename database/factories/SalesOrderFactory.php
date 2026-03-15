<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Customer;
use App\Models\SalesOrder;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<SalesOrder> */
class SalesOrderFactory extends Factory
{
    protected $model = SalesOrder::class;

    /** @return array<string, mixed> */
    public function definition(): array
    {
        return [
            'tenant_id' => null,
            'customer_id' => Customer::factory(),
            'order_number' => strtoupper(fake()->unique()->bothify('SO-######')),
            'order_date' => fake()->date(),
            'due_date' => fake()->optional()->dateTimeBetween('now', '+30 days')?->format('Y-m-d'),
            'status' => 'draft',
            'subtotal' => 0,
            'tax_amount' => 0,
            'discount_amount' => 0,
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

    public function fulfilled(): static
    {
        return $this->state([
            'status' => 'fulfilled',
            'confirmed_by' => User::factory(),
            'confirmed_at' => now()->subDay(),
            'fulfilled_by' => User::factory(),
            'fulfilled_at' => now(),
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
