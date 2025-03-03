<?php

namespace Modules\Water\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Modules\Water\Enums\ProtocolStatusEnum;

class ProtocolSecondStepRequest extends FormRequest
{

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'user_type' => 'required|integer|between:1,2',
            'inn' => 'required|string',
            'enterprise_name' => 'required|string',
            'pin' => 'required|string',
            'birth_date' => 'required|date',
            'functionary_name' => 'required|string',
            'phone' => 'required|string',
            'self_government_name' => 'required|string',
            'inspector_name' => 'required|string',
            'participant_name' => 'required|string',
            'files' => 'required|array',
            'protocol_status_id' => 'required|integer',
        ];
    }

    public function prepareForValidation()
    {
        $this->merge([
            'protocol_status_id' => $this->protocol_status_id == ProtocolStatusEnum::NOT_DEFECT->value
                ? ProtocolStatusEnum::CONFIRM_NOT_DEFECT->value
                : ProtocolStatusEnum::FORMING->value
        ]);
    }
}
