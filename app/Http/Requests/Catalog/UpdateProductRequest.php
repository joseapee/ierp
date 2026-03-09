<?php

declare(strict_types=1);

namespace App\Http\Requests\Catalog;

use Illuminate\Foundation\Http\FormRequest;

class UpdateProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['required', 'string', 'max:255'],
            'sku' => ['required', 'string', 'max:100'],
            'type' => ['required', 'string', 'in:standard,variable,service,bundle,manufactured'],
            'category_id' => ['nullable', 'integer', 'exists:categories,id'],
            'brand_id' => ['nullable', 'integer', 'exists:brands,id'],
            'base_unit_id' => ['required', 'integer', 'exists:units_of_measure,id'],
            'description' => ['nullable', 'string'],
            'short_description' => ['nullable', 'string', 'max:500'],
            'image' => ['nullable', 'string', 'max:255'],
            'barcode' => ['nullable', 'string', 'max:50'],
            'cost_price' => ['numeric', 'min:0'],
            'sell_price' => ['numeric', 'min:0'],
            'pricing_mode' => ['required', 'string', 'in:manual,percentage_markup,fixed_profit'],
            'markup_percentage' => ['nullable', 'numeric', 'min:0', 'max:9999'],
            'profit_amount' => ['nullable', 'numeric', 'min:0'],
            'tax_rate' => ['numeric', 'min:0', 'max:100'],
            'valuation_method' => ['required', 'string', 'in:fifo,lifo,weighted_average,standard'],
            'reorder_level' => ['numeric', 'min:0'],
            'reorder_quantity' => ['numeric', 'min:0'],
            'is_active' => ['boolean'],
            'is_purchasable' => ['boolean'],
            'is_sellable' => ['boolean'],
            'is_stockable' => ['boolean'],
            'attribute_ids' => ['nullable', 'array'],
            'attribute_ids.*' => ['integer', 'exists:product_attributes,id'],
        ];
    }
}
