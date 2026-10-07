<?php

namespace App\Http\Controllers\Admin;

use App\Enums\TicketStatus;
use App\Http\Controllers\Controller;
use App\Models\Department;
use App\Models\SavedView;
use App\Models\Ticket;
use App\Models\User;
use App\Services\SlaService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SupportInboxController extends Controller
{
    public function index(Request $request, SlaService $sla): View
    {
        abort_unless($request->user()->can('tickets.view'), 403);

        $validated = $request->validate([
            'q' => ['nullable', 'string', 'max:255'],
            'status' => ['nullable', 'string'],
            'priority' => ['nullable', 'string', 'in:low,medium,high,urgent'],
            'department_id' => ['nullable', 'integer', 'exists:departments,id'],
            'queue' => ['nullable', 'string'],
            'sort' => ['nullable', 'string', 'in:latest,oldest,priority,sla'],
            'preview' => ['nullable', 'integer', 'exists:tickets,id'],
        ]);

        $search = $validated['q'] ?? null;
        $status = $validated['status'] ?? null;
        $priority = $validated['priority'] ?? null;
        $departmentId = $validated['department_id'] ?? null;
        $activeQueue = $validated['queue'] ?? 'open';
        $sort = $validated['sort'] ?? 'latest';

        $baseFiltered = $this->filteredQuery($search, $status, $priority, $departmentId);

        $queues = [
            'my' => (clone $baseFiltered)->where('assigned_to', auth()->id()),
            'open' => (clone $baseFiltered)->whereNotIn('status', [TicketStatus::Resolved->value, TicketStatus::Closed->value]),
            'unassigned' => (clone $baseFiltered)->whereNull('assigned_to')
                ->whereNotIn('status', [TicketStatus::Resolved->value, TicketStatus::Closed->value]),
            'urgent' => (clone $baseFiltered)->where('priority', 'urgent')
                ->whereNotIn('status', [TicketStatus::Resolved->value, TicketStatus::Closed->value]),
            'sla-risk' => (clone $baseFiltered)
                ->whereNotIn('status', [TicketStatus::Resolved->value, TicketStatus::Closed->value])
                ->where(function ($q) {
                    $q->where('sla_breached', true)
                        ->orWhereNotNull('sla_warned_at')
                        ->orWhere(function ($q2) {
                            $q2->whereNotNull('sla_due_at')->where('sla_due_at', '<=', now()->addHours(4));
                        });
                }),
            'waiting-customer' => (clone $baseFiltered)->where('status', TicketStatus::Waiting->value),
            'waiting-agent' => (clone $baseFiltered)->where('status', TicketStatus::Answered->value),
            'recently-updated' => (clone $baseFiltered),
        ];

        $counts = [];
        foreach ($queues as $key => $query) {
            $counts[$key] = (clone $query)->count();
        }

        $listQuery = $queues[$activeQueue] ?? $queues['open'];
        if (! array_key_exists($activeQueue, $queues)) {
            $activeQueue = 'open';
        }

        $listQuery = $this->applySort($listQuery, $sort);

        $tickets = $listQuery->with(['user', 'assignedTo', 'department'])
            ->take(25)
            ->get();

        foreach ($tickets as $ticket) {
            $ticket->setAttribute('sla_remaining', $sla->getTimeRemaining($ticket));
        }

        $previewTicket = null;
        if (! empty($validated['preview'])) {
            $previewTicket = Ticket::with(['user', 'assignedTo', 'department', 'category'])
                ->find($validated['preview']);
            if ($previewTicket) {
                $previewTicket->setAttribute('sla_remaining', $sla->getTimeRemaining($previewTicket));
            }
        }

        $savedViews = SavedView::where(function ($q) {
            $q->where('user_id', auth()->id())->orWhere('is_shared', true);
        })->orderBy('name')->get();

        return view('admin.support.inbox', [
            'queues' => [
                'my' => ['label' => __('My queue'), 'count' => $counts['my']],
                'open' => ['label' => __('Open'), 'count' => $counts['open']],
                'unassigned' => ['label' => __('Unassigned'), 'count' => $counts['unassigned']],
                'urgent' => ['label' => __('Urgent'), 'count' => $counts['urgent']],
                'sla-risk' => ['label' => __('SLA risk'), 'count' => $counts['sla-risk']],
                'waiting-customer' => ['label' => __('Waiting customer'), 'count' => $counts['waiting-customer']],
                'waiting-agent' => ['label' => __('Waiting agent'), 'count' => $counts['waiting-agent']],
                'recently-updated' => ['label' => __('Recently updated'), 'count' => $counts['recently-updated']],
            ],
            'activeQueue' => $activeQueue,
            'tickets' => $tickets,
            'previewTicket' => $previewTicket,
            'filters' => [
                'q' => $search,
                'status' => $status,
                'priority' => $priority,
                'department_id' => $departmentId,
                'sort' => $sort,
            ],
            'departments' => Department::where('is_active', true)->orderBy('name')->get(['id', 'name']),
            'agents' => User::whereHas('roles', fn ($q) => $q->where('name', 'agent'))->get(['id', 'name']),
            'savedViews' => $savedViews,
        ]);
    }

    private function filteredQuery(?string $search, ?string $status, ?string $priority, mixed $departmentId)
    {
        return Ticket::query()
            ->when($search, fn ($q) => $q->where(function ($q) use ($search) {
                $q->where('subject', 'like', "%{$search}%")
                    ->orWhere('uid', 'like', "%{$search}%");
            }))
            ->when($status, fn ($q) => $q->where('status', $status))
            ->when($priority, fn ($q) => $q->where('priority', $priority))
            ->when($departmentId, fn ($q) => $q->where('department_id', $departmentId));
    }

    private function applySort(mixed $query, string $sort): mixed
    {
        return match ($sort) {
            'oldest' => $query->oldest(),
            'priority' => $query->orderByRaw("CASE priority WHEN 'urgent' THEN 0 WHEN 'high' THEN 1 WHEN 'medium' THEN 2 WHEN 'low' THEN 3 ELSE 4 END")->latest(),
            'sla' => $query->orderByRaw('sla_due_at IS NULL, sla_due_at ASC')->latest(),
            default => $query->latest(),
        };
    }
}
