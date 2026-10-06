<?php

use App\Models\Ticket;
use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});

Broadcast::channel('ticket.{ticketId}', function ($user, $ticketId) {
    if ($user->hasRole(['super-admin', 'admin', 'manager', 'agent'])) {
        return ['id' => $user->id, 'name' => $user->name];
    }

    $ticket = Ticket::find($ticketId);

    if ($ticket && (int) $ticket->user_id === (int) $user->id) {
        return ['id' => $user->id, 'name' => $user->name];
    }

    return false;
});
