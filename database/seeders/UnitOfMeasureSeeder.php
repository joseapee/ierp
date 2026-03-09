<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Tenant;
use App\Models\UnitConversion;
use App\Models\UnitOfMeasure;
use Illuminate\Database\Seeder;

class UnitOfMeasureSeeder extends Seeder
{
    public function run(): void
    {
        $tenant = Tenant::query()->first();
        $tid = $tenant?->id;

        $units = [
            ['name' => 'Piece', 'abbreviation' => 'pcs', 'type' => 'quantity', 'is_base_unit' => true],
            ['name' => 'Box', 'abbreviation' => 'box', 'type' => 'quantity', 'is_base_unit' => false],
            ['name' => 'Dozen', 'abbreviation' => 'doz', 'type' => 'quantity', 'is_base_unit' => false],
            ['name' => 'Kilogram', 'abbreviation' => 'kg', 'type' => 'weight', 'is_base_unit' => true],
            ['name' => 'Gram', 'abbreviation' => 'g', 'type' => 'weight', 'is_base_unit' => false],
            ['name' => 'Liter', 'abbreviation' => 'L', 'type' => 'volume', 'is_base_unit' => true],
            ['name' => 'Milliliter', 'abbreviation' => 'mL', 'type' => 'volume', 'is_base_unit' => false],
            ['name' => 'Meter', 'abbreviation' => 'm', 'type' => 'length', 'is_base_unit' => true],
            ['name' => 'Centimeter', 'abbreviation' => 'cm', 'type' => 'length', 'is_base_unit' => false],
        ];

        $created = [];
        foreach ($units as $unit) {
            $created[$unit['abbreviation']] = UnitOfMeasure::query()->create(
                array_merge($unit, ['tenant_id' => $tid])
            );
        }

        $conversions = [
            ['from' => 'box', 'to' => 'pcs', 'factor' => 12],
            ['from' => 'doz', 'to' => 'pcs', 'factor' => 12],
            ['from' => 'kg', 'to' => 'g', 'factor' => 1000],
            ['from' => 'L', 'to' => 'mL', 'factor' => 1000],
            ['from' => 'm', 'to' => 'cm', 'factor' => 100],
        ];

        foreach ($conversions as $conv) {
            UnitConversion::query()->create([
                'tenant_id' => $tid,
                'from_unit_id' => $created[$conv['from']]->id,
                'to_unit_id' => $created[$conv['to']]->id,
                'factor' => $conv['factor'],
            ]);
        }
    }
}
