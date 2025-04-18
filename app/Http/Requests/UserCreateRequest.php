<?php

namespace App\Http\Requests;

use App\Enums\UserStatusEnum;
use Illuminate\Foundation\Http\FormRequest;

class UserCreateRequest extends FormRequest
{

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'middle_name' => 'required|string|max:255',
            'surname' => 'required|string|max:255',
            'phone' => 'required|string|max:255',
            'pin' => 'required|string|max:255|unique:users',
            'region_id' => 'required|integer|exists:regions,id',
            'district_id' => 'required|integer|exists:districts,id',
            'login' => 'required|string|max:255|unique:users',
            'password' => 'required|string',
            'birth_date' => 'required|date',
            'role_id' => 'required|array',
            'user_status_id' => 'required',
            'files' => 'sometimes',
            'images' => 'sometimes',
            'image' => 'sometimes|image|mimes:jpeg,png,jpg,gif|max:2048',
        ];
    }

    protected function prepareForValidation()
    {
        $this->merge([
            'login' => $this->passport,
            'password' => $this->pin,
            'user_status_id' => UserStatusEnum::ACTIVE->value
        ]);

    }
}
