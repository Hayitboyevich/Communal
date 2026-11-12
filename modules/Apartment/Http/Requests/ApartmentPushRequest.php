<?php

namespace Modules\Apartment\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Validation\Rule;
use Modules\Apartment\Enums\ApartmentsTypeEnum;

class ApartmentPushRequest extends FormRequest
{
    public const MAX_ITEMS = 500;

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'apartments' => ['required', 'array', 'min:1', 'max:' . self::MAX_ITEMS],
            'apartments.*.home_id' => ['required', 'integer', 'min:1', 'distinct'],
            'apartments.*.company_id' => ['required', 'integer'],
            'apartments.*.street_id' => ['nullable', 'integer'],
            'apartments.*.street_name' => ['nullable', 'string', 'max:255'],
            'apartments.*.home_name' => ['nullable', 'string', 'max:255'],
            'apartments.*.apartment_type' => ['nullable', 'integer', Rule::enum(ApartmentsTypeEnum::class)],
            'apartments.*.region_soato' => ['nullable', 'string', 'max:20'],
            'apartments.*.district_soato' => ['nullable', 'string', 'max:20'],
            'apartments.*.cadastral_number' => ['nullable', 'string', 'max:64'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $items = $this->normalizeItems();

        if (!is_array($items)) return;

        // Soato raqam ko'rinishida kelsa ham string sifatida saqlaymiz

        foreach ($items as $i => $item) {
            if (!is_array($item)) continue;
            foreach (['region_soato', 'district_soato', 'cadastral_number'] as $key) {
                if (isset($item[$key]) && (is_int($item[$key]) || is_float($item[$key]))) {
                    $items[$i][$key] = (string) $item[$key];
                }
            }
        }

        $this->merge(['apartments' => $items]);
    }

    /**
     * Uchala formatni bitta ko'rinishga keltiradi — apartments: [ {...}, {...} ]
     *  1) { "home_id": ..., ... }                 — kalitsiz bitta obyekt
     *  2) { "apartments": { "home_id": ..., ... } } — apartments ichida bitta obyekt
     *  3) { "apartments": [ {...}, {...} ] }       — massiv (o'zgarishsiz)
     */
    private function normalizeItems(): mixed
    {
        // 1-holat: apartments yo'q, uy ma'lumoti to'g'ridan-to'g'ri body'da
        if (!$this->has('apartments')) {
            return $this->has('home_id') ? [$this->all()] : null;
        }

        $items = $this->input('apartments');

        // 2-holat: apartments ichida massiv emas, bitta obyekt
        if (is_array($items) && !array_is_list($items)) {
            return [$items];
        }

        // 3-holat: massiv (yoki noto'g'ri tur — validatsiya o'zi xato beradi)
        return $items;
    }

    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(response()->json([
            'success' => false,
            'message' => 'Validation error',
            'code' => 422,
            'data' => $validator->errors(),
        ], 422));
    }
}
