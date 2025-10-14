<?php

namespace App\Http\Requests;

use App\Models\District;
use App\Models\Place;
use App\Models\Region;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Modules\Apartment\Enums\MonitoringStatusEnum;
use Modules\Apartment\Models\MonitoringType;
use Modules\Apartment\Models\ViolationType;
use Modules\Water\Const\CategoryType;
use Modules\Water\Const\Step;
use Modules\Water\Enums\ProtocolStatusEnum;

class MonitoringMyHomeRequest extends FormRequest
{

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'monitoring_type_id' => 'sometimes',
            'monitoring_base_id' => 'required|integer|exists:monitoring_bases,id',
            'company_id' => 'sometimes',
            'apartment_id' => 'sometimes',
            'region_id' => 'required|integer|exists:regions,id',
            'district_id' => 'required|integer|exists:districts,id',
            'address_commit' => 'sometimes',
            'lat' => 'sometimes',
            'long' => 'sometimes',
            'images' => 'required|array',
            'docs' => 'sometimes',
            'monitoring_status_id' => 'required|integer|exists:monitoring_statuses,id',
            'type' => 'required',
            'category' => 'required',
            'file_path' => 'sometimes',
        ];
    }

    protected function prepareForValidation()
    {
        $violationType = ViolationType::query()->where('my_home_id', $this->monitoring_type_id)->first();
        $place = $violationType ? Place::query()->find($violationType->place_id) : null;
        $monitoringType = $place ? MonitoringType::query()->find($place->monitoring_type_id) : null;

        $this->merge([
            'monitoring_status_id' => MonitoringStatusEnum::NEW->value,
            'monitoring_type_id' => $monitoringType ? $monitoringType->id : null,
            'region_id' => Region::query()->where('soato', $this->region)->first()?->id,
            'district_id' => District::query()->where('soato', $this->district)->first()?->id,
            'step' => Step::ONE,
            'monitoring_base_id' => 5,
            'type' => Step::ONE,
            'category' => Step::TWO,
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
