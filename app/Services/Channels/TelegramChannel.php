<?php

namespace App\Services\Channels;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Telegram Bot API driver. Docs: https://core.telegram.org/bots/api
 */
class TelegramChannel implements ChannelInterface
{
    public function name(): string
    {
        return 'telegram';
    }

    public function enabled(): bool
    {
        return (bool) config('services.telegram.bot_token');
    }

    public function send(string $recipientId, string $text): ?string
    {
        if (! $this->enabled()) {
            return null;
        }

        try {
            $response = Http::timeout(15)->post($this->base().'/sendMessage', [
                'chat_id' => $recipientId,
                'text' => mb_substr($text, 0, 4000),
            ]);

            if (! $response->successful()) {
                Log::warning('Telegram send failed.', ['status' => $response->status()]);

                return null;
            }

            $id = $response->json('result.message_id');

            return $id !== null ? (string) $id : null;
        } catch (\Throwable $e) {
            Log::warning('Telegram send failed.', ['error' => $e->getMessage()]);

            return null;
        }
    }

    /**
     * Verify the webhook secret token header against configured secret.
     */
    public function validSecret(?string $provided): bool
    {
        $secret = (string) config('services.telegram.webhook_secret');

        if ($secret === '' || $provided === null) {
            return false;
        }

        return hash_equals($secret, $provided);
    }

    /**
     * @return array<int, array{from: string, name: string, body: string, message_id: string}>
     */
    public function parseWebhook(array $payload): array
    {
        $message = $payload['message'] ?? $payload['edited_message'] ?? null;

        if (! is_array($message) || ! isset($message['text'], $message['from']['id'])) {
            return [];
        }

        $from = $message['from'];
        $name = trim(($from['first_name'] ?? '').' '.($from['last_name'] ?? '')) ?: ($from['username'] ?? (string) $from['id']);

        return [[
            'from' => (string) $from['id'],
            'name' => $name,
            'body' => (string) $message['text'],
            'message_id' => (string) ($message['message_id'] ?? ''),
        ]];
    }

    protected function base(): string
    {
        return 'https://api.telegram.org/bot'.config('services.telegram.bot_token');
    }
}
