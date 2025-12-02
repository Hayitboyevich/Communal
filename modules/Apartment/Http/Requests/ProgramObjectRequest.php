<?php

namespace Modules\Apartment\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class ProgramObjectRequest extends FormRequest
{

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'inspector_id' => 'required',
            'status' => 'required',
            'region_id' => 'required',
            'district_id' => 'required',
            'cadastral_number' => 'sometimes',
            'responsible_pin' => 'sometimes',
            'birth_date' => 'sometimes',
            'full_name' => 'sometimes',
            'address' => 'sometimes',
        ];
    }

    public function prepareForValidation()
    {
        $this->merge([
            'inspector_id' => Auth::id(),
            'status' => 1,
        ]);
    }
}
