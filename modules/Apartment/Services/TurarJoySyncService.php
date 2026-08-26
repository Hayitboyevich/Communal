<?php

namespace Modules\Apartment\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class TurarJoySyncService
{
    public function sync(int $homeId, string $source = 'create')
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

            $this->notifyTelegram($homeId, $source, $response->successful(), $response->status(), $response->json() ?? $response->body());

            return $response;
        } catch (\Exception $exception) {
            Log::info($exception->getMessage());
            $this->notifyTelegram($homeId, $source, false, null, $exception->getMessage());
            return null;
        }
    }

    private function notifyTelegram(int $homeId, string $source, bool $sent, ?int $statusCode, $result = null): void
    {
        try {
            $emoji = $sent ? '✅' : '❌';
            $status = $sent ? 'Yuborildi' : 'Yuborilmadi';

            $text = "{$emoji} <b>Turar joy sync</b>\n"
                . "🏠 Home ID: <code>{$homeId}</code>\n"
                . "🔖 Funksiya: <b>{$source}</b>\n"
                . "📌 Holat: <b>{$status}</b>\n"
                . "🕒 Vaqt: " . now()->format('Y-m-d H:i:s');

            if ($statusCode) {
                $text .= "\n🔢 Status kod: <code>{$statusCode}</code>";
            }

            if (!empty($result)) {
                $resultText = is_string($result)
                    ? $result
                    : json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);

                $text .= "\n📩 Javob:\n<pre>" . htmlspecialchars(mb_substr($resultText, 0, 1000), ENT_QUOTES) . "</pre>";
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
