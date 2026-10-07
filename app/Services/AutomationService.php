<?php

namespace App\Services;

use App\Models\AutomationRule;
use App\Models\AutomationRun;
use App\Models\Ticket;
use App\Models\User;
use App\Models\WebhookEndpoint;
use Illuminate\Support\Facades\Mail;

class AutomationService
{
    protected static int $depth = 0;

    protected const MAX_DEPTH = 2;

    public const TRIGGERS = [
        'ticket_created',
        'ticket_updated',
        'ticket_replied',
        'ticket_agent_replied',
        'ticket_status_changed',
        'ticket_priority_changed',
        'ticket_assignment_changed',
        'ticket_reopened',
        'csat.submitted',
        'sla.warning',
        'sla.breached',
    ];

    protected const CONDITION_KEYS = [
        'status',
        'priority',
        'department_id',
        'category_id',
        'source',
        'assigned_to',
        'user_id',
        'tag',
        'sla_breached',
    ];

    /**
     * Evaluate active rules for an event and apply matching actions.
     * Guarded against infinite recursion via a static depth counter.
     */
    public function fire(string $event, Ticket $ticket, array $context = []): void
    {
        if (self::$depth >= self::MAX_DEPTH) {
            return;
        }

        if (! in_array($event, self::TRIGGERS, true)) {
            return;
        }

        $rules = AutomationRule::where('is_active', true)
            ->where('trigger_event', $event)
            ->orderBy('sort_order')
            ->get();

        if ($rules->isEmpty()) {
            return;
        }

        self::$depth++;

        try {
            foreach ($rules as $rule) {
                $started = microtime(true);
                $run = AutomationRun::create([
                    'rule_id' => $rule->id,
                    'ticket_id' => $ticket->id,
                    'trigger' => $event,
                    'status' => 'running',
                ]);

                try {
                    if (! $this->conditionsMatch($rule, $ticket, $context)) {
                        $run->update(['status' => 'skipped', 'duration_ms' => $this->elapsed($started)]);

                        continue;
                    }

                    $executed = $this->applyActions($rule, $ticket->fresh() ?? $ticket, $context);

                    $run->update([
                        'status' => 'success',
                        'actions_executed' => $executed,
                        'duration_ms' => $this->elapsed($started),
                    ]);

                    ActivityLogService::logCustom(
                        null,
                        'automation_fired',
                        AutomationRule::class,
                        $rule->id,
                        $rule->name,
                        ['event' => $event, 'ticket_id' => $ticket->id]
                    );
                } catch (\Throwable $e) {
                    $run->update([
                        'status' => 'failed',
                        'error' => substr($e->getMessage(), 0, 2000),
                        'duration_ms' => $this->elapsed($started),
                    ]);
                    report($e);
                }
            }
        } finally {
            self::$depth--;
        }
    }

    protected function elapsed(float $started): int
    {
        return (int) round((microtime(true) - $started) * 1000);
    }

    protected function conditionsMatch(AutomationRule $rule, Ticket $ticket, array $context = []): bool
    {
        $conditions = $rule->conditions ?? [];

        if ($conditions === []) {
            return true;
        }

        foreach ($conditions as $key => $expected) {
            if (! in_array($key, self::CONDITION_KEYS, true)) {
                return false;
            }

            if ($key === 'tag') {
                $names = $ticket->tags()->pluck('name')->map(fn ($n) => mb_strtolower($n))->all();
                if (! in_array(mb_strtolower((string) $expected), $names, true)) {
                    return false;
                }

                continue;
            }

            $actual = $ticket->getAttribute($key);

            if ($actual instanceof \BackedEnum) {
                $actual = $actual->value;
            }

            if ($key === 'sla_breached') {
                $actual = $actual ? '1' : '0';
                $expected = $expected ? '1' : '0';
            }

            if (is_array($expected)) {
                $matched = false;
                foreach ($expected as $option) {
                    if ((string) $actual === (string) $option) {
                        $matched = true;
                        break;
                    }
                }
                if (! $matched) {
                    return false;
                }

                continue;
            }

            if ((string) $actual !== (string) $expected) {
                return false;
            }
        }

        return true;
    }

    /**
     * @return array<int, string> executed action types
     */
    protected function applyActions(AutomationRule $rule, Ticket $ticket, array $context = []): array
    {
        $actions = $this->normalizeActions($rule->actions ?? []);
        $executed = [];
        $tickets = app(TicketService::class);

        foreach ($actions as $action) {
            $type = $action['type'] ?? null;
            $value = $action['value'] ?? null;

            match ($type) {
                'set_priority' => $ticket->update(['priority' => $value]),
                'set_status' => $tickets->changeStatus($ticket->fresh() ?? $ticket, (string) $value),
                'set_category' => $ticket->update(['category_id' => $value ?: null]),
                'assign_agent' => $this->assignAgent($ticket, $value),
                'assign_department' => $ticket->update(['department_id' => $value]),
                'add_internal_note' => $this->addNote($ticket, (string) $value),
                'notify_assignee' => $this->notifyAssignee($ticket, (string) $value),
                'notify_customer' => $this->notifyCustomer($ticket, (string) $value),
                'add_tag' => $tickets->addTag($ticket->fresh() ?? $ticket, (string) $value),
                'remove_tag' => $tickets->removeTag($ticket->fresh() ?? $ticket, (string) $value),
                'send_email' => $this->sendEmail($ticket, $action, $context),
                'call_webhook' => $this->callWebhook($ticket, $action, $context),
                'escalate' => $this->escalate($ticket, $action),
                'trigger_ai' => $tickets->autoClassify($ticket->fresh() ?? $ticket),
                default => null,
            };

            if ($type) {
                $executed[] = (string) $type;
            }
        }

        return $executed;
    }

