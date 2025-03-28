<?php

namespace Modules\Water\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

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
            'full_number' => 'required',
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
}
