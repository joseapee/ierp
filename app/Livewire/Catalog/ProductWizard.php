<?php

declare(strict_types=1);

namespace App\Livewire\Catalog;

use App\Models\Bom;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductAttribute;
use App\Models\UnitOfMeasure;
use App\Services\BomService;
use App\Services\ProductService;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Str;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.app')]
class ProductWizard extends Component
{
    // ── Wizard state ──
    public int $currentStep = 1;

    public int $totalSteps = 6;

    public ?int $productId = null;

    // ── Step 1: Type ──
    public string $type = 'standard';

    // ── Step 2: Details ──
    public string $name = '';

    public string $slug = '';

    public string $sku = '';

    public ?int $category_id = null;

    public ?int $brand_id = null;

    public ?int $base_unit_id = null;

    public string $description = '';

    public string $short_description = '';

    // ── Step 3: Inventory ──
    public string $barcode = '';

    public string $valuation_method = 'weighted_average';

    public string $reorder_level = '0';

    public string $reorder_quantity = '0';

    public bool $is_active = true;

    public bool $is_purchasable = true;

    public bool $is_sellable = true;

    public bool $is_stockable = true;

    public array $attribute_ids = [];

    // ── Step 4: Manufacturing (only for manufactured products) ──
    public array $bomItems = [];

    public string $bomName = '';

    public string $bomVersion = '1.0';

    // ── Step 5: Pricing ──
    public string $cost_price = '0';

    public string $sell_price = '0';

    public string $tax_rate = '0';

    public string $pricing_mode = 'manual';

    public string $markup_percentage = '';

    public string $profit_amount = '';

    public function mount(?Product $product = null): void
    {
        if ($product && $product->exists) {
            $this->productId = $product->id;
            $product->load(['attributes', 'activeBom.items']);

            $this->type = $product->type;
            $this->name = $product->name;
            $this->slug = $this->generateSlug($product->name);
            $this->sku = $product->sku;
            $this->category_id = $product->category_id;
            $this->brand_id = $product->brand_id;
            $this->base_unit_id = $product->base_unit_id;
            $this->description = $product->description ?? '';
            $this->short_description = $product->short_description ?? '';
            $this->barcode = $product->barcode ?? '';
            $this->valuation_method = $product->valuation_method;
            $this->reorder_level = (string) $product->reorder_level;
            $this->reorder_quantity = (string) $product->reorder_quantity;
            $this->is_active = $product->is_active;
            $this->is_purchasable = $product->is_purchasable;
            $this->is_sellable = $product->is_sellable;
            $this->is_stockable = $product->is_stockable;
            $this->attribute_ids = $product->attributes->pluck('id')->toArray();
            $this->cost_price = (string) $product->cost_price;
            $this->sell_price = (string) $product->sell_price;
            $this->tax_rate = (string) $product->tax_rate;
            $this->pricing_mode = $product->pricing_mode ?? 'manual';
            $this->markup_percentage = (string) ($product->markup_percentage ?? '');
            $this->profit_amount = (string) ($product->profit_amount ?? '');

            if ($product->activeBom) {
                $this->bomName = $product->activeBom->name;
                $this->bomVersion = $product->activeBom->version;
                $this->bomItems = $product->activeBom->items->map(fn ($item) => [
                    'product_id' => $item->product_id,
                    'quantity' => (string) $item->quantity,
                    'unit_cost' => (string) $item->unit_cost,
                    'wastage_percentage' => (string) $item->wastage_percentage,
                ])->toArray();
            }
        }
    }

    public function selectType(string $type): void
    {
        $this->type = $type;
        $this->updatedType();
    }

    public function updatedName(): void
    {
        if (! $this->productId) {
            $this->slug = $this->generateSlug($this->name);
        }
    }

    public function generateSlug(string $string): string
    {
        $slug = Str::slug($string);
        $count = Product::query()
            ->where('slug', $slug)
            ->where('tenant_id', auth()->user()->tenant_id)
            ->count();

        if ($count > 0) {
            $slug .= '-'.($count + 1);
        }

        return $slug;
    }

