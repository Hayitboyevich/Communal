<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UserUpdateRequest extends FormRequest
{

    public function authorize(): bool
    {
        return true;
    }


    public function rules(): array
    {
        return [
            'name' => 'sometimes',
            'middle_name' => 'sometimes',
            'surname' => 'sometimes',
            'phone' => 'sometimes',
            'pin' => 'sometimes',
            'region_id' => 'sometimes',
            'district_id' => 'sometimes',
            'login' => 'sometimes',
            'password' => 'sometimes',
            'birth_date' => 'sometimes',
            'role_id' => 'sometimes',
            'user_status_id' => 'sometimes',
            'files' => 'sometimes',
            'docs' => 'required|array',
            'docs.*' => 'file|mimes:pdf,zip,doc,docx',
            'image' => 'sometimes',
            'pkcs7' => 'required|string'
        ];
    }
}
