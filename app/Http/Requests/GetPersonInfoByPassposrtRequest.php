<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class GetPersonInfoByPassposrtRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        if ($this->has('pin')) {
            $this->merge([
                'pin' => mb_strtoupper(trim($this->input('pin')), 'UTF-8'),
            ]);
        }
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'pin' => [
                'bail',
                'required',
                'string',
                'not_regex:/\p{Cyrillic}/u',
                'regex:/^([A-Z]{2}\d{7}|\d{14})$/',
            ],
            'birth_date' => 'required',
        ];
    }

    public function messages(): array
    {
        return [
            'pin.required'  => 'Pasport seriyasi yoki JSHSHIR kiriting',
            'pin.not_regex' => 'Lotin harflarida kiriting (masalan: AA0000000)',
            'pin.regex'     => 'Format noto\'g\'ri. Namuna: AA0000000 yoki 00000000000000 (14 ta)',
        ];
    }

    public function identifierType(): ?string
    {
        return preg_match('/^\d{14}$/', (string) $this->input('pin')) ? null : 'd';
    }
}
