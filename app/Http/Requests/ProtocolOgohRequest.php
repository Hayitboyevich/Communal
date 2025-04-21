<?php

namespace App\Http\Requests;

use App\Enums\UserRoleEnum;
use App\Models\District;
use App\Models\Region;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Modules\Water\Const\CategoryType;
use Modules\Water\Const\Step;
use Modules\Water\Enums\ProtocolStatusEnum;

class ProtocolOgohRequest extends FormRequest
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
            'role_id' => 'sometimes',
        ];
    }

    public function prepareForValidation()
    {
        $this->merge([
            'protocol_status_id' =>  ProtocolStatusEnum::ENTER_RESULT->value ,
            'step' => Step::ONE,
            'category' => CategoryType::MONITORING,
            'region_id' => Region::query()->where('soato', $this->region)->first()->id ,
            'district_id' => District::query()->where('soato', $this->district)->first()->id ,
        ]);
    }
}
