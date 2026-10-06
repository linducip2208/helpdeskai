<?php

namespace App\Jobs;

use App\Models\WebhookDelivery;
use App\Services\WebhookService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;

class DeliverWebhook implements ShouldQueue
{
    use Queueable;

    public int $tries = 5;

    public function backoff(): array
    {
        return [60, 300, 900, 3600];
    }

    public function __construct(
        public int $deliveryId,
    ) {}

    public function handle(WebhookService $webhooks): void
    {
        $delivery = WebhookDelivery::with('endpoint')->find($this->deliveryId);

        if (! $delivery || $delivery->status === 'sent') {
            return;
        }

        if (! $delivery->endpoint || ! $delivery->endpoint->is_active) {
            $delivery->update(['status' => 'skipped', 'error' => 'Endpoint inactive or deleted.']);

            return;
        }

        try {
            $webhooks->send($delivery);
        } catch (\Throwable $e) {
            Log::warning('Webhook delivery failed, will retry', [
                'delivery_id' => $this->deliveryId,
                'error' => $e->getMessage(),
            ]);

            throw $e;
        }
    }

    public function failed(\Throwable $e): void
    {
        if ($delivery = WebhookDelivery::find($this->deliveryId)) {
            $delivery->update([
                'status' => 'failed',
                'error' => substr($e->getMessage(), 0, 2000),
            ]);
        }

        Log::error('Webhook delivery exhausted retries', [
            'delivery_id' => $this->deliveryId,
            'error' => $e->getMessage(),
        ]);
    }
}
