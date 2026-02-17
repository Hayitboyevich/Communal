<?php

namespace Modules\Apartment\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class LetterRequest extends FormRequest
{

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'monitoring_id' => 'required|exists:monitorings,id|unique:letters,monitoring_id',
            'regulation_id' => 'required|exists:regulations,id|unique:letters,regulation_id',
            'region_id' => 'required|exists:regions,id',
            'district_id' => 'required|exists:districts,id',
            'address' => 'required|string',
            'fish'=> 'required|string',
            'inspector_id' => 'required|exists:users,id',
            'status' => 'required|in:1,2',
        ];
    }

    protected function prepareForValidation()
    {
        $this->merge([
            'inspector_id' => Auth::id(),
            'status' => 1
        ]);
    }

}
