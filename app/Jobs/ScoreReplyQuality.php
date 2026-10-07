<?php

namespace App\Jobs;

use App\Models\TicketReply;
use App\Services\QaService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;

class ScoreReplyQuality implements ShouldQueue
{
    use Queueable;

    public int $tries = 2;

    public int $timeout = 120;

    public function backoff(): array
    {
        return [60, 300];
    }

    public function __construct(
        public int $replyId,
    ) {}

    public function handle(QaService $qa): void
    {
        $reply = TicketReply::with('ticket')->find($this->replyId);

        if (! $reply || ! $reply->ticket) {
            return;
        }

        try {
            $qa->scoreReply($reply);
        } catch (\Throwable $e) {
            Log::warning('QA scoring job failed', [
                'reply_id' => $this->replyId,
                'error' => $e->getMessage(),
            ]);

            throw $e;
        }
    }

    public function failed(\Throwable $e): void
    {
        Log::error('QA scoring job exhausted retries', [
            'reply_id' => $this->replyId,
            'error' => $e->getMessage(),
        ]);
    }
}
