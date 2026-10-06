<?php

namespace App\Services;

use App\Enums\TicketStatus;
use App\Models\Ticket;

class SlaService
{
    public function evaluateTicket(Ticket $ticket): bool
    {
        if (! $ticket->sla_due_at) {
            return false;
        }

        $isBreached = now()->greaterThan($ticket->sla_due_at)
            && in_array($ticket->status->value, ['open', 'in_progress', 'waiting']);

        if ($isBreached && ! $ticket->sla_breached) {
            $ticket->update(['sla_breached' => true]);
            $this->sendBreachNotification($ticket);
        }

        return $isBreached;
    }

    public function checkAllTickets(): void
    {
        Ticket::whereNotIn('status', [
            TicketStatus::Resolved->value,
            TicketStatus::Closed->value,
        ])->chunkById(200, function ($tickets) {
            foreach ($tickets as $ticket) {
                $this->evaluateTicket($ticket);
            }
        });
    }

    public function sendBreachNotification(Ticket $ticket): void
    {
        if ($ticket->assignedTo) {
            ActivityLogService::log(
                'sla_breach',
                $ticket,
                $ticket->subject,
                ['due_at' => $ticket->sla_due_at->toDateTimeString()]
            );
        }
    }

    public function getTimeRemaining(Ticket $ticket): ?string
    {
        if (! $ticket->sla_due_at) {
            return null;
        }

        if ($ticket->sla_breached || now()->greaterThan($ticket->sla_due_at)) {
            return 'Breached';
        }

        $remaining = now()->diff($ticket->sla_due_at);

        $parts = [];
        if ($remaining->d > 0) {
            $parts[] = $remaining->d.'d';
        }
        if ($remaining->h > 0) {
            $parts[] = $remaining->h.'h';
        }
        if ($remaining->i > 0) {
            $parts[] = $remaining->i.'m';
        }

        return $parts ? implode(' ', $parts).' remaining' : 'Imminent';
    }
}
