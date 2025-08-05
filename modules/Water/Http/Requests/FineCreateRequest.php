<?php

namespace Modules\Water\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Exceptions\HttpResponseException;

class FineCreateRequest extends FormRequest
{

    public function authorize(): bool
    {
        return true;
    }


    public function rules(): array
    {
        return [
            'guid' => 'required|integer',
            'parent_id' => 'required|integer',
            'created_time' => 'required',
            'updated_time' => 'required',
            'region_id' => 'required',
            'district_id' => 'required',
            'protocol_article_part' => 'sometimes',
            'inspector_pinpp' => 'sometimes',
            'series' => 'required',
            'number' => 'required',
            'decision_series' => 'sometimes',
            'decision_number' => 'sometimes',
            'status' => 'sometimes',
            'status_name' => 'sometimes',
            'last_name' => 'sometimes',
            'first_name' => 'sometimes',
            'second_name' => 'sometimes',
            'document_series' => 'sometimes',
            'document_number' => 'sometimes',
            'pinpp' => 'sometimes',
            'birth_date' => 'sometimes',
            'employment_place' => 'sometimes',
            'employment_position' => 'sometimes',
            'execution_date' => 'sometimes',
            'main_punishment_type' => 'sometimes',
            'main_punishment_amount' => 'sometimes',
            'resolution_organ' => 'sometimes',
            'adm_case_organ' => 'sometimes',
            'resolution_consider_info' => 'sometimes',
            'paid_amount' => 'sometimes',
            'decision_status' => 'sometimes',
            'project_id' => 'required'
        ];
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
