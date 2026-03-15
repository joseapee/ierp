<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\FiscalYear;
use App\Models\JournalEntry;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<JournalEntry> */
class JournalEntryFactory extends Factory
{
    protected $model = JournalEntry::class;

    /** @return array<string, mixed> */
    public function definition(): array
    {
        return [
            'tenant_id' => null,
            'fiscal_year_id' => FiscalYear::factory(),
            'entry_number' => 'JE-'.str_pad((string) fake()->unique()->numberBetween(1, 99999), 6, '0', STR_PAD_LEFT),
            'date' => fake()->dateTimeBetween('-1 year', 'now')->format('Y-m-d'),
            'description' => fake()->sentence(),
            'reference' => null,
            'source_type' => null,
            'source_id' => null,
            'status' => 'draft',
            'posted_by' => null,
            'posted_at' => null,
            'voided_by' => null,
            'voided_at' => null,
            'notes' => null,
        ];
    }

    public function posted(): static
    {
        return $this->state([
            'status' => 'posted',
            'posted_at' => now(),
        ]);
    }

    public function voided(): static
    {
        return $this->state([
            'status' => 'voided',
            'voided_at' => now(),
        ]);
    }
}
