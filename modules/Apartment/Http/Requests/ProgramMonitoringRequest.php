<?php

namespace Modules\Apartment\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class ProgramMonitoringRequest extends FormRequest
{

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'images' => 'array',
            'program_object_id' => 'integer|exists:program_objects,id',
            'lat' => 'required|string',
            'long' => 'required|string',
            'checklists' => 'array',
        ];
    }

}
