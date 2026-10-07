<?php

namespace App\Jobs;

use App\Models\TicketReply;
use App\Services\TicketService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;

class AnalyzeTicketSentiment implements ShouldQueue
{
    use Queueable;

    public int $tries = 3;

    public int $timeout = 120;

    public function backoff(): array
    {
        return [30, 120, 300];
    }

    public function __construct(
        public int $replyId,
    ) {}

    public function handle(TicketService $tickets): void
    {
        $reply = TicketReply::with('ticket')->find($this->replyId);

        if (! $reply || ! $reply->ticket) {
            return;
        }

        try {
            $tickets->analyzeSentiment($reply);
        } catch (\Throwable $e) {
            Log::warning('AI sentiment job failed', [
                'reply_id' => $this->replyId,
                'error' => $e->getMessage(),
            ]);

            throw $e;
        }
    }

    public function failed(\Throwable $e): void
    {
        Log::error('AI sentiment job exhausted retries', [
            'reply_id' => $this->replyId,
            'error' => $e->getMessage(),
        ]);
    }
}
