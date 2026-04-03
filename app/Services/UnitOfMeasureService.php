<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\UnitConversion;
use App\Models\UnitOfMeasure;
use Illuminate\Database\Eloquent\Collection;

class UnitOfMeasureService
{
    /**
     * Get all units of measure for the current tenant scope.
     */
    public function all(): Collection
    {
        $tenantId = app()->bound('current.tenant') ? app('current.tenant')->id : null;

        return UnitOfMeasure::query()
            ->where('tenant_id', $tenantId)
            ->orderBy('name')
            ->get();
    }

    /**
     * Create a new unit of measure.
     */
    public function create(array $data): UnitOfMeasure
    {
        $tenantId = app()->bound('current.tenant') ? app('current.tenant')->id : null;

        return UnitOfMeasure::query()->create([
            'tenant_id' => $tenantId,
            'name' => $data['name'],
            'abbreviation' => $data['abbreviation'],
            'type' => $data['type'],
            'is_base_unit' => $data['is_base_unit'] ?? false,
            'is_active' => $data['is_active'] ?? true,
        ]);
    }

    /**
     * Update an existing unit of measure.
     */
    public function update(UnitOfMeasure $unit, array $data): UnitOfMeasure
    {
        $unit->update([
            'name' => $data['name'],
            'abbreviation' => $data['abbreviation'],
            'type' => $data['type'],
            'is_base_unit' => $data['is_base_unit'] ?? false,
            'is_active' => $data['is_active'] ?? true,
        ]);

        return $unit;
    }

    /**
     * Delete a unit of measure.
     */
    public function delete(UnitOfMeasure $unit): void
    {
        $unit->delete();
    }

    public function seedDefaultUnitsForTenant($tenant): void
    {
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
            // Delete Existing unit if it exists to avoid duplicates
            $existing = UnitOfMeasure::query()
                ->where('tenant_id', $tenant->id)
                ->where('abbreviation', $unit['abbreviation'])
                ->first();

            if (! $existing) {
                $created[$unit['abbreviation']] = UnitOfMeasure::query()->create(
                    array_merge($unit, ['tenant_id' => $tenant->id])
                );
            } else {
                $created[$unit['abbreviation']] = $existing;
            }
        }

        $conversions = [
            ['from' => 'box', 'to' => 'pcs', 'factor' => 12],
            ['from' => 'doz', 'to' => 'pcs', 'factor' => 12],
            ['from' => 'kg', 'to' => 'g', 'factor' => 1000],
            ['from' => 'L', 'to' => 'mL', 'factor' => 1000],
            ['from' => 'm', 'to' => 'cm', 'factor' => 100],
        ];

        foreach ($conversions as $conv) {
            // Delete existing conversion if it exists to avoid duplicates
            UnitConversion::query()
                ->where('tenant_id', $tenant->id)
                ->where('from_unit_id', $created[$conv['from']]->id)
                ->where('to_unit_id', $created[$conv['to']]->id)
                ->delete();

            // Recreate conversion
            UnitConversion::query()->create([
                'tenant_id' => $tenant->id,
                'from_unit_id' => $created[$conv['from']]->id,
                'to_unit_id' => $created[$conv['to']]->id,
                'factor' => $conv['factor'],
            ]);
        }
    }
}
