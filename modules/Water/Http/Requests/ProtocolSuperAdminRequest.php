<?php

namespace Modules\Water\Http\Requests;

use App\Enums\UserRoleEnum;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Modules\Water\Const\CategoryType;
use Modules\Water\Const\Step;
use Modules\Water\Enums\ProtocolStatusEnum;
use Modules\Water\Models\Protocol;

class ProtocolSuperAdminRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = Auth::guard('api')->user();
        if (!$user) {
            return false;
        }
        return $user->getRoleFromToken() === UserRoleEnum::SUPER_ADMIN->value;
    }

    public function rules(): array
    {
        return [
            'id' => [
                'required',
                'integer',
                'exists:protocols,id',
                function ($attribute, $value, $fail) {
                    $protocol = Protocol::find($value);
                    if ($protocol && $protocol->step !== Step::ONE) {
                        $fail('Bu protocolda yozma ko\'rsatmasi mavjud.');
                    }
                },
            ],
            'protocol_type_id' => 'sometimes|integer|exists:protocol_types,id',
            'deadline' => 'sometimes|date',
            'comment' => 'required|string',
        ];
    }

}
