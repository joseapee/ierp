<?php

declare(strict_types=1);

namespace App\Livewire\Catalog;

use App\Http\Requests\Catalog\StoreBrandRequest;
use App\Http\Requests\Catalog\UpdateBrandRequest;
use App\Models\Brand;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Str;
use Livewire\Component;

class BrandFormModal extends Component
{
    public bool $showModal = false;

    public ?int $brandId = null;

    public string $name = '';

    public string $slug = '';

    public string $description = '';

    public bool $is_active = true;

    protected $listeners = [
        'openBrandFormModal' => 'open',
    ];

    public function open(?int $brandId = null): void
    {
        $this->resetValidation();
        $this->reset(['name', 'slug', 'description', 'is_active']);
        $this->brandId = $brandId;
        $this->is_active = true;

        if ($brandId) {
            $brand = Brand::findOrFail($brandId);
            $this->name = $brand->name;
            $this->slug = $brand->slug;
            $this->description = $brand->description ?? '';
            $this->is_active = $brand->is_active;
        }

        $this->showModal = true;
    }

    public function updatedName(): void
    {
        if (! $this->brandId) {
            $this->slug = Str::slug($this->name);
        }
    }

    public function save(): void
    {
        if ($this->brandId) {
            $validated = $this->validate((new UpdateBrandRequest)->rules());
            $brand = Brand::findOrFail($this->brandId);
            $this->authorize('update', $brand);
            $brand->update($validated);
            $this->dispatch('toast', message: 'Brand updated successfully.', type: 'success');
        } else {
            $validated = $this->validate((new StoreBrandRequest)->rules());
            $this->authorize('create', Brand::class);
            Brand::create($validated);
            $this->dispatch('toast', message: 'Brand created successfully.', type: 'success');
        }

        $this->showModal = false;
        $this->dispatch('brandSaved');
    }

    public function render(): View
    {
        return view('livewire.catalog.brand-form-modal');
    }
}
