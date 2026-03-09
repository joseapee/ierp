<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Category;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class CategoryService
{
    /**
     * Get categories as a nested tree.
     *
     * @param  array{search?: string, is_active?: bool}  $filters
     */
    public function tree(array $filters = []): Collection
    {
        return Category::query()
            ->whereNull('parent_id')
            ->with(['children' => function ($q) {
                $q->orderBy('sort_order')->with('children');
            }])
            ->when($filters['search'] ?? null, fn ($q, $search) => $q->where('name', 'like', "%{$search}%"))
            ->when(isset($filters['is_active']), fn ($q) => $q->where('is_active', $filters['is_active']))
            ->orderBy('sort_order')
            ->get();
    }

    /**
     * Get all categories as a flat list ordered by name.
     */
    public function flatList(): Collection
    {
        return Category::query()
            ->orderBy('name')
            ->get();
    }

    /**
     * Create a new category.
     *
     * @param  array{name: string, slug: string, parent_id?: int|null, description?: string|null, image?: string|null, sort_order?: int, is_active?: bool}  $data
     */
    public function create(array $data): Category
    {
        return DB::transaction(function () use ($data): Category {
            return Category::create([
                'name' => $data['name'],
                'slug' => $data['slug'],
                'parent_id' => $data['parent_id'] ?? null,
                'description' => $data['description'] ?? null,
                'image' => $data['image'] ?? null,
                'sort_order' => $data['sort_order'] ?? 0,
                'is_active' => $data['is_active'] ?? true,
            ]);
        });
    }

    /**
     * Update an existing category.
     *
     * @param  array{name?: string, slug?: string, parent_id?: int|null, description?: string|null, image?: string|null, sort_order?: int, is_active?: bool}  $data
     */
    public function update(Category $category, array $data): Category
    {
        return DB::transaction(function () use ($category, $data): Category {
            $category->update(
                collect($data)->only(['name', 'slug', 'parent_id', 'description', 'image', 'sort_order', 'is_active'])->toArray()
            );

            return $category->refresh();
        });
    }

    /**
     * Delete a category.
     *
     * @throws RuntimeException If the category has children or products.
     */
    public function delete(Category $category): bool
    {
        if ($category->children()->exists()) {
            throw new RuntimeException('Cannot delete a category that has children.');
        }

        if ($category->products()->exists()) {
            throw new RuntimeException('Cannot delete a category that has products.');
        }

        return (bool) $category->delete();
    }
}
