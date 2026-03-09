<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Warehouse;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class WarehouseService
{
    /**
     * Paginated, searchable, filterable list of warehouses.
     *
     * @param  array{search?: string, is_active?: bool}  $filters
     */
    public function paginate(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        return Warehouse::query()
            ->when($filters['search'] ?? null, fn ($q, $search) => $q->where(fn ($q) => $q->where('name', 'like', "%{$search}%")
                ->orWhere('code', 'like', "%{$search}%")
            ))
            ->when(isset($filters['is_active']), fn ($q) => $q->where('is_active', $filters['is_active']))
            ->latest()
            ->paginate($perPage);
    }

    /**
     * Get all active warehouses ordered by name.
     */
    public function all(): Collection
    {
        return Warehouse::query()
            ->where('is_active', true)
            ->orderBy('name')
            ->get();
    }

    /**
     * Create a new warehouse.
     *
     * @param  array{name: string, code: string, address?: string, city?: string, phone?: string, is_active?: bool, is_default?: bool}  $data
     */
    public function create(array $data): Warehouse
    {
        return DB::transaction(function () use ($data): Warehouse {
            if (! empty($data['is_default'])) {
                Warehouse::query()->where('is_default', true)->update(['is_default' => false]);
            }

            return Warehouse::create([
                'name' => $data['name'],
                'code' => $data['code'],
                'address' => $data['address'] ?? null,
                'city' => $data['city'] ?? null,
                'phone' => $data['phone'] ?? null,
                'is_active' => $data['is_active'] ?? true,
                'is_default' => $data['is_default'] ?? false,
            ]);
        });
    }

    /**
     * Update an existing warehouse.
     *
     * @param  array{name?: string, code?: string, address?: string, city?: string, phone?: string, is_active?: bool, is_default?: bool}  $data
     */
    public function update(Warehouse $warehouse, array $data): Warehouse
    {
        return DB::transaction(function () use ($warehouse, $data): Warehouse {
            if (! empty($data['is_default'])) {
                Warehouse::query()
                    ->where('is_default', true)
                    ->where('id', '!=', $warehouse->id)
                    ->update(['is_default' => false]);
            }

            $warehouse->update(
                collect($data)->only(['name', 'code', 'address', 'city', 'phone', 'is_active', 'is_default'])->toArray()
            );

            return $warehouse->refresh();
        });
    }

    /**
     * Delete a warehouse (soft delete).
     *
     * @throws RuntimeException
     */
    public function delete(Warehouse $warehouse): bool
    {
        if ($warehouse->stockBatches()->exists()) {
            throw new RuntimeException('Cannot delete a warehouse that has stock batches.');
        }

        return (bool) $warehouse->delete();
    }
}
