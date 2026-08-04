<?php

namespace Modules\Apartment\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Modules\Apartment\Http\Enums\ApartmentHiddenEconomyTypeEnum;

class CreateHiddenEconomyRequest extends FormRequest
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
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'user_id' => 'required|integer|exists:users,id',
            'apartment_id' => 'required',
            'home_id' => 'required',
            'monitoring_type_id' => 'required|integer|exists:monitoring_types,id|in:1,2',
            'hidden_economy_type' => 'required|integer|in:' . implode(',', ApartmentHiddenEconomyTypeEnum::types()),
//            'place_id' => [Rule::requiredIf(fn () => $this->monitoring_type_id == 2),Rule::prohibitedIf(fn () => $this->monitoring_type_id == 1), 'array', 'min:1', 'max:2'],
//            'place_id.*' => 'integer|exists:places,id|in:8,9,10',
        ];
    }
}
