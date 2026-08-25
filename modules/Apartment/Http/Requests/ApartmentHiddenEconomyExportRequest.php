<?php

namespace Modules\Apartment\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Modules\Apartment\Http\Enums\ApartmentHiddenEconomyTypeEnum;

class ApartmentHiddenEconomyExportRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'type' => ['sometimes', 'integer', 'in:' . implode(',', ApartmentHiddenEconomyTypeEnum::types())],
            'status' => 'sometimes',
            'district_id' => 'integer|sometimes|exists:districts,id',
            'company_id' => 'integer|sometimes|exists:companies,id',
            'home_id' => 'integer|sometimes|exists:apartments,home_id',
        ];
    }
}
