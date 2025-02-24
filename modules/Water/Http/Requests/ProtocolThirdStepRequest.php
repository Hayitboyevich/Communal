<?php

namespace Modules\Water\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

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
            'images' => 'required|array',
        ];
    }

    public function prepareForValidation()
    {
        $this->merge([
            'is_finished' => true
        ]);
    }
}