    public function updatedType(): void
    {
        if ($this->type === 'manufactured') {
            $this->is_purchasable = false;
            $this->is_sellable = true;
            $this->is_stockable = true;
            if ($this->pricing_mode === 'manual') {
                $this->pricing_mode = 'percentage_markup';
                $this->markup_percentage = '50';
            }
        } elseif ($this->type === 'service') {
            $this->is_stockable = false;
        }

        $this->recalculateSteps();
    }

    public function nextStep(): void
    {
        $this->validateCurrentStep();
        $this->currentStep = min($this->currentStep + 1, $this->totalSteps);
        $this->skipManufacturingStep('forward');
    }

    public function previousStep(): void
    {
        $this->currentStep = max($this->currentStep - 1, 1);
        $this->skipManufacturingStep('backward');
    }

    public function goToStep(int $step): void
    {
        if ($step < $this->currentStep) {
            $this->currentStep = $step;
        }
    }

    public function addBomItem(): void
    {
        $this->bomItems[] = [
            'product_id' => null,
            'quantity' => '1',
            'unit_cost' => '0',
            'wastage_percentage' => '0',
        ];
    }

    public function removeBomItem(int $index): void
    {
        unset($this->bomItems[$index]);
        $this->bomItems = array_values($this->bomItems);
        $this->recalculateBomCost();
    }

    public function updatedBomItems($value, $key): void
    {
        // Auto-populate unit cost when a raw material is selected.
        if (str_ends_with((string) $key, '.product_id') && $value) {
            $index = (int) explode('.', (string) $key)[0];
            $material = Product::find($value);

            if ($material) {
                $this->bomItems[$index]['unit_cost'] = (string) $material->cost_price;
            }
        }

        $this->recalculateBomCost();
    }

    public function save(): void
    {
        $service = app(ProductService::class);

        $data = [
            'name' => $this->name,
            'slug' => $this->slug,
            'sku' => $this->sku,
            'type' => $this->type,
            'category_id' => $this->category_id,
            'brand_id' => $this->brand_id,
            'base_unit_id' => $this->base_unit_id,
            'description' => $this->description ?: null,
            'short_description' => $this->short_description ?: null,
            'barcode' => $this->barcode ?: null,
            'cost_price' => $this->cost_price,
            'sell_price' => $this->sell_price,
            'pricing_mode' => $this->pricing_mode,
            'markup_percentage' => $this->markup_percentage ?: null,
            'profit_amount' => $this->profit_amount ?: null,
            'tax_rate' => $this->tax_rate,
            'valuation_method' => $this->valuation_method,
            'reorder_level' => $this->reorder_level,
            'reorder_quantity' => $this->reorder_quantity,
            'is_active' => $this->is_active,
            'is_purchasable' => $this->is_purchasable,
            'is_sellable' => $this->is_sellable,
            'is_stockable' => $this->is_stockable,
            'attribute_ids' => $this->attribute_ids,
        ];

        if ($this->productId) {
            $product = Product::findOrFail($this->productId);
            $this->authorize('update', $product);
            $product = $service->update($product, $data);
            $message = 'Product updated successfully.';
        } else {
            $this->authorize('create', Product::class);
            $product = $service->create($data);
            $message = 'Product created successfully.';
        }

        // Create/update BOM for manufactured products.
        if ($this->type === 'manufactured' && ! empty($this->bomItems)) {
            $bomService = app(BomService::class);
            $existingBom = $product->activeBom;

            $bomData = [
                'product_id' => $product->id,
                'name' => $this->bomName ?: $product->name.' BOM',
                'version' => $this->bomVersion,
                'status' => 'active',
                'items' => array_map(fn ($item) => [
                    'product_id' => $item['product_id'],
                    'quantity' => (float) $item['quantity'],
                    'unit_cost' => (float) $item['unit_cost'],
                    'wastage_percentage' => (float) ($item['wastage_percentage'] ?? 0),
                ], $this->bomItems),
            ];

            if ($existingBom) {
                $bomService->update($existingBom, $bomData);
            } else {
                $bomService->create($bomData);
            }
        }

        $this->dispatch('toast', message: $message, type: 'success');
        $this->redirect(route('products.index'), navigate: true);
    }

