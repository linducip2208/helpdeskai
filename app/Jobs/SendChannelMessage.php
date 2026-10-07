<?php

namespace App\Jobs;

use App\Models\Ticket;
use App\Models\TicketReply;
use App\Services\Channels\ChannelManager;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;

class SendChannelMessage implements ShouldQueue
{
    use Queueable;

    public int $tries = 3;

    public function backoff(): array
    {
        return [60, 300, 900];
    }

    public function __construct(
        public int $ticketId,
        public int $replyId,
    ) {}

    public function handle(ChannelManager $channels): void
    {
        $ticket = Ticket::with('user')->find($this->ticketId);
        $reply = TicketReply::find($this->replyId);

        if (! $ticket || ! $reply || ! $ticket->user) {
            return;
        }

        $customer = $ticket->user;
        $text = "[{$ticket->uid}] {$ticket->subject}\n\n{$reply->body}";

        foreach (['whatsapp', 'telegram'] as $channel) {
            $recipient = $channel === 'telegram' ? $customer->telegram_id : $customer->whatsapp_id;

            if (! $recipient) {
                continue;
            }

            $driver = $channels->driver($channel);

            if (! $driver || ! $driver->enabled()) {
                continue;
            }

            try {
                $driver->send($recipient, $text);
            } catch (\Throwable $e) {
                Log::warning('Channel send failed, will retry', [
                    'channel' => $channel,
                    'ticket_id' => $this->ticketId,
                    'error' => $e->getMessage(),
                ]);

                throw $e;
            }

            return;
        }
    }

    public function failed(\Throwable $e): void
    {
        Log::error('Channel message exhausted retries', [
            'ticket_id' => $this->ticketId,
            'reply_id' => $this->replyId,
            'error' => $e->getMessage(),
        ]);
    }
}
