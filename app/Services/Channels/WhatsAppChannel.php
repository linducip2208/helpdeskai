<?php

namespace App\Services\Channels;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Meta WhatsApp Cloud API driver.
 * Docs: https://developers.facebook.com/docs/whatsapp/cloud-api
 */
class WhatsAppChannel implements ChannelInterface
{
    public function name(): string
    {
        return 'whatsapp';
    }

    public function enabled(): bool
    {
        return (bool) config('services.whatsapp.token')
            && (bool) config('services.whatsapp.phone_number_id');
    }

    public function send(string $recipientId, string $text): ?string
    {
        if (! $this->enabled()) {
            return null;
        }

        try {
            $response = Http::timeout(15)
                ->withToken((string) config('services.whatsapp.token'))
                ->post($this->base().'/messages', [
                    'messaging_product' => 'whatsapp',
                    'to' => $recipientId,
                    'type' => 'text',
                    'text' => ['preview_url' => false, 'body' => mb_substr($text, 0, 4000)],
                ]);

            if (! $response->successful()) {
                Log::warning('WhatsApp send failed.', ['status' => $response->status()]);

                return null;
            }

            return $response->json('messages.0.id');
        } catch (\Throwable $e) {
            Log::warning('WhatsApp send failed.', ['error' => $e->getMessage()]);

            return null;
        }
    }

    /**
     * Verify X-Hub-Signature-256 webhook signature (HMAC-SHA256 of raw body).
     */
    public function validSignature(string $rawBody, ?string $signature): bool
    {
        $secret = (string) config('services.whatsapp.app_secret');

        if ($secret === '' || $signature === null) {
            return false;
        }

        $expected = 'sha256='.hash_hmac('sha256', $rawBody, $secret);

        return hash_equals($expected, $signature);
    }

    /**
     * Parse Meta webhook payload into normalized messages.
     *
     * @return array<int, array{from: string, name: string, body: string, message_id: string}>
     */
    public function parseWebhook(array $payload): array
    {
        $out = [];

        foreach ($payload['entry'] ?? [] as $entry) {
            foreach ($entry['changes'] ?? [] as $change) {
                $value = $change['value'] ?? [];
                $contacts = [];
                foreach ($value['contacts'] ?? [] as $contact) {
                    $contacts[$contact['wa_id'] ?? ''] = $contact['profile']['name'] ?? '';
                }
                foreach ($value['messages'] ?? [] as $message) {
                    if (($message['type'] ?? '') !== 'text') {
                        continue;
                    }
                    $from = (string) ($message['from'] ?? '');
                    $out[] = [
                        'from' => $from,
                        'name' => $contacts[$from] ?? $from,
                        'body' => (string) ($message['text']['body'] ?? ''),
                        'message_id' => (string) ($message['id'] ?? ''),
                    ];
                }
            }
        }

        return $out;
    }

    protected function base(): string
    {
        return 'https://graph.facebook.com/v21.0/'.config('services.whatsapp.phone_number_id');
    }
}
