<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Modules\Water\Const\Step;

class MonitoringCreateSecondRequest extends FormRequest
{

    public function authorize(): bool
    {
        return true;
    }


    public function rules(): array
    {
        return [
            'monitoring_status_id' => 'required|exists:protocol_statuses,id',
            'regulations' => 'sometimes',
        ];
    }

    public function prepareForValidation()
    {
        $this->merge([
            'step' => Step::TWO
        ]);
    }
}
