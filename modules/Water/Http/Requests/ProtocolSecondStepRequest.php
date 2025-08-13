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
            'user_type' => 'sometimes',
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
            'images' => 'sometimes',
            'protocol_status_id' => 'sometimes',
            'step' => 'required|integer',
            'additional_files' => 'sometimes',
            'additional_comment' => 'sometimes',
            'is_finished' => 'sometimes',
            'defect_id' => 'sometimes',
            'defect_comment' => 'sometimes',
        ];
    }

    public function prepareForValidation()
    {
        $this->merge([
            'step' => 2
        ]);
    }


}
