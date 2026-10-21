<?php

namespace Modules\Water\Http\Requests;

use App\Enums\UserRoleEnum;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Modules\Water\Const\CategoryType;
use Modules\Water\Const\Step;
use Modules\Water\Enums\ProtocolStatusEnum;

class ProtocolFirstStepRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * protocol_id kelsa — editable protokolni tahrirlash rejimi.
     */
    public function isEditMode(): bool
    {
        return $this->filled('protocol_id');
    }

    public function rules(): array
    {
        $r = $this->isEditMode() ? 'sometimes' : 'required';

        return [
            'protocol_id' => [
                'nullable',
                'integer',
                Rule::exists('protocols', 'id')->where('editable', true),
            ],
            'protocol_type_id' => "$r|integer|exists:protocol_types,id",
            'protocol_status_id' => 'required|integer|exists:protocol_statuses,id',
            'region_id' => "$r|integer|exists:regions,id",
            'district_id' => "$r|integer|exists:districts,id",
            'inspector_id' => 'sometimes',
            'address' => "$r|string",
            'description' => "$r|string",
            'lat' => "$r|numeric",
            'long' => "$r|numeric",
            'step' => 'required|integer',
            'images' => "$r|array",
            'type' => "$r|integer",
            'user_id' => "$r|integer",
            'role_id' => 'sometimes',
            'group_type' => 'sometimes|string|in:I,II,III',
        ];
    }

    public function messages(): array
    {
        return [
            'protocol_id.exists' => 'Protokol topilmadi yoki tahrirlashga ruxsat yo\'q (editable = false).',
        ];
    }

    public function prepareForValidation()
    {
        $data = [
            'protocol_status_id' => ProtocolStatusEnum::ENTER_RESULT->value,
            'step' => Step::ONE,
            'category' => CategoryType::MONITORING,
        ];

        // Tahrirlashda inspector_id ni null qilib yubormaslik uchun faqat inspector bo'lsa yozamiz
        if ($this->role_id == UserRoleEnum::INSPECTOR->value) {
            $data['inspector_id'] = Auth::id();
        } elseif (!$this->isEditMode()) {
            $data['inspector_id'] = null;
        }

        $this->merge($data);
    }
}
