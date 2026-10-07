<?php

namespace App\Jobs;

use App\Models\Ticket;
use App\Services\TicketService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;

class ClassifyTicketWithAi implements ShouldQueue
{
    use Queueable;

    public int $tries = 3;

    public int $timeout = 180;

    public function backoff(): array
    {
        return [30, 120, 300];
    }

    public function __construct(
        public int $ticketId,
        public bool $force = false,
    ) {}

    public function handle(TicketService $tickets): void
    {
        $ticket = Ticket::find($this->ticketId);

        if (! $ticket) {
            return;
        }

        if (! $this->force && $ticket->ai_classified_at !== null) {
            return;
        }

        try {
            $tickets->autoClassify($ticket);
        } catch (\Throwable $e) {
            Log::warning('AI classification job failed', [
                'ticket_id' => $this->ticketId,
                'error' => $e->getMessage(),
            ]);

            throw $e;
        }
    }

    public function failed(\Throwable $e): void
    {
        Log::error('AI classification job exhausted retries', [
            'ticket_id' => $this->ticketId,
            'error' => $e->getMessage(),
        ]);
    }
}
