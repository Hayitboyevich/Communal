<?php

namespace App\Http\Requests;

use App\Models\District;
use App\Models\Region;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Modules\Water\Const\CategoryType;
use Modules\Water\Const\Step;
use Modules\Water\Enums\ProtocolStatusEnum;
use Modules\Water\Models\ProtocolType;

class ProtocolWaterRequest extends FormRequest
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
            'region' => 'required',
            'district' => 'required',
            'region_id' => 'required|integer|exists:regions,id',
            'district_id' => 'required|integer|exists:districts,id',
            'address' => 'required|string',
            'description' => 'required|string',
            'lat' => 'required|numeric',
            'long' => 'required|numeric',
            'step' => 'required|integer',
            'images' => 'required|array',
            'type' => 'required|integer',
            'user_id' => 'required|integer',
            'fish'=> 'required|string',
            'phone_number'=> 'required|string',
            'user_type' => 'sometimes',
            'inn' => 'sometimes',
            'enterprise_name' => 'sometimes',
            'pin' => 'sometimes',
            'birth_date' => 'sometimes',
            'functionary_name' => 'sometimes',
            'phone' => 'sometimes',
            'self_government_name' => 'sometimes',
            'inspector_name' => 'sometimes',
            'participant_name' => 'sometimes',
            'files' => 'sometimes',
        ];
    }

    public function prepareForValidation()
    {
        $this->merge([
            'protocol_status_id' =>  ProtocolStatusEnum::FORMING->value,
            'step' => Step::TWO,
            'type' => 2,
            'category' => CategoryType::MONITORING,
            'region_id' => Region::query()->where('soato', $this->region)->first()->id,
            'district_id' => District::query()->where('soato', $this->district)->first()->id,
        ]);
    }

    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(response()->json([
            'success' => false,
            'message' => 'Validatsiyadan o‘tmadi.',
            'errors' => $validator->errors()
        ], 422));
    }
}
