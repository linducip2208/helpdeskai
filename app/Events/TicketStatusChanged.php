<?php

namespace App\Events;

use App\Models\Ticket;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class TicketStatusChanged implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public Ticket $ticket,
        public string $from,
        public string $to,
    ) {}

    public function broadcastOn(): PrivateChannel
    {
        return new PrivateChannel('ticket.'.$this->ticket->id);
    }

    public function broadcastAs(): string
    {
        return 'ticket.status';
    }

    public function broadcastWith(): array
    {
        return [
            'ticket_id' => $this->ticket->id,
            'uid' => $this->ticket->uid,
            'from' => $this->from,
            'to' => $this->to,
        ];
    }
}
