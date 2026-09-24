<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Egov'dan kelgan ish joyi javobi tarixi. Har bir request emas, faqat positions
 * o'zgargan holat yangi qator bo'ladi; o'zgarmasa oxirgi qatorning last_seen_at'i yangilanadi.
 */
class UserWorkPlaceSnapshot extends Model
{
    public $timestamps = false;

    protected $guarded = false;

    protected $casts = [
        'response'      => 'array',
        'first_seen_at' => 'datetime',
        'last_seen_at'  => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Faqat positions'dan hash olinadi: javobdagi jsonrpc id har safar tasodifiy.
     * Tartib va kalitlar ketma-ketligi hashga ta'sir qilmasligi uchun normallashtiriladi.
     */
    public static function hashPositions(array $positions): string
    {
        $positions = json_decode(json_encode(array_values($positions)), true) ?? [];

        foreach ($positions as &$position) {
            ksort($position);
        }
        unset($position);

        usort($positions, fn ($a, $b) => [(string) ($a['org_tin'] ?? ''), (string) ($a['position_id'] ?? '')]
            <=> [(string) ($b['org_tin'] ?? ''), (string) ($b['position_id'] ?? '')]);

        return md5(json_encode($positions, JSON_UNESCAPED_UNICODE));
    }
}
