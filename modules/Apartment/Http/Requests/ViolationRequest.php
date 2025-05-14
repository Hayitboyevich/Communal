<?php

namespace Modules\Apartment\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ViolationRequest extends FormRequest
{

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'violations.*' => 'required|array',
        ];
    }
}
