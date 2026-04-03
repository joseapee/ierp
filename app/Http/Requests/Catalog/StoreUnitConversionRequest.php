<?php

declare(strict_types=1);

namespace App\Http\Requests\Catalog;

use Illuminate\Foundation\Http\FormRequest;

class StoreUnitConversionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'from_unit_id' => ['required', 'integer', 'exists:units_of_measure,id'],
            'to_unit_id' => ['required', 'integer', 'exists:units_of_measure,id', 'different:from_unit_id'],
            'factor' => ['required', 'numeric', 'gt:0'],
        ];
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        return [
            'to_unit_id.different' => 'The target unit must be different from the source unit.',
            'factor.gt' => 'The conversion factor must be greater than zero.',
        ];
    }
}
