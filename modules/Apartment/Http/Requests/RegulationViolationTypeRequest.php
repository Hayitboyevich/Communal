<?php

namespace Modules\Apartment\Http\Requests;

use App\Enums\UserRoleEnum;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class RegulationViolationTypeRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return (int) auth()->user()->getRoleFromToken() === UserRoleEnum::VIOLATION_TYPE_VIEWER->value;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'regulation_id' => ['required', 'integer', Rule::exists('regulations', 'id')->where('editable_violation_type', true)],
            'violation_type_id' => 'required|integer|exists:violation_types,id',
        ];
    }
}
