<?php

namespace Modules\Apartment\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class ProgramRequest extends FormRequest
{

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => 'required',
            'user_id' => 'required',
        ];
    }

    public function prepareForValidation()
    {
        $this->merge([
            'user_id' => Auth::id(),
        ]);
    }

}
