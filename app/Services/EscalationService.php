<?php

namespace App\Services;

use App\Models\SlaEscalationRule;
use App\Models\SlaEscalationRun;
use App\Models\Ticket;
use App\Models\User;

class EscalationService
{
    public function run(): int
    {
        $rules = SlaEscalationRule::where('is_active', true)->orderBy('id')->get();

        if ($rules->isEmpty()) {
            return 0;
        }

        $count = 0;
        $tickets = Ticket::whereIn('status', ['open', 'in_progress', 'waiting'])->get();

        foreach ($tickets as $ticket) {
            foreach ($rules as $rule) {
                if ($this->alreadyRan($rule->id, $ticket->id)) {
                    continue;
                }

                if ($this->matches($rule, $ticket)) {
                    $this->apply($rule, $ticket);
                    SlaEscalationRun::create(['rule_id' => $rule->id, 'ticket_id' => $ticket->id]);
                    $count++;
                }
            }
        }

        return $count;
    }

    protected function alreadyRan(int $ruleId, int $ticketId): bool
    {
        return SlaEscalationRun::where('rule_id', $ruleId)->where('ticket_id', $ticketId)->exists();
    }

    protected function matches(SlaEscalationRule $rule, Ticket $ticket): bool
    {
        $reference = match ($rule->trigger) {
            'response_warning' => $ticket->sla_warned_at,
            'response_breach' => $ticket->first_response_at === null ? $ticket->sla_response_due_at : null,
            'resolution_warning' => $ticket->sla_warned_at,
            'resolution_breach' => $ticket->sla_due_at,
            default => null,
        };

        if (! $reference) {
            if (str_ends_with($rule->trigger, '_breach')) {
                $reference = $rule->trigger === 'response_breach'
                    ? $ticket->sla_response_due_at
                    : $ticket->sla_due_at;
                if (! $reference || ! now()->greaterThan($reference)) {
                    return false;
                }
            } else {
                return (bool) $ticket->sla_warned_at;
            }
        }

        return $reference->diffInMinutes() >= $rule->after_minutes;
    }

    protected function apply(SlaEscalationRule $rule, Ticket $ticket): void
    {
        $tickets = app(TicketService::class);
        $fresh = $ticket->fresh() ?? $ticket;

        if ($rule->action_priority) {
            $tickets->updateTicket($fresh, ['priority' => $rule->action_priority]);
            $fresh = $fresh->fresh() ?? $fresh;
        }

        if ($rule->action_assign_role) {
            $agent = User::role($rule->action_assign_role)
                ->where('is_active', true)
                ->where('id', '!=', $fresh->assigned_to)
                ->orderBy('id')
                ->first();

            if ($agent) {
                $tickets->assignTicket($fresh, $agent->id);
            }
        }

        if ($rule->notify_assignee && $fresh->assigned_to && $agent = User::find($fresh->assigned_to)) {
            app(AppNotificationService::class)->notify(
                $agent,
                'ticket.escalated',
                "Escalated: {$ticket->uid}",
                "Ticket {$ticket->uid} escalated by rule {$rule->name}.",
                route('admin.tickets.show', $ticket),
                ['ticket_id' => $ticket->id, 'rule_id' => $rule->id]
            );
        }

        ActivityLogService::logCustom(null, 'sla_escalated', SlaEscalationRule::class, $rule->id, $rule->name, [
            'ticket_id' => $ticket->id,
        ]);
    }
}
