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
        if (!$this->route('id')) {
            return [
                'monitoring_type_id' => 'required|exists:monitoring_types,id|in:1,2',
                'place_id' => [Rule::requiredIf(fn () => $this->monitoring_type_id == 2),Rule::prohibitedIf(fn () => $this->monitoring_type_id == 1), 'array', 'min:1', 'max:2'],
                'place_id.*' => 'integer|exists:places,id|in:8,9,10',
                'per_page' => 'integer|sometimes',
                'page' => 'integer|sometimes',
                'region_id' => 'integer|sometimes|exists:regions,id',
                'district_id' => 'integer|sometimes|exists:districts,id',
                'company_id' => 'integer|sometimes|exists:companies,id',
                'home_id' => 'integer|sometimes|exists:apartments,home_id',
                'type' => 'sometimes|in:2',
                'is_administrative' => 'sometimes|boolean',
                'street_name' => 'sometimes|string'
            ];
        }
        return [];
    }
}
