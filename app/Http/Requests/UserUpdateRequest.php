<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UserUpdateRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
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
            'images' => 'sometimes',
            'image' => 'sometimes',
        ];
    }
}
