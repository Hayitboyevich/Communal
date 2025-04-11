<?php

namespace Modules\Water\Http\Requests;

use App\Enums\UserRoleEnum;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Modules\Water\Enums\ProtocolStatusEnum;

class ProtocolFirstStepRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'protocol_type_id' => 'required|integer|exists:protocol_types,id',
            'protocol_status_id' => 'required|integer|exists:protocol_statuses,id',
            'region_id' => 'required|integer|exists:regions,id',
            'district_id' => 'required|integer|exists:districts,id',
            'inspector_id' => 'sometimes',
            'address' => 'required|string',
            'description' => 'required|string',
            'lat' => 'required|numeric',
            'long' => 'required|numeric',
            'step' => 'required|integer',
            'images' => 'required|array',
            'type' => 'required|integer',
            'user_id' => 'required|integer',
            'role_id' => 'required|integer',
        ];
    }

    public function prepareForValidation()
    {
        $this->merge([
            'protocol_status_id' => $this->role_id == UserRoleEnum::INSPECTOR->value ? ProtocolStatusEnum::ENTER_RESULT->value : ProtocolStatusEnum::NEW->value,
            'inspector_id' => $this->role_id == UserRoleEnum::INSPECTOR->value ? Auth::id() : null,
            'step' => 1,
        ]);
    }
}