    /**
     * @return array<int, array{type: string, value: mixed}>
     */
    protected function normalizeActions(mixed $actions): array
    {
        if (! is_array($actions)) {
            return [];
        }

        if (isset($actions['type'])) {
            return [$actions];
        }

        return array_values(array_filter($actions, fn ($a) => is_array($a) && isset($a['type'])));
    }

    protected function assignAgent(Ticket $ticket, mixed $value): void
    {
        $agent = is_numeric($value) ? User::find((int) $value) : null;

        if (! $agent) {
            $agent = User::whereHas('roles', fn ($q) => $q->where('name', 'agent'))
                ->where('is_active', true)
                ->inRandomOrder()
                ->first();
        }

        if ($agent) {
            app(TicketService::class)->assignTicket($ticket->fresh() ?? $ticket, $agent->id);
        }
    }

    protected function addNote(Ticket $ticket, string $text): void
    {
        if (trim($text) === '') {
            return;
        }

        $ticket->replies()->create([
            'user_id' => auth()->id() ?? $ticket->assigned_to ?? $ticket->user_id,
            'body' => '[Automation] '.$text,
            'is_internal' => true,
            'source' => 'automation',
        ]);
    }

    protected function sendEmail(Ticket $ticket, array $action, array $context = []): void
    {
        $to = $action['to'] ?? 'customer';
        $user = $to === 'assignee'
            ? ($ticket->assigned_to ? User::find($ticket->assigned_to) : null)
            : User::find($ticket->user_id);

        if (! $user || ! $user->email) {
            return;
        }

        $subject = $action['subject'] ?? "Update on ticket {$ticket->uid}";
        $body = $action['body'] ?? "There is an update on your ticket {$ticket->uid}.";

        Mail::raw(
            $this->interpolate($body, $ticket),
            fn ($m) => $m->to($user->email)->subject($this->interpolate($subject, $ticket))
        );
    }

    protected function callWebhook(Ticket $ticket, array $action, array $context = []): void
    {
        $event = (string) ($action['event'] ?? '');

        if (! in_array($event, WebhookEndpoint::EVENTS, true)) {
            return;
        }

        app(WebhookService::class)->dispatch($event, $ticket->fresh() ?? $ticket, $context);
    }

    protected function escalate(Ticket $ticket, array $action): void
    {
        $tickets = app(TicketService::class);
        $fresh = $ticket->fresh() ?? $ticket;

        if (! empty($action['priority'])) {
            $tickets->updateTicket($fresh, ['priority' => (string) $action['priority']]);
            $fresh = $fresh->fresh() ?? $fresh;
        }

        $notified = [];
        foreach (['admin', 'manager'] as $role) {
            foreach (User::role($role)->where('is_active', true)->get(['id', 'email']) as $supervisor) {
                app(AppNotificationService::class)->notify(
                    $supervisor,
                    'ticket.escalated',
                    "Escalated: {$ticket->uid}",
                    "Ticket {$ticket->uid} was escalated by automation.",
                    route('admin.tickets.show', $ticket),
                    ['ticket_id' => $ticket->id]
                );
                $notified[] = $supervisor->id;
            }
        }

        ActivityLogService::logCustom(null, 'ticket_escalated', Ticket::class, $ticket->id, $ticket->subject, [
            'priority' => $action['priority'] ?? null,
            'notified' => $notified,
        ]);
    }

    protected function interpolate(string $text, Ticket $ticket): string
    {
        return str_replace(
            ['{uid}', '{subject}', '{status}', '{priority}'],
            [$ticket->uid, $ticket->subject, (string) $ticket->status->value, (string) ($ticket->priority ?? '')],
            $text
        );
    }

    protected function notifyAssignee(Ticket $ticket, string $message): void
    {
        if ($ticket->assigned_to && $agent = User::find($ticket->assigned_to)) {
            app(AppNotificationService::class)->notify(
                $agent,
                'automation.fired',
                'Automation: '.$ticket->uid,
                trim($message) !== '' ? $message : 'An automation rule was applied to this ticket.',
                route('admin.tickets.show', $ticket),
                ['ticket_id' => $ticket->id]
            );
        }
    }

    protected function notifyCustomer(Ticket $ticket, string $message): void
    {
        if ($customer = User::find($ticket->user_id)) {
            app(AppNotificationService::class)->notify(
                $customer,
                'automation.fired',
                'Update on ticket '.$ticket->uid,
                trim($message) !== '' ? $message : 'There is an update on your ticket.',
                route('user.tickets.show', $ticket),
                ['ticket_id' => $ticket->id]
            );
        }
    }
}
