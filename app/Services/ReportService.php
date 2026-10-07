<?php

namespace App\Services;

use App\Models\ActivityLog;
use App\Models\AiUsageLog;
use App\Models\Ticket;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class ReportService
{
    protected const OPEN_STATUSES = ['open', 'in_progress', 'waiting'];

    /**
     * @return array{from: ?string, to: ?string}
     */
    public function normalizeRange(?string $from, ?string $to, int $defaultDays = 30): array
    {
        try {
            $toDate = $to ? Carbon::parse($to)->endOfDay() : now()->endOfDay();
        } catch (\Throwable) {
            $toDate = now()->endOfDay();
        }

        try {
            $fromDate = $from ? Carbon::parse($from)->startOfDay() : $toDate->copy()->subDays($defaultDays - 1)->startOfDay();
        } catch (\Throwable) {
            $fromDate = $toDate->copy()->subDays($defaultDays - 1)->startOfDay();
        }

        if ($fromDate->greaterThan($toDate)) {
            [$fromDate, $toDate] = [$toDate->copy()->startOfDay(), $fromDate->copy()->endOfDay()];
        }

        return [$fromDate->toDateTimeString(), $toDate->toDateTimeString()];
    }

    protected function inRange(?string $from, ?string $to)
    {
        $query = Ticket::query();

        if ($from) {
            $query->where('created_at', '>=', $from);
        }

        if ($to) {
            $query->where('created_at', '<=', $to);
        }

        return $query;
    }

    public function overview(?string $from = null, ?string $to = null): array
    {
        [$from, $to] = $this->normalizeRange($from, $to);

        $base = $this->inRange($from, $to);
        $total = (clone $base)->count();

        $open = (clone $base)->whereIn('status', self::OPEN_STATUSES)->count();
        $resolved = (clone $base)->where('status', 'resolved')->count();
        $closed = (clone $base)->where('status', 'closed')->count();
        $breached = (clone $base)->where('sla_breached', true)->count();
        $due = (clone $base)->whereNotNull('sla_due_at')->count();
        $unassigned = (clone $base)->whereIn('status', self::OPEN_STATUSES)->whereNull('assigned_to')->count();

        $reopened = ActivityLog::where('action', 'ticket_status_change')
            ->where('meta->to', 'open')
            ->whereBetween('created_at', [$from, $to])
            ->count();

        $aiLogs = AiUsageLog::whereBetween('created_at', [$from, $to]);
        $aiCalls = (clone $aiLogs)->count();
        $aiCost = (float) (clone $aiLogs)->sum('cost_estimated');

        $automationFired = ActivityLog::where('action', 'automation_fired')
            ->whereBetween('created_at', [$from, $to])
            ->count();

        return [
            'from' => $from,
            'to' => $to,
            'total_tickets' => $total,
            'open_tickets' => $open,
            'resolved_tickets' => $resolved,
            'closed_tickets' => $closed,
            'sla_breached' => $breached,
            'unassigned_tickets' => $unassigned,
            'sla_compliance' => $due > 0 ? round((($due - $breached) / $due) * 100, 1) : null,
            'avg_first_response' => $this->formatDuration($this->avgMinutes($from, $to, 'first_response_at')),
            'avg_resolution' => $this->formatDuration($this->avgMinutes($from, $to, 'resolved_at')),
            'satisfaction_avg' => round((float) (clone $base)->whereNotNull('satisfaction_rating')->avg('satisfaction_rating'), 1),
            'reopened_tickets' => $reopened,
            'ai_calls' => $aiCalls,
            'ai_cost' => round($aiCost, 4),
            'automation_fired' => $automationFired,
            'by_channel' => $this->byChannel($from, $to),
            'aging' => $this->aging($from, $to),
            'escalations' => ActivityLog::where('action', 'ticket_escalated')
                ->whereBetween('created_at', [$from, $to])->count()
                + ActivityLog::where('action', 'sla_escalated')
                    ->whereBetween('created_at', [$from, $to])->count(),
        ];
    }

    /**
     * @return array<string, int>
     */
    public function byChannel(?string $from = null, ?string $to = null): array
    {
        [$from, $to] = $this->normalizeRange($from, $to);

        return $this->inRange($from, $to)
            ->selectRaw('source, COUNT(*) as total')
            ->groupBy('source')
            ->pluck('total', 'source')
            ->map(fn ($v) => (int) $v)
            ->all();
    }

    /**
     * Open-ticket age buckets in days.
     *
     * @return array<string, int>
     */
    public function aging(?string $from = null, ?string $to = null): array
    {
        [$from, $to] = $this->normalizeRange($from, $to);

        $buckets = ['0-1 days' => 0, '2-3 days' => 0, '4-7 days' => 0, '8-30 days' => 0, '30+ days' => 0];

        $this->inRange($from, $to)
            ->whereIn('status', self::OPEN_STATUSES)
            ->pluck('created_at')
            ->each(function ($created) use (&$buckets) {
                $days = $created->diffInDays(now());
                match (true) {
                    $days <= 1 => $buckets['0-1 days']++,
                    $days <= 3 => $buckets['2-3 days']++,
                    $days <= 7 => $buckets['4-7 days']++,
                    $days <= 30 => $buckets['8-30 days']++,
                    default => $buckets['30+ days']++,
                };
            });

        return $buckets;
    }

    /**
     * @return array<string, int>
     */
    public function byStatus(?string $from = null, ?string $to = null): array
    {
        [$from, $to] = $this->normalizeRange($from, $to);

        return $this->inRange($from, $to)
            ->selectRaw('status, COUNT(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status')
            ->map(fn ($v) => (int) $v)
            ->all();
    }

    /**
     * @return array<string, int>
     */
    public function byPriority(?string $from = null, ?string $to = null): array
    {
        [$from, $to] = $this->normalizeRange($from, $to);

        return $this->inRange($from, $to)
            ->selectRaw('priority, COUNT(*) as total')
            ->groupBy('priority')
            ->pluck('total', 'priority')
            ->map(fn ($v) => (int) $v)
            ->all();
    }

    /**
     * @return array<int, array{id: int, name: string, total: int}>
     */
    public function byDepartment(?string $from = null, ?string $to = null): array
    {
        [$from, $to] = $this->normalizeRange($from, $to);

        return $this->inRange($from, $to)
            ->selectRaw('department_id, COUNT(*) as total')
            ->with('department:id,name')
            ->groupBy('department_id')
            ->orderByDesc('total')
            ->get()
            ->map(fn ($row) => [
                'id' => $row->department_id,
                'name' => $row->department?->name ?? __('Unassigned'),
                'total' => (int) $row->total,
            ])
            ->all();
    }

    /**
     * @return array<int, array{id: int, name: string, total: int}>
     */
    public function byCategory(?string $from = null, ?string $to = null): array
    {
        [$from, $to] = $this->normalizeRange($from, $to);

        return $this->inRange($from, $to)
            ->selectRaw('category_id, COUNT(*) as total')
            ->with('category:id,name')
            ->groupBy('category_id')
            ->orderByDesc('total')
            ->get()
            ->map(fn ($row) => [
                'id' => $row->category_id,
                'name' => $row->category?->name ?? __('Uncategorized'),
                'total' => (int) $row->total,
            ])
            ->all();
    }

    /**
     * @return Collection<int, array{name: string, assigned: int, resolved: int, open: int, avg_response: ?string, csat: ?float}>
     */
    public function agentPerformance(?string $from = null, ?string $to = null)
    {
        [$from, $to] = $this->normalizeRange($from, $to);

        return User::whereHas('roles', fn ($q) => $q->whereIn('name', ['agent', 'admin', 'manager']))
            ->withCount([
                'assignedTickets as assigned' => fn ($q) => $q->whereBetween('tickets.created_at', [$from, $to]),
                'assignedTickets as resolved' => fn ($q) => $q->whereBetween('tickets.created_at', [$from, $to])->whereIn('status', ['resolved', 'closed']),
                'assignedTickets as open' => fn ($q) => $q->whereIn('status', self::OPEN_STATUSES),
            ])
            ->orderByDesc('assigned')
            ->get()
            ->map(fn (User $u) => [
                'name' => $u->name,
                'assigned' => (int) $u->assigned,
                'resolved' => (int) $u->resolved,
                'open' => (int) $u->open,
                'avg_response' => $this->formatDuration($this->agentAvgMinutes($u->id, $from, $to)),
                'csat' => round((float) Ticket::where('assigned_to', $u->id)->whereBetween('tickets.created_at', [$from, $to])->whereNotNull('satisfaction_rating')->avg('satisfaction_rating'), 1) ?: 0,
            ]);
    }

    /**
     * Created vs resolved per day (portable, no raw date functions).
     *
     * @return array{labels: array<int,string>, created: array<int,int>, resolved: array<int,int>}
     */
    public function trends(?string $from = null, ?string $to = null): array
    {
        [$from, $to] = $this->normalizeRange($from, $to);

        $start = Carbon::parse($from)->startOfDay();
        $end = Carbon::parse($to)->endOfDay();
        $days = min(93, max(1, $start->diffInDays($end) + 1));

        $labels = [];
        $buckets = [];
        for ($i = $days - 1; $i >= 0; $i--) {
            $key = $end->copy()->subDays($i)->format('Y-m-d');
            $buckets[$key] = ['created' => 0, 'resolved' => 0];
            $labels[] = $end->copy()->subDays($i)->format('M d');
        }

        $created = Ticket::whereBetween('created_at', [$start->toDateTimeString(), $end->toDateTimeString()])
            ->pluck('created_at');

        foreach ($created as $date) {
            $key = $date->format('Y-m-d');
            if (isset($buckets[$key])) {
                $buckets[$key]['created']++;
            }
        }

        $resolved = Ticket::whereNotNull('resolved_at')
            ->whereBetween('resolved_at', [$start->toDateTimeString(), $end->toDateTimeString()])
            ->pluck('resolved_at');

        foreach ($resolved as $date) {
            $key = $date->format('Y-m-d');
            if (isset($buckets[$key])) {
                $buckets[$key]['resolved']++;
            }
        }

        return [
            'labels' => $labels,
            'created' => array_column($buckets, 'created'),
            'resolved' => array_column($buckets, 'resolved'),
        ];
    }

    protected function avgMinutes(?string $from, ?string $to, string $column): ?float
    {
        $rows = Ticket::whereNotNull($column)
            ->whereBetween('created_at', [$from, $to])
            ->orderByDesc('id')
            ->take(500)
            ->get(['created_at', $column]);

        if ($rows->isEmpty()) {
            return null;
        }

        return round($rows->sum(fn ($t) => max(0, $t->created_at->diffInMinutes($t->{$column}))) / $rows->count(), 1);
    }

    protected function agentAvgMinutes(int $userId, ?string $from, ?string $to): ?float
    {
        $rows = Ticket::where('assigned_to', $userId)
            ->whereNotNull('first_response_at')
            ->whereBetween('created_at', [$from, $to])
            ->orderByDesc('id')
            ->take(200)
            ->get(['created_at', 'first_response_at']);

        if ($rows->isEmpty()) {
            return null;
        }

        return round($rows->sum(fn ($t) => max(0, $t->created_at->diffInMinutes($t->first_response_at))) / $rows->count(), 1);
    }

    protected function formatDuration(?float $minutes): ?string
    {
        if ($minutes === null) {
            return null;
        }

        if ($minutes < 60) {
            return $minutes.'m';
        }

        if ($minutes < 1440) {
            return round($minutes / 60, 1).'h';
        }

        return round($minutes / 1440, 1).'d';
    }
}
