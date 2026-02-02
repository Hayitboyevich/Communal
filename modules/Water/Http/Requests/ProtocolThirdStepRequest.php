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
            'step' => 'required|integer',
            'category' => 'required|integer',
        ];
    }

    public function prepareForValidation()
    {
        $this->merge([
            'protocol_status_id' => ProtocolStatusEnum::FORMED->value,
            'step' => 3,
            'category' => 2
        ]);
    }
}
