<?php

namespace Modules\Apartment\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class ChangeInspectorRequest extends FormRequest
{

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'inspector_id' => 'required|exists:users,id',
            'monitoringIds' => 'required|array',
            'comment'=> 'required|string'
        ];
    }

}
