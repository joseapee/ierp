<?php

declare(strict_types=1);

namespace App\Livewire\Catalog;

use App\Http\Requests\Catalog\StoreCategoryRequest;
use App\Http\Requests\Catalog\UpdateCategoryRequest;
use App\Models\Category;
use App\Services\CategoryService;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Str;
use Livewire\Component;

class CategoryFormModal extends Component
{
    public bool $showModal = false;

    public ?int $categoryId = null;

    public string $name = '';

    public string $slug = '';

    public ?int $parent_id = null;

    public string $description = '';

    public int $sort_order = 0;

    public bool $is_active = true;

    protected $listeners = [
        'openCategoryFormModal' => 'open',
    ];

    public function open(?int $categoryId = null): void
    {
        $this->resetValidation();
        $this->reset(['name', 'slug', 'parent_id', 'description', 'sort_order', 'is_active']);
        $this->categoryId = $categoryId;
        $this->is_active = true;
        $this->sort_order = 0;

        if ($categoryId) {
            $category = Category::findOrFail($categoryId);
            $this->name = $category->name;
            $this->slug = $category->slug;
            $this->parent_id = $category->parent_id;
            $this->description = $category->description ?? '';
            $this->sort_order = $category->sort_order;
            $this->is_active = $category->is_active;
        }

        $this->showModal = true;
    }

    public function updatedName(): void
    {
        if (! $this->categoryId) {
            $this->slug = Str::slug($this->name);
        }
    }

    public function save(): void
    {
        $service = app(CategoryService::class);

        if ($this->categoryId) {
            $validated = $this->validate((new UpdateCategoryRequest)->rules());
            $category = Category::findOrFail($this->categoryId);
            $this->authorize('update', $category);
            $service->update($category, $validated);
            $this->dispatch('toast', message: 'Category updated successfully.', type: 'success');
        } else {
            $validated = $this->validate((new StoreCategoryRequest)->rules());
            $this->authorize('create', Category::class);
            $service->create($validated);
            $this->dispatch('toast', message: 'Category created successfully.', type: 'success');
        }

        $this->showModal = false;
        $this->dispatch('categorySaved');
    }

    public function render(): View
    {
        $parentCategories = Category::query()
            ->where('is_active', true)
            ->whereNull('parent_id')
            ->when($this->categoryId, fn ($q) => $q->where('id', '!=', $this->categoryId))
            ->orderBy('name')
            ->get();

        return view('livewire.catalog.category-form-modal', [
            'parentCategories' => $parentCategories,
        ]);
    }
}
