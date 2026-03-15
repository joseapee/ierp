<?php

namespace Database\Factories;

use App\Models\Payment;
use App\Models\Subscription;
use App\Models\Tenant;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Payment>
 */
class PaymentFactory extends Factory
{
    protected $model = Payment::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'tenant_id' => Tenant::factory(),
            'subscription_id' => Subscription::factory(),
            'amount' => fake()->randomFloat(2, 10000, 100000),
            'currency' => 'NGN',
            'payment_method' => 'card',
            'status' => 'pending',
            'paystack_reference' => Str::uuid()->toString(),
        ];
    }

    public function successful(): static
    {
        return $this->state(fn (array $attributes): array => [
            'status' => 'success',
            'paid_at' => now(),
            'paystack_transaction_id' => (string) fake()->randomNumber(8),
        ]);
    }

    public function failed(): static
    {
        return $this->state(fn (array $attributes): array => [
            'status' => 'failed',
        ]);
    }

    public function refunded(): static
    {
        return $this->state(fn (array $attributes): array => [
            'status' => 'refunded',
            'paid_at' => now()->subDay(),
        ]);
    }
}