    public function render(): View
    {
        $allSteps = [1 => 'Type', 2 => 'Details', 3 => 'Inventory', 4 => 'Manufacturing', 5 => 'Pricing', 6 => 'Review'];

        if ($this->type !== 'manufactured') {
            unset($allSteps[4]);
        }

        return view('livewire.catalog.product-wizard', [
            'steps' => $allSteps,
            'categories' => Category::where('is_active', true)->orderBy('name')->get(),
            'brands' => Brand::where('is_active', true)->orderBy('name')->get(),
            'units' => UnitOfMeasure::where('is_active', true)->orderBy('name')->get(),
            'attributes' => ProductAttribute::orderBy('name')->get(),
            'rawMaterials' => Product::where('is_active', true)
                ->where('is_purchasable', true)
                ->where('type', '!=', 'manufactured')
                ->orderBy('name')
                ->get(),
        ]);
    }

    private function validateCurrentStep(): void
    {
        match ($this->currentStep) {
            1 => $this->validate(['type' => 'required|in:standard,variable,service,bundle,manufactured']),
            2 => $this->validate([
                'name' => 'required|string|max:255',
                // 'slug' => 'required|string|max:255',
                'sku' => 'required|string|max:100',
                'base_unit_id' => 'required|integer|exists:units_of_measure,id',
            ]),
            3 => $this->validate([
                'valuation_method' => 'required|in:fifo,lifo,weighted_average,standard',
            ]),
            4 => $this->validateManufacturingStep(),
            5 => $this->validate([
                'cost_price' => 'required|numeric|min:0',
                'sell_price' => 'required|numeric|min:0',
                'pricing_mode' => 'required|in:manual,percentage_markup,fixed_profit',
            ]),
            default => null,
        };
    }

    private function validateManufacturingStep(): void
    {
        if ($this->type !== 'manufactured') {
            return;
        }

        $this->validate([
            'bomItems' => 'required|array|min:1',
            'bomItems.*.product_id' => 'required|integer|exists:products,id',
            'bomItems.*.quantity' => 'required|numeric|gt:0',
            'bomItems.*.unit_cost' => 'required|numeric|min:0',
        ]);
    }

    private function skipManufacturingStep(string $direction): void
    {
        // Skip step 4 (manufacturing) for non-manufactured products.
        if ($this->currentStep === 4 && $this->type !== 'manufactured') {
            $this->currentStep = $direction === 'forward' ? 5 : 3;
        }
    }

    private function recalculateSteps(): void
    {
        $this->totalSteps = $this->type === 'manufactured' ? 6 : 6;
    }

    public function updatedMarkupPercentage(): void
    {
        $this->recalculateSellPrice();
    }

    public function updatedProfitAmount(): void
    {
        $this->recalculateSellPrice();
    }

    public function updatedCostPrice(): void
    {
        $this->recalculateSellPrice();
    }

    public function updatedPricingMode(): void
    {
        $this->recalculateSellPrice();
    }

    private function recalculateBomCost(): void
    {
        $totalCost = 0;

        foreach ($this->bomItems as $item) {
            $qty = (float) ($item['quantity'] ?? 0);
            $cost = (float) ($item['unit_cost'] ?? 0);
            $wastage = (float) ($item['wastage_percentage'] ?? 0);
            $totalCost += $qty * $cost * (1 + $wastage / 100);
        }

        $this->cost_price = number_format($totalCost, 4, '.', '');
        $this->recalculateSellPrice();
    }

    private function recalculateSellPrice(): void
    {
        $cost = (float) $this->cost_price;

        if ($this->pricing_mode === 'percentage_markup' && $this->markup_percentage !== '') {
            $this->sell_price = number_format($cost * (1 + (float) $this->markup_percentage / 100), 4, '.', '');
        } elseif ($this->pricing_mode === 'fixed_profit' && $this->profit_amount !== '') {
            $this->sell_price = number_format($cost + (float) $this->profit_amount, 4, '.', '');
        }
    }
}
