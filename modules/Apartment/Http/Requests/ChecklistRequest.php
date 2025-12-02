<?php

namespace Modules\Apartment\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class ChecklistRequest extends FormRequest
{

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'work_type_id' => 'required|integer|exists:work_types,id',
            'name' => 'required|string',
        ];
    }

}
