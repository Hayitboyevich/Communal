<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Modules\Water\Enums\ProtocolStatusEnum;

class ProtocolChangeRequest extends FormRequest
{

    public function authorize(): bool
    {
        return true;
    }


    public function rules(): array
    {
        return [
            'comment' => 'required|string',
            'protocol_status_id' => 'required|exists:protocol_statuses,id',
            'images' => 'sometimes',
            'files' => 'sometimes',
        ];
    }

    public function prepareForValidation()
    {
        $this->merge([
            'protocol_status_id' => $this->protocol_status_id == ProtocolStatusEnum::CONFIRMED->value
                ? ProtocolStatusEnum::CONFIRM_RESULT->value
                : $this->protocol_status_id,
        ]);
    }
}
