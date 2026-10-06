<?php

namespace App\Services;

use App\Models\AutomationRule;
use App\Models\Ticket;
use App\Models\User;

class AutomationService
{
    protected static int $depth = 0;

    protected const MAX_DEPTH = 2;

    protected const CONDITION_KEYS = [
        'status',
        'priority',
        'department_id',
        'category_id',
        'source',
        'assigned_to',
        'user_id',
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
                try {
                    if (! $this->conditionsMatch($rule, $ticket)) {
                        continue;
                    }

                    $this->applyActions($rule, $ticket->fresh() ?? $ticket, $context);

                    ActivityLogService::logCustom(
                        null,
                        'automation_fired',
                        AutomationRule::class,
                        $rule->id,
                        $rule->name,
                        ['event' => $event, 'ticket_id' => $ticket->id]
                    );
                } catch (\Throwable $e) {
                    report($e);
                }
            }
        } finally {
            self::$depth--;
        }
    }

    protected function conditionsMatch(AutomationRule $rule, Ticket $ticket): bool
    {
        $conditions = $rule->conditions ?? [];

        if ($conditions === []) {
            return true;
        }

        foreach ($conditions as $key => $expected) {
            if (! in_array($key, self::CONDITION_KEYS, true)) {
                return false;
            }

            $actual = $ticket->getAttribute($key);

            if ($actual instanceof \BackedEnum) {
                $actual = $actual->value;
            }

            if ((string) $actual !== (string) $expected) {
                return false;
            }
        }

        return true;
    }

    protected function applyActions(AutomationRule $rule, Ticket $ticket, array $context = []): void
    {
        $actions = $this->normalizeActions($rule->actions ?? []);

        foreach ($actions as $action) {
            $type = $action['type'] ?? null;
            $value = $action['value'] ?? null;

            match ($type) {
                'set_priority' => $ticket->update(['priority' => $value]),
                'set_status' => app(TicketService::class)->changeStatus($ticket->fresh() ?? $ticket, (string) $value),
                'set_category' => $ticket->update(['category_id' => $value ?: null]),
                'assign_agent' => $this->assignAgent($ticket, $value),
                'assign_department' => $ticket->update(['department_id' => $value]),
                'add_internal_note' => $this->addNote($ticket, (string) $value),
                'notify_assignee' => $this->notifyAssignee($ticket, (string) $value),
                'notify_customer' => $this->notifyCustomer($ticket, (string) $value),
                default => null,
            };
        }
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
