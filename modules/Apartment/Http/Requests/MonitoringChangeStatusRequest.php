<?php

namespace Modules\Apartment\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

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
            'comment' => 'sometimes'
        ];
    }
}
