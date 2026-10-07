<?php

namespace Modules\Apartment\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Modules\Apartment\Enums\ApartmentsTypeEnum;

class ApartmentFilterRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array|string>
     */
    public function rules(): array
    {
        return [
            'apartment_type' => ['bail','sometimes', Rule::enum(ApartmentsTypeEnum::class)->only([
                ApartmentsTypeEnum::BOSHQARUVSIZ, ApartmentsTypeEnum::OOB
            ])],
            'company_id' => 'bail|sometimes|integer|exists:companies,company_id',
            'region_id' => ['bail', Rule::requiredIf(fn() => $this->filled('apartment_type')),'integer', 'exists:regions,id'],
            'district_id' => ['bail', Rule::requiredIf(fn() => $this->filled('apartment_type') && $this->filled('region_id')),'integer',
                Rule::exists('districts', 'id')->where('region_id', $this->input('region_id'))],
        ];
    }
}
