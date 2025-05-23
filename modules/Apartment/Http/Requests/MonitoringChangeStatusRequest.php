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
            'send_mib' => 'sometimes',
            'send_chora' => 'sometimes',
            'type' => 'required'
        ];
    }

    public function prepareForValidation()
    {
        $data = [];
        switch ($this->monitoring_status_id) {
            case MonitoringStatusEnum::ADMINISTRATIVE->value:
                $data['is_administrative'] = true;
                break;

            case MonitoringStatusEnum::COURT->value:
                $data['send_court'] = true;
                break;

            case MonitoringStatusEnum::MIB->value:
                $data['send_mib'] = true;
                break;
            case MonitoringStatusEnum::FIXED->value:
                $data['send_chora'] = true;
                break;
        }

        $data['type'] = 2;

        $this->merge($data);
    }
}
