<?php

namespace Database\Factories;

use App\Models\Invoice;
use App\Models\Payment;
use App\Models\Subscription;
use App\Models\Tenant;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Invoice>
 */
class InvoiceFactory extends Factory
{
    protected $model = Invoice::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'tenant_id' => Tenant::factory(),
            'subscription_id' => Subscription::factory(),
            'payment_id' => null,
            'invoice_number' => 'INV-'.str_pad((string) fake()->unique()->randomNumber(6), 6, '0', STR_PAD_LEFT),
            'amount' => fake()->randomFloat(2, 10000, 100000),
            'currency' => 'NGN',
            'status' => 'draft',
            'issued_at' => now(),
            'due_at' => now()->addDays(30),
            'line_items' => [
                ['description' => 'Subscription fee', 'amount' => fake()->randomFloat(2, 10000, 100000)],
            ],
        ];
    }

    public function paid(): static
    {
        return $this->state(fn (array $attributes): array => [
            'status' => 'paid',
            'paid_at' => now(),
            'payment_id' => Payment::factory()->successful(),
        ]);
    }

    public function issued(): static
    {
        return $this->state(fn (array $attributes): array => [
            'status' => 'issued',
            'issued_at' => now(),
        ]);
    }

    public function void(): static
    {
        return $this->state(fn (array $attributes): array => [
            'status' => 'void',
        ]);
    }
}
