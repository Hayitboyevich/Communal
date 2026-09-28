<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

class OneTimeTokenService
{
    /**
     * @param string $purpose
     * @param array  $payload
     * @param int    $ttl
     * @return string
     */
    public static function issue(string $purpose, array $payload, int $ttl = 30): string
    {
        $token = Str::random(48);

        Cache::put(
            self::key($purpose, $token),
            $payload,
            now()->addSeconds($ttl)
        );

        return $token;
    }

    /**
     * @param string      $purpose
     * @param string|null $token
     * @return array|null
     */
    public static function consume(string $purpose, ?string $token): ?array
    {
        if (! $token) {
            return null;
        }

        $key = self::key($purpose, $token);

        return Cache::lock("$key:lock", 5)
        ->get(fn () => Cache::pull($key))
            ?: null;
    }

    /**
     * @param string $purpose
     * @param string $token
     * @return string
     */
    private static function key(string $purpose, string $token): string
    {
        return "ott:$purpose:" . hash('sha256', $token);
    }
}
