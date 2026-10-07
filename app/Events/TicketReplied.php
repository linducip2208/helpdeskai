<?php

namespace App\Events;

use App\Models\Ticket;
use App\Models\TicketReply;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class TicketReplied implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public Ticket $ticket,
        public TicketReply $reply,
    ) {}

    public function broadcastOn(): PrivateChannel
    {
        return new PrivateChannel('ticket.'.$this->ticket->id);
    }

    public function broadcastAs(): string
    {
        return 'ticket.replied';
    }

    public function broadcastWith(): array
    {
        return [
            'ticket_id' => $this->ticket->id,
            'uid' => $this->ticket->uid,
            'reply_id' => $this->reply->id,
            'is_internal' => (bool) $this->reply->is_internal,
        ];
    }
}
