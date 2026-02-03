<?php

namespace Modules\Apartment\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class ProgramObjectEditRequest extends FormRequest
{

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'id' => 'required|integer|exists:program_objects,id',
            'region_id' => 'sometimes',
            'district_id' => 'sometimes',
            'quarter_name' => 'sometimes',
            'street_name' => 'sometimes',
            'apartment_number' => 'sometimes',
        ];
    }

}
