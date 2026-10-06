<?php

namespace App\Console\Commands;

use App\Enums\TicketStatus;
use App\Models\Ticket;
use App\Services\AppNotificationService;
use Illuminate\Console\Command;

class SendTicketReminders extends Command
{
    protected $signature = 'tickets:reminders';

    protected $description = 'Remind assignees about tickets nearing or past their SLA deadline';

    public function handle(AppNotificationService $notifications): int
    {
        $openStatuses = [
            TicketStatus::Open->value,
            TicketStatus::InProgress->value,
            TicketStatus::Waiting->value,
        ];

        $tickets = Ticket::whereIn('status', $openStatuses)
            ->whereNotNull('assigned_to')
            ->whereNotNull('sla_due_at')
            ->where('sla_due_at', '<=', now()->addDay())
            ->with('assignedTo')
            ->get();

        $sent = 0;
        foreach ($tickets as $ticket) {
            if (! $ticket->assignedTo) {
                continue;
            }

            $breached = now()->greaterThan($ticket->sla_due_at);
            $title = $breached ? 'SLA breached' : 'SLA deadline approaching';
            $body = "Ticket #{$ticket->id} — {$ticket->subject} is due {$ticket->sla_due_at->diffForHumans()}.";

            $notifications->notify(
                $ticket->assignedTo,
                'ticket_reminder',
                $title,
                $body,
                route('admin.tickets.show', $ticket),
                ['ticket_id' => $ticket->id]
            );
            $sent++;
        }

        $this->info("Sent {$sent} ticket reminder(s).");

        return self::SUCCESS;
    }
}
