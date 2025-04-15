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
            'inn' => 'sometimes',
            'enterprise_name' => 'sometimes',
            'pin' => 'sometimes',
            'birth_date' => 'sometimes',
            'functionary_name' => 'sometimes',
            'phone' => 'sometimes',
            'self_government_name' => 'sometimes',
            'inspector_name' => 'sometimes',
            'participant_name' => 'sometimes',
            'files' => 'sometimes',
            'protocol_status_id' => 'sometimes',
            'step' => 'required|integer',
            'additional_files' => 'sometimes',
            'additional_comment' => 'sometimes'
        ];
    }

    public function prepareForValidation()
    {
        $this->merge([
            'protocol_status_id' => $this->protocol_status_id == ProtocolStatusEnum::NOT_DEFECT->value
                ? ProtocolStatusEnum::CONFIRM_NOT_DEFECT->value
                : ProtocolStatusEnum::FORMING->value,
            'step' => 2
        ]);
    }
}
