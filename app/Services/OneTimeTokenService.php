<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

class OneTimeTokenService
{
    /**
     * Token yaratadi.
     *
     * @param string $purpose  Token nima uchun ekani (masalan: 'download', 'email-verify')
     * @param array  $payload  Token orqasida yashiringan ma'lumot (masalan: ['user_id' => 5])
     * @param int    $ttl      Token necha soniya yashashi (standart: 30)
     * @return string          Foydalanuvchiga beriladigan tasodifiy token
     */
    public static function issue(string $purpose, array $payload, int $ttl = 30): string
    {
        $token = Str::random(48);

        Cache::put(
            self::key($purpose, $token),   // kalit: "ott:download:9f86d08..."
            $payload,                      // qiymat: ['file_id' => 42]
            now()->addSeconds($ttl)        // qachon o'chadi
        );

        return $token;
    }

    /**
     * @param string      $purpose  issue() da berilgan maqsad bilan bir xil bo'lishi shart
     * @param string|null $token    Foydalanuvchidan kelgan token (null bo'lishi mumkin)
     * @return array|null           Token to'g'ri bo'lsa payload, aks holda null
     */
    public static function consume(string $purpose, ?string $token): ?array
    {
        if (! $token) {
            return null;
        }

        $key = self::key($purpose, $token);

        return Cache::lock("$key:lock", 5)   // lock nomi va 5 soniya maksimal muddat
        ->get(fn () => Cache::pull($key)) // o'qib, darhol o'chiradi
            ?: null;
    }

    /**
     * Keshdagi kalitni yasaydi.
     *
     * @param string $purpose  Maqsad
     * @param string $token    Asl token
     * @return string          "ott:{purpose}:{sha256 xesh}"
     */
    private static function key(string $purpose, string $token): string
    {
        return "ott:$purpose:" . hash('sha256', $token);
    }
}
