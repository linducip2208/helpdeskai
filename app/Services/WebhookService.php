<?php

namespace App\Services;

use App\Jobs\DeliverWebhook;
use App\Models\Ticket;
use App\Models\WebhookDelivery;
use App\Models\WebhookEndpoint;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class WebhookService
{
    /**
     * Queue signed deliveries for all active endpoints subscribed to the event.
     */
    public function dispatch(string $event, Ticket $ticket, array $extra = []): void
    {
        if (! in_array($event, WebhookEndpoint::EVENTS, true)) {
            return;
        }

        $endpoints = WebhookEndpoint::where('is_active', true)->get()->filter(
            fn (WebhookEndpoint $e) => in_array($event, $e->eventList(), true)
        );

        if ($endpoints->isEmpty()) {
            return;
        }

        $payload = [
            'event' => $event,
            'occurred_at' => now()->toIso8601String(),
            'ticket' => [
                'id' => $ticket->id,
                'uid' => $ticket->uid,
                'subject' => $ticket->subject,
                'status' => $ticket->status instanceof \BackedEnum ? $ticket->status->value : (string) $ticket->status,
                'priority' => $ticket->priority,
                'department' => $ticket->department?->name,
                'assigned_to' => $ticket->assigned_to,
            ],
            'extra' => $extra,
        ];

        foreach ($endpoints as $endpoint) {
            $key = hash('sha256', $event.'|'.$ticket->id.'|'.($extra['action_at'] ?? $payload['occurred_at']));

            $delivery = WebhookDelivery::firstOrCreate(
                ['idempotency_key' => $key],
                [
                    'webhook_endpoint_id' => $endpoint->id,
                    'event' => $event,
                    'payload' => $payload,
                    'status' => 'pending',
                ]
            );

            if ($delivery->status === 'pending') {
                DeliverWebhook::dispatch($delivery->id);
            }
        }
    }

    /**
     * Send a single delivery synchronously (called from the queued job).
     */
    public function send(WebhookDelivery $delivery): void
    {
        $endpoint = $delivery->endpoint()->firstOrFail();
        $payload = $delivery->payload ?? [];
        $body = json_encode($payload, JSON_UNESCAPED_SLASHES);
        $timestamp = (string) time();
        $secret = $endpoint->decryptedSecret() ?? '';

        $signature = hash_hmac('sha256', $timestamp.'.'.$body, $secret);

        $delivery->increment('attempts');

        $response = Http::timeout($endpoint->timeout_seconds ?: 10)
            ->withHeaders([
                'Content-Type' => 'application/json',
                'X-Webhook-Event' => $delivery->event,
                'X-Webhook-Timestamp' => $timestamp,
                'X-Webhook-Signature' => 'sha256='.$signature,
                'X-Idempotency-Key' => $delivery->idempotency_key,
            ])
            ->withBody($body, 'application/json')
            ->post($endpoint->url);

        $delivery->update([
            'response_status' => $response->status(),
            'response_body' => substr($response->body(), 0, 4000),
        ]);

        if (! $response->successful()) {
            throw new \RuntimeException('Webhook endpoint responded with HTTP '.$response->status());
        }

        $delivery->update(['status' => 'sent', 'error' => null]);
    }

    /**
     * Send a one-off test event synchronously (admin UI).
     *
     * @return array{ok: bool, status: ?int, body: string}
     */
    public function sendTest(WebhookEndpoint $endpoint): array
    {
        $payload = [
            'event' => 'webhook.test',
            'occurred_at' => now()->toIso8601String(),
            'message' => 'Test event from '.config('app.name'),
        ];
        $body = json_encode($payload, JSON_UNESCAPED_SLASHES);
        $timestamp = (string) time();
        $signature = hash_hmac('sha256', $timestamp.'.'.$body, $endpoint->decryptedSecret() ?? '');

        try {
            $response = Http::timeout(min(15, $endpoint->timeout_seconds ?: 10))
                ->withHeaders([
                    'Content-Type' => 'application/json',
                    'X-Webhook-Event' => 'webhook.test',
                    'X-Webhook-Timestamp' => $timestamp,
                    'X-Webhook-Signature' => 'sha256='.$signature,
                ])
                ->withBody($body, 'application/json')
                ->post($endpoint->url);

            return [
                'ok' => $response->successful(),
                'status' => $response->status(),
                'body' => substr($response->body(), 0, 2000),
            ];
        } catch (\Throwable $e) {
            Log::warning('Webhook test failed', ['endpoint_id' => $endpoint->id, 'error' => $e->getMessage()]);

            return ['ok' => false, 'status' => null, 'body' => $e->getMessage()];
        }
    }
}
