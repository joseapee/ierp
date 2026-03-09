<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\StockTransfer;
use App\Models\User;
use App\Models\Warehouse;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<StockTransfer> */
class StockTransferFactory extends Factory
{
    protected $model = StockTransfer::class;

    /** @return array<string, mixed> */
    public function definition(): array
    {
        return [
            'tenant_id' => null,
            'transfer_number' => strtoupper(fake()->unique()->bothify('TRF-####-??')),
            'from_warehouse_id' => Warehouse::factory(),
            'to_warehouse_id' => Warehouse::factory(),
            'status' => 'draft',
            'notes' => fake()->optional()->sentence(),
            'initiated_by' => User::factory(),
            'completed_by' => null,
            'completed_at' => null,
        ];
    }
}
