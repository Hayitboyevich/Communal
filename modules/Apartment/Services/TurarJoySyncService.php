<?php

namespace Modules\Apartment\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class TurarJoySyncService
{
    public function sync(int $homeId)
    {
        try {
            $response = Http::withBasicAuth(
                config('services.turar_joy_sync.login'),
                config('services.turar_joy_sync.password')
            )->withOptions([
                'query' => [
                    'home_id' => $homeId,
                ],
            ])->post(config('services.turar_joy_sync.url'));

            $this->notifyTelegram($homeId, $response->successful(), $response->successful() ? null : $response->body());

            return $response;
        } catch (\Exception $exception) {
            Log::info($exception->getMessage());
            $this->notifyTelegram($homeId, false, $exception->getMessage());
            return null;
        }
    }

    private function notifyTelegram(int $homeId, bool $sent, ?string $error = null): void
    {
        try {
            $emoji = $sent ? '✅' : '❌';
            $status = $sent ? 'Yuborildi' : 'Yuborilmadi';

            $text = "{$emoji} <b>Turar joy sync</b>\n"
                . "🏠 Home ID: <code>{$homeId}</code>\n"
                . "📌 Holat: <b>{$status}</b>\n"
                . "🕒 Vaqt: " . now()->format('Y-m-d H:i:s');

            if ($error) {
                $text .= "\n⚠️ Xato: <code>" . htmlspecialchars(mb_substr($error, 0, 500), ENT_QUOTES) . "</code>";
            }

            Http::post('https://api.telegram.org/bot' . config('services.turar_joy_sync.telegram_bot_token') . '/sendMessage', [
                'chat_id' => config('services.turar_joy_sync.telegram_channel_id'),
                'text' => $text,
                'parse_mode' => 'HTML',
            ]);
        } catch (\Exception $exception) {
            Log::info($exception->getMessage());
        }
    }
}
