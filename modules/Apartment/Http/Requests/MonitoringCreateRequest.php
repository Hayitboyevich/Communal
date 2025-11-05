<?php

namespace Modules\Apartment\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Support\Facades\Auth;
use Modules\Water\Const\Step;
use Modules\Water\Enums\ProtocolStatusEnum;

class MonitoringCreateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }


    public function rules(): array
    {
        return [
            'monitoring_type_id' => 'required|integer|exists:monitoring_types,id',
            'monitoring_base_id' => 'required|integer|exists:monitoring_bases,id',
            'company_id' => 'sometimes',
            'apartment_id' => 'sometimes',
            'region_id' => 'required|integer|exists:regions,id',
            'district_id' => 'required|integer|exists:districts,id',
            'address_commit' => 'sometimes',
            'lat' => 'required|string',
            'long' => 'required|string',
            'images' => 'required|array',
            'docs' => 'sometimes',
            'user_id' => 'required|integer|exists:users,id',
            'role_id' => 'required|integer|exists:users,id',
            'monitoring_status_id' => 'required|integer|exists:protocol_statuses,id',
            'type' => 'required',
            'bsk_type' => 'sometimes',
            'address' => 'sometimes',
            'category' => 'required',
        ];
    }

    protected function prepareForValidation()
    {
        $user = Auth::user();
        $this->merge([
            'user_id' => Auth::id(),
            'role_id' => $user->getRoleFromToken(),
            'monitoring_status_id' => ProtocolStatusEnum::ENTER_RESULT->value,
            'step' => Step::ONE,
            'type' => Step::ONE,
            'category' => Step::ONE,
        ]);
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
