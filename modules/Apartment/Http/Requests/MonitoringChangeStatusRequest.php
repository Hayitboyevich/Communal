<?php

namespace Modules\Apartment\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Modules\Apartment\Enums\MonitoringStatusEnum;

class MonitoringChangeStatusRequest extends FormRequest
{

    public function authorize(): bool
    {
        return true;
    }


    public function rules(): array
    {
        return [
            'monitoring_status_id' => 'required|integer|exists:monitoring_statuses,id',
            'docs' => 'sometimes',
            'images' => 'sometimes',
            'comment' => 'sometimes',
            'is_administrative' => 'sometimes',
            'send_court' => 'sometimes',
        ];
    }

    public function prepareForValidation()
    {
        $this->merge([
            'is_administrative' => $this->monitoring_status_id == MonitoringStatusEnum::ADMINISTRATIVE->value ? true : false,
            'send_court' => $this->monitoring_status_id == MonitoringStatusEnum::COURT->value ? true : false,
        ]);
    }
}
