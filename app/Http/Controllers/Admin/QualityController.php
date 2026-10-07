<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\QaScore;
use App\Models\Setting;
use App\Models\Ticket;
use App\Models\User;
use App\Services\ReportService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class QualityController extends Controller
{
    public function __construct(protected ReportService $reports) {}

    public function index(Request $request): View
    {
        $from = $request->get('from');
        $to = $request->get('to');

        $agents = User::whereHas('roles', fn ($q) => $q->whereIn('name', ['agent', 'admin', 'manager']))
            ->withCount([
                'assignedTickets as assigned' => fn ($q) => $this->inRange($q, $from, $to),
                'assignedTickets as resolved' => fn ($q) => $this->inRange($q, $from, $to)->whereIn('status', ['resolved', 'closed']),
            ])
            ->withAvg([
                'assignedTickets as csat_avg' => fn ($q) => $this->inRange($q, $from, $to)->whereNotNull('satisfaction_rating'),
            ], 'satisfaction_rating')
            ->orderBy('name')
            ->get();

        $reopened = ActivityLog::selectRaw('tickets.assigned_to as agent_id, COUNT(*) as total')
            ->join('tickets', 'tickets.id', '=', 'activity_logs.target_id')
            ->where('activity_logs.action', 'ticket_status_change')
            ->where('activity_logs.target_type', Ticket::class)
            ->whereIn('activity_logs.meta->from', ['resolved', 'closed'])
            ->where('activity_logs.meta->to', 'open')
            ->when($from, fn ($q) => $q->where('activity_logs.created_at', '>=', $from))
            ->when($to, fn ($q) => $q->where('activity_logs.created_at', '<=', $to))
            ->groupBy('tickets.assigned_to')
            ->pluck('total', 'agent_id');

        $qaAvg = QaScore::selectRaw('tickets.assigned_to as agent_id, AVG(qa_scores.overall) as avg_overall, COUNT(*) as scored')
            ->join('tickets', 'tickets.id', '=', 'qa_scores.ticket_id')
            ->when($from, fn ($q) => $q->where('qa_scores.created_at', '>=', $from))
            ->when($to, fn ($q) => $q->where('qa_scores.created_at', '<=', $to))
            ->groupBy('tickets.assigned_to')
            ->pluck('avg_overall', 'agent_id');

        $scores = QaScore::with(['reply.user:id,name', 'ticket:id,uid,subject'])
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return view('admin.qa.index', [
            'agents' => $agents,
            'reopened' => $reopened,
            'qaAvg' => $qaAvg,
            'scores' => $scores,
            'filters' => ['from' => $from, 'to' => $to],
            'qaEnabled' => (bool) Setting::get('ai.qa_enabled', false),
        ]);
    }

    protected function inRange($query, ?string $from, ?string $to)
    {
        return $query
            ->when($from, fn ($q) => $q->where('tickets.created_at', '>=', $from))
            ->when($to, fn ($q) => $q->where('tickets.created_at', '<=', $to));
    }
}
