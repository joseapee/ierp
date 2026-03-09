<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Category;
use App\Models\Product;
use App\Models\ProductAttributeValue;
use App\Models\ProductVariant;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ProductService
{
    /**
     * Paginated, searchable, filterable list of products.
     *
     * @param  array{search?: string, category_id?: int, brand_id?: int, type?: string, is_active?: string}  $filters
     */
    public function paginate(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        return Product::query()
            ->with(['category', 'brand', 'baseUnit'])
            ->when($filters['search'] ?? null, fn ($q, $search) => $q->where(fn ($q) => $q->where('name', 'like', "%{$search}%")
                ->orWhere('sku', 'like', "%{$search}%")
            ))
            ->when($filters['category_id'] ?? null, fn ($q, $categoryId) => $q->where('category_id', $categoryId))
            ->when($filters['brand_id'] ?? null, fn ($q, $brandId) => $q->where('brand_id', $brandId))
            ->when($filters['type'] ?? null, fn ($q, $type) => $q->where('type', $type))
            ->when(isset($filters['is_active']), fn ($q) => $q->where('is_active', $filters['is_active'] === 'active'))
            ->latest()
            ->paginate($perPage);
    }

    /**
     * Create a new product and optionally sync attributes.
     *
     * @param  array{name: string, slug: string, sku: string, type: string, category_id?: int, brand_id?: int, base_unit_id: int, description?: string, short_description?: string, image?: string, barcode?: string, cost_price?: float, sell_price?: float, tax_rate?: float, valuation_method?: string, reorder_level?: float, reorder_quantity?: float, is_active?: bool, is_purchasable?: bool, is_sellable?: bool, is_stockable?: bool, attribute_ids?: int[]}  $data
     */
    public function create(array $data): Product
    {
        return DB::transaction(function () use ($data): Product {
            $product = Product::create([
                'name' => $data['name'],
                'slug' => $data['slug'],
                'sku' => $data['sku'],
                'type' => $data['type'],
                'category_id' => $data['category_id'] ?? null,
                'brand_id' => $data['brand_id'] ?? null,
                'base_unit_id' => $data['base_unit_id'],
                'description' => $data['description'] ?? null,
                'short_description' => $data['short_description'] ?? null,
                'image' => $data['image'] ?? null,
                'barcode' => $data['barcode'] ?? null,
                'cost_price' => $data['cost_price'] ?? 0,
                'sell_price' => $data['sell_price'] ?? 0,
                'tax_rate' => $data['tax_rate'] ?? 0,
                'valuation_method' => $data['valuation_method'] ?? 'fifo',
                'reorder_level' => $data['reorder_level'] ?? 0,
                'reorder_quantity' => $data['reorder_quantity'] ?? 0,
                'is_active' => $data['is_active'] ?? true,
                'is_purchasable' => $data['is_purchasable'] ?? true,
                'is_sellable' => $data['is_sellable'] ?? true,
                'is_stockable' => $data['is_stockable'] ?? true,
            ]);

            if ($data['type'] === 'variable' && ! empty($data['attribute_ids'])) {
                $product->attributes()->sync($data['attribute_ids']);
            }

            return $product->load(['category', 'brand', 'baseUnit', 'attributes']);
        });
    }

    /**
     * Update an existing product and optionally sync attributes.
     *
     * @param  array{name?: string, slug?: string, sku?: string, type?: string, category_id?: int, brand_id?: int, base_unit_id?: int, description?: string, short_description?: string, image?: string, barcode?: string, cost_price?: float, sell_price?: float, tax_rate?: float, valuation_method?: string, reorder_level?: float, reorder_quantity?: float, is_active?: bool, is_purchasable?: bool, is_sellable?: bool, is_stockable?: bool, attribute_ids?: int[]}  $data
     */
    public function update(Product $product, array $data): Product
    {
        return DB::transaction(function () use ($product, $data): Product {
            $product->update(collect($data)->only([
                'name',
                'slug',
                'sku',
                'type',
                'category_id',
                'brand_id',
                'base_unit_id',
                'description',
                'short_description',
                'image',
                'barcode',
                'cost_price',
                'sell_price',
                'tax_rate',
                'valuation_method',
                'reorder_level',
                'reorder_quantity',
                'is_active',
                'is_purchasable',
                'is_sellable',
                'is_stockable',
            ])->toArray());

            if (array_key_exists('attribute_ids', $data)) {
                $product->attributes()->sync($data['attribute_ids'] ?? []);
            }

            return $product->load(['category', 'brand', 'baseUnit', 'attributes']);
        });
    }

    /**
     * Delete a product (soft delete).
     */
    public function delete(Product $product): bool
    {
        return (bool) $product->delete();
    }

    /**
     * Generate a SKU from the product name.
     *
     * Takes the first 3 characters uppercase and appends 4 random digits.
     * If a category ID is provided, prefixes with the first 2 characters of the category name.
     */
    public function generateSku(string $name, ?int $categoryId = null): string
    {
        $prefix = '';

        if ($categoryId !== null) {
            $category = Category::find($categoryId);

            if ($category) {
                $prefix = Str::upper(Str::substr($category->name, 0, 2));
            }
        }

        $base = Str::upper(Str::substr($name, 0, 3));
        $digits = str_pad((string) random_int(0, 9999), 4, '0', STR_PAD_LEFT);

        return $prefix.$base.$digits;
    }

    /**
     * Generate variants from the Cartesian product of attribute values.
     *
     * @param  array<int, int[]>  $attributeConfig  Keyed by attribute_id, each value is an array of attribute value IDs.
     */
    public function generateVariants(Product $product, array $attributeConfig): void
    {
        DB::transaction(function () use ($product, $attributeConfig): void {
            // Build the arrays for Cartesian product, preserving attribute_id keys.
            $attributeSets = [];

            foreach ($attributeConfig as $attributeId => $valueIds) {
                $values = ProductAttributeValue::whereIn('id', $valueIds)
                    ->where('product_attribute_id', $attributeId)
                    ->orderBy('sort_order')
                    ->get();

                if ($values->isNotEmpty()) {
                    $attributeSets[$attributeId] = $values;
                }
            }

            if (empty($attributeSets)) {
                return;
            }

            // Compute Cartesian product of all attribute value collections.
            $combinations = [[]];

            foreach ($attributeSets as $attributeId => $values) {
                $newCombinations = [];

                foreach ($combinations as $combination) {
                    foreach ($values as $value) {
                        $newCombinations[] = $combination + [
                            $attributeId => $value,
                        ];
                    }
                }

                $combinations = $newCombinations;
            }

            // Create a variant for each combination.
            $sortOrder = 0;

            foreach ($combinations as $combination) {
                $skuParts = [];
                $nameParts = [];

                foreach ($combination as $attributeId => $value) {
                    $skuParts[] = $value->slug;
                    $nameParts[] = $value->attribute->name.':'.$value->value;
                }

                $variantSku = $product->sku.'-'.implode('-', $skuParts);
                $variantName = $product->name.' - '.implode(', ', $nameParts);

                $variant = ProductVariant::create([
                    'product_id' => $product->id,
                    'sku' => $variantSku,
                    'name' => $variantName,
                    'is_active' => true,
                    'sort_order' => $sortOrder++,
                ]);

                // Create pivot entries in product_variant_attributes.
                foreach ($combination as $attributeId => $value) {
                    $variant->attributeValues()->attach($value->id, [
                        'product_attribute_id' => $attributeId,
                    ]);
                }
            }
        });
    }
}
