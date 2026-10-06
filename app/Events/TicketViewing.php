<?php

namespace App\Events;

use App\Models\Ticket;
use App\Models\User;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class TicketViewing implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public Ticket $ticket,
        public User $viewer,
    ) {}

    public function broadcastOn(): PrivateChannel
    {
        return new PrivateChannel('ticket.'.$this->ticket->id);
    }

    public function broadcastAs(): string
    {
        return 'ticket.viewing';
    }

    /**
     * @return array{id: int, name: string}
     */
    public function broadcastWith(): array
    {
        return [
            'id' => $this->viewer->id,
            'name' => $this->viewer->name,
        ];
    }
}
