<?php

namespace App\Services;

use App\Enums\TicketStatus;
use App\Models\Ticket;
use App\Models\User;

class SlaService
{
    protected const OPEN_STATUSES = ['open', 'in_progress', 'waiting'];

    public function __construct(protected AppNotificationService $notifier) {}

    public function evaluateTicket(Ticket $ticket): bool
    {
        $breached = false;

        if ($ticket->sla_due_at && ! in_array($ticket->status->value, ['resolved', 'closed'], true)) {
            if (now()->greaterThan($ticket->sla_due_at) && ! $ticket->sla_breached) {
                $ticket->update(['sla_breached' => true]);
                $this->sendBreachNotification($ticket, 'resolution');
            }

            $breached = $breached || (bool) $ticket->sla_breached;
        }

        if ($ticket->sla_response_due_at && $ticket->first_response_at === null
            && in_array($ticket->status->value, self::OPEN_STATUSES, true)) {
            if (now()->greaterThan($ticket->sla_response_due_at) && ! $ticket->sla_breached) {
                $ticket->update(['sla_breached' => true]);
                $this->sendBreachNotification($ticket, 'first_response');
            }

            $breached = $breached || (bool) $ticket->fresh()->sla_breached;
        }

        $this->maybeWarn($ticket);

        return $breached;
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

    protected function maybeWarn(Ticket $ticket): void
    {
        if ($ticket->sla_warned_at !== null || $ticket->sla_breached) {
            return;
        }

        if (! in_array($ticket->status->value, self::OPEN_STATUSES, true)) {
            return;
        }

        $dueAt = $ticket->sla_response_due_at && $ticket->first_response_at === null
            ? $ticket->sla_response_due_at
            : $ticket->sla_due_at;

        if (! $dueAt) {
            return;
        }

        $created = $ticket->created_at;
        $total = $created->diffInMinutes($dueAt);

        if ($total <= 0) {
            return;
        }

        $remaining = now()->diffInMinutes($dueAt, false);

        if ($remaining < 0 || $remaining / $total > 0.25) {
            return;
        }

        $ticket->update(['sla_warned_at' => now()]);

        ActivityLogService::log(
            'sla_warning',
            $ticket,
            $ticket->subject,
            ['due_at' => $dueAt->toDateTimeString()]
        );

        if ($ticket->assigned_to && $agent = User::find($ticket->assigned_to)) {
            $this->notifier->notify(
                $agent,
                'ticket.sla_warning',
                "SLA at risk: {$ticket->uid}",
                "Due {$dueAt->format('d M Y H:i')}. Please respond soon.",
                route('admin.tickets.show', $ticket),
                ['ticket_id' => $ticket->id]
            );
        }
    }

    public function sendBreachNotification(Ticket $ticket, string $kind = 'resolution'): void
    {
        ActivityLogService::log(
            'sla_breach',
            $ticket,
            $ticket->subject,
            [
                'due_at' => $ticket->sla_due_at?->toDateTimeString(),
                'kind' => $kind,
            ]
        );

        if ($ticket->assigned_to && $agent = User::find($ticket->assigned_to)) {
            $this->notifier->notify(
                $agent,
                'ticket.sla_breached',
                "SLA breached: {$ticket->uid}",
                ucfirst(str_replace('_', ' ', $kind))." SLA breached for {$ticket->subject}.",
                route('admin.tickets.show', $ticket),
                ['ticket_id' => $ticket->id]
            );
        }
    }

    public function getTimeRemaining(Ticket $ticket): ?string
    {
        $dueAt = $ticket->sla_response_due_at && $ticket->first_response_at === null
            ? $ticket->sla_response_due_at
            : $ticket->sla_due_at;

        if (! $dueAt) {
            return null;
        }

        if ($ticket->sla_breached || now()->greaterThan($dueAt)) {
            return 'Breached';
        }

        $remaining = now()->diff($dueAt);

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
