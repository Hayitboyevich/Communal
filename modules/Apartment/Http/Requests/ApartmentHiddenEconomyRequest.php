<?php

namespace Modules\Apartment\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ApartmentHiddenEconomyRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'monitoring_type_id' => 'required|exists:monitoring_types,id|in:1,2',
            'place_id' => [Rule::requiredIf(fn () => $this->monitoring_type_id == 2),Rule::prohibitedIf(fn () => $this->monitoring_type_id == 1), 'array', 'min:1', 'max:2'],
            'place_id.*' => 'integer|exists:places,id|in:8,9,10',
        ];
    }
}
