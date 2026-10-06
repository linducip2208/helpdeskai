<?php

namespace App\Jobs;

use App\Models\PushSubscription;
use App\Services\WebPushService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;

class SendWebPushNotification implements ShouldQueue
{
    use Queueable;

    public int $tries = 3;

    public function backoff(): array
    {
        return [30, 120, 300];
    }

    public function __construct(
        public int $subscriptionId,
        public array $payload,
    ) {}

    public function handle(WebPushService $webPush): void
    {
        $subscription = PushSubscription::find($this->subscriptionId);

        if (! $subscription) {
            return;
        }

        try {
            $webPush->send($subscription, $this->payload);
        } catch (\Throwable $e) {
            Log::warning('Queued web push failed', [
                'subscription_id' => $this->subscriptionId,
                'error' => $e->getMessage(),
            ]);

            throw $e;
        }
    }

    public function failed(\Throwable $e): void
    {
        Log::error('Queued web push exhausted retries', [
            'subscription_id' => $this->subscriptionId,
            'error' => $e->getMessage(),
        ]);
    }
}
