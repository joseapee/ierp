<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\FiscalYear;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<FiscalYear> */
class FiscalYearFactory extends Factory
{
    protected $model = FiscalYear::class;

    /** @return array<string, mixed> */
    public function definition(): array
    {
        $year = (int) date('Y');

        return [
            'tenant_id' => null,
            'name' => "FY {$year}",
            'start_date' => "{$year}-01-01",
            'end_date' => "{$year}-12-31",
            'status' => 'open',
            'closed_by' => null,
            'closed_at' => null,
        ];
    }

    public function closed(): static
    {
        return $this->state([
            'status' => 'closed',
            'closed_at' => now(),
        ]);
    }

    public function locked(): static
    {
        return $this->state([
            'status' => 'locked',
            'closed_at' => now(),
        ]);
    }
}
