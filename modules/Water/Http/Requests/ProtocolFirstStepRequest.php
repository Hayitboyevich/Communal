<?php

namespace Modules\Water\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Modules\Water\Enums\ProtocolStatusEnum;

class ProtocolFirstStepRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'protocol_type_id' => 'required|integer|exists:protocol_types,id',
            'protocol_status_id' => 'required|integer|exists:protocol_statuses,id',
            'region_id' => 'required|integer|exists:regions,id',
            'district_id' => 'required|integer|exists:districts,id',
            'address' => 'required|string',
            'description' => 'required|string',
            'lat' => 'required|numeric',
            'long' => 'required|numeric',
            'step' => 'required|integer',
            'images' => 'required|array',
        ];
    }

    public function prepareForValidation()
    {
        $this->merge([
            'protocol_status_id' => ProtocolStatusEnum::ENTER_RESULT->value,
            'step' => 1
        ]);
    }
}
