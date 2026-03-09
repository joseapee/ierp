<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Bom;
use App\Models\BomItem;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class BomService
{
    /**
     * Paginated list of BOMs for a product.
     *
     * @param  array{product_id?: int, status?: string, search?: string}  $filters
     */
    public function paginate(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        return Bom::query()
            ->with(['product', 'items.product'])
            ->when($filters['product_id'] ?? null, fn ($q, $v) => $q->where('product_id', $v))
            ->when($filters['status'] ?? null, fn ($q, $v) => $q->where('status', $v))
            ->when($filters['search'] ?? null, fn ($q, $v) => $q->where('name', 'like', "%{$v}%"))
            ->latest()
            ->paginate($perPage);
    }

    /**
     * Create a new BOM with its items.
     *
     * @param  array{product_id: int, name: string, version?: string, description?: string, yield_quantity?: float, status?: string, effective_date?: string, expiry_date?: string, notes?: string, items: array<int, array{product_id: int, product_variant_id?: int, quantity: float, unit_cost: float, wastage_percentage?: float, sort_order?: int, notes?: string}>}  $data
     */
    public function create(array $data): Bom
    {
        return DB::transaction(function () use ($data): Bom {
            $bom = Bom::create([
                'product_id' => $data['product_id'],
                'name' => $data['name'],
                'version' => $data['version'] ?? '1.0',
                'description' => $data['description'] ?? null,
                'yield_quantity' => $data['yield_quantity'] ?? 1,
                'status' => $data['status'] ?? 'draft',
                'effective_date' => $data['effective_date'] ?? null,
                'expiry_date' => $data['expiry_date'] ?? null,
                'notes' => $data['notes'] ?? null,
            ]);

            foreach ($data['items'] as $index => $item) {
                $bom->items()->create([
                    'product_id' => $item['product_id'],
                    'product_variant_id' => $item['product_variant_id'] ?? null,
                    'quantity' => $item['quantity'],
                    'unit_cost' => $item['unit_cost'],
                    'wastage_percentage' => $item['wastage_percentage'] ?? 0,
                    'sort_order' => $item['sort_order'] ?? $index,
                    'notes' => $item['notes'] ?? null,
                ]);
            }

            return $bom->load('items.product');
        });
    }

    /**
     * Update a BOM and replace its items.
     *
     * @param  array{name?: string, version?: string, description?: string, yield_quantity?: float, status?: string, effective_date?: string, expiry_date?: string, notes?: string, items?: array}  $data
     */
    public function update(Bom $bom, array $data): Bom
    {
        return DB::transaction(function () use ($bom, $data): Bom {
            $bom->update(collect($data)->only([
                'name', 'version', 'description', 'yield_quantity',
                'status', 'effective_date', 'expiry_date', 'notes',
            ])->toArray());

            if (array_key_exists('items', $data)) {
                $bom->items()->delete();

                foreach ($data['items'] as $index => $item) {
                    $bom->items()->create([
                        'product_id' => $item['product_id'],
                        'product_variant_id' => $item['product_variant_id'] ?? null,
                        'quantity' => $item['quantity'],
                        'unit_cost' => $item['unit_cost'],
                        'wastage_percentage' => $item['wastage_percentage'] ?? 0,
                        'sort_order' => $item['sort_order'] ?? $index,
                        'notes' => $item['notes'] ?? null,
                    ]);
                }
            }

            return $bom->load('items.product');
        });
    }

    /**
     * Delete a BOM. Only draft BOMs can be deleted.
     */
    public function delete(Bom $bom): void
    {
        if ($bom->status !== 'draft') {
            throw new RuntimeException('Only draft BOMs can be deleted.');
        }

        $bom->delete();
    }

    /**
     * Activate a BOM and deactivate any other active BOM for the same product.
     */
    public function activate(Bom $bom): Bom
    {
        return DB::transaction(function () use ($bom): Bom {
            // Deactivate other active BOMs for this product.
            Bom::query()
                ->where('product_id', $bom->product_id)
                ->where('id', '!=', $bom->id)
                ->where('status', 'active')
                ->update(['status' => 'inactive']);

            $bom->update([
                'status' => 'active',
                'effective_date' => $bom->effective_date ?? now(),
            ]);

            return $bom->fresh();
        });
    }

    /**
     * Calculate the total material cost for a BOM.
     *
     * Cost = sum of (quantity * unit_cost * (1 + wastage_percentage/100)) for each item,
     * divided by yield_quantity.
     */
    public function calculateCost(Bom $bom): float
    {
        $bom->loadMissing('items');

        $totalMaterialCost = $bom->items->sum(function (BomItem $item): float {
            $wastageMultiplier = 1 + ((float) $item->wastage_percentage / 100);

            return (float) $item->quantity * (float) $item->unit_cost * $wastageMultiplier;
        });

        $yieldQty = (float) $bom->yield_quantity;

        return $yieldQty > 0 ? $totalMaterialCost / $yieldQty : $totalMaterialCost;
    }

    /**
     * Create a new version of an existing BOM by duplicating it.
     */
    public function duplicate(Bom $bom, string $newVersion): Bom
    {
        return DB::transaction(function () use ($bom, $newVersion): Bom {
            $bom->loadMissing('items');

            $newBom = $bom->replicate(['id', 'created_at', 'updated_at', 'deleted_at']);
            $newBom->version = $newVersion;
            $newBom->status = 'draft';
            $newBom->save();

            foreach ($bom->items as $item) {
                $newItem = $item->replicate(['id', 'created_at', 'updated_at']);
                $newItem->bom_id = $newBom->id;
                $newItem->save();
            }

            return $newBom->load('items.product');
        });
    }
}
