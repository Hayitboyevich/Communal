<?php

namespace Modules\Water\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Modules\Water\Enums\ProtocolStatusEnum;

class ProtocolThirdStepRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'defect_information' => 'required|string',
            'comment' => 'required|string',
            'deadline' => 'required|date',
            'image_files' => 'required|array',
            'is_finished' => 'required|boolean',
        ];
    }

    public function prepareForValidation()
    {
        $this->merge([
            'is_finished' => true,
            'image_files' => $this->images,
            'protocol_status_id' => ProtocolStatusEnum::CONFIRM_RESULT->value
        ]);
    }
}
