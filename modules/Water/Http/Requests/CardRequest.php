<?php

namespace Modules\Water\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Exceptions\HttpResponseException;

class CardRequest extends FormRequest
{

    public function authorize(): bool
    {
        return true;
    }


    public function rules(): array
    {
        return [
            'user_id' => 'required',
            'full_number' => 'required|unique:cards,full_number',
            'first6' => 'required',
            'last4' => 'required',
            'expMonth' => 'required',
            'expYear' => 'required',
            'bin' => 'sometimes',
            'cardHolder' => 'required',
            'bankName' => 'required',
            'bankCode' => 'required',
            'token' => 'required',
            'hashPan' => 'required',
            'processing' => 'required',
            'type' => 'required',
            'phone' => 'required',
            'status' => 'required'
        ];
    }
    protected function prepareForValidation()
    {
        $this->merge([
            'user_id' => Auth::id(),
            'status' => false
        ]);
    }
    public function messages(): array
    {
        return [
            'full_number.required' => 'Karta raqami majburiy.',
            'full_number.unique' => 'Bu karta raqami allaqachon mavjud.',
        ];
    }

    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(response()->json([
            'success' => false,
            'message' => 'Validation errors',
            'errors' => $validator->errors()
        ], 422));
    }
}
