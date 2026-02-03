<?php

namespace Modules\Apartment\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Support\Facades\Auth;
use Modules\Water\Const\Step;
use Modules\Water\Enums\ProtocolStatusEnum;

class MonitoringAdminRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }


    public function rules(): array
    {
        return [
            'id' => 'required|integer|exists:monitorings,id',
            'monitoring_type_id' => 'sometimes|integer|exists:monitoring_types,id',
            'deadline' => 'sometimes',
            'comment' => 'required',
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
