<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AiUsageLog;
use App\Models\Ticket;
use App\Models\User;
use App\Services\SpreadsheetExportService;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ExportController extends Controller
{
    public function __construct(protected SpreadsheetExportService $sheets) {}

    public function tickets(Request $request): StreamedResponse
    {
        $query = $this->ticketQuery($request);
        $filename = 'tickets-'.now()->format('Ymd-His').'.csv';

        return $this->stream($filename, function ($out) use ($query) {
            fputcsv($out, $this->ticketHeadings());

            $query->orderBy('id')->chunk(500, function ($rows) use ($out) {
                foreach ($rows as $t) {
                    fputcsv($out, $this->ticketRow($t));
                }
            });
        });
    }

    public function ticketsXlsx(Request $request): StreamedResponse
    {
        $query = $this->ticketQuery($request);
        $filename = 'tickets-'.now()->format('Ymd-His').'.xlsx';

        return $this->sheets->stream($filename, $this->ticketHeadings(), function ($write) use ($query) {
            $query->orderBy('id')->chunk(500, function ($rows) use ($write) {
                foreach ($rows as $t) {
                    $write($this->ticketRow($t));
                }
            });
        });
    }

    public function agents(): StreamedResponse
    {
        $filename = 'agents-'.now()->format('Ymd-His').'.csv';

        return $this->stream($filename, function ($out) {
            fputcsv($out, $this->agentHeadings());
            $this->agentQuery()->chunk(200, function ($users) use ($out) {
                foreach ($users as $u) {
                    fputcsv($out, $this->agentRow($u));
                }
            });
        });
    }

    public function agentsXlsx(): StreamedResponse
    {
        $filename = 'agents-'.now()->format('Ymd-His').'.xlsx';

        return $this->sheets->stream($filename, $this->agentHeadings(), function ($write) {
            $this->agentQuery()->chunk(200, function ($users) use ($write) {
                foreach ($users as $u) {
                    $write($this->agentRow($u));
                }
            });
        });
    }

    public function sla(Request $request): StreamedResponse
    {
        $query = $this->slaQuery($request);
        $filename = 'sla-'.now()->format('Ymd-His').'.csv';

        return $this->stream($filename, function ($out) use ($query) {
            fputcsv($out, $this->slaHeadings());

            $query->orderBy('id')->chunk(500, function ($rows) use ($out) {
                foreach ($rows as $t) {
                    fputcsv($out, $this->slaRow($t));
                }
            });
        });
    }

    public function slaXlsx(Request $request): StreamedResponse
    {
        $query = $this->slaQuery($request);
        $filename = 'sla-'.now()->format('Ymd-His').'.xlsx';

        return $this->sheets->stream($filename, $this->slaHeadings(), function ($write) use ($query) {
            $query->orderBy('id')->chunk(500, function ($rows) use ($write) {
                foreach ($rows as $t) {
                    $write($this->slaRow($t));
                }
            });
        });
    }

    public function aiUsage(Request $request): StreamedResponse
    {
        $query = $this->aiUsageQuery($request);
        $filename = 'ai-usage-'.now()->format('Ymd-His').'.csv';

        return $this->stream($filename, function ($out) use ($query) {
            fputcsv($out, $this->aiUsageHeadings());

            $query->orderBy('id')->chunk(500, function ($rows) use ($out) {
                foreach ($rows as $log) {
                    fputcsv($out, $this->aiUsageRow($log));
                }
            });
        });
    }

    public function aiUsageXlsx(Request $request): StreamedResponse
    {
        $query = $this->aiUsageQuery($request);
        $filename = 'ai-usage-'.now()->format('Ymd-His').'.xlsx';

        return $this->sheets->stream($filename, $this->aiUsageHeadings(), function ($write) use ($query) {
            $query->orderBy('id')->chunk(500, function ($rows) use ($write) {
                foreach ($rows as $log) {
                    $write($this->aiUsageRow($log));
                }
            });
        });
    }

    protected function ticketQuery(Request $request)
    {
        $query = Ticket::query()
            ->with(['user:id,name,email', 'assignedTo:id,name', 'department:id,name', 'category:id,name']);

        if ($status = $request->query('status')) {
            $query->where('status', $status);
        }
        if ($priority = $request->query('priority')) {
            $query->where('priority', $priority);
        }
        if ($departmentId = $request->query('department_id')) {
            $query->where('department_id', $departmentId);
        }
        if ($assignedTo = $request->query('assigned_to')) {
            $query->where('assigned_to', $assignedTo);
        }
        if ($from = $request->query('from')) {
            $query->whereDate('created_at', '>=', $from);
        }
        if ($to = $request->query('to')) {
            $query->whereDate('created_at', '<=', $to);
        }

        return $query;
    }

    protected function ticketHeadings(): array
    {
        return [
            'UID', 'Subject', 'Status', 'Priority', 'Source',
            'Customer', 'Customer Email', 'Assigned To',
            'Department', 'Category',
            'Created At', 'First Response At', 'Closed At', 'SLA Due At',
        ];
    }

    protected function ticketRow(Ticket $t): array
    {
        return [
            $t->uid,
            $t->subject,
            is_object($t->status) ? $t->status->value : $t->status,
            $t->priority,
            $t->source,
            optional($t->user)->name,
            optional($t->user)->email,
            optional($t->assignedTo)->name,
            optional($t->department)->name,
            optional($t->category)->name,
            optional($t->created_at)?->toIso8601String(),
            optional($t->first_response_at ?? null)?->toIso8601String(),
            optional($t->closed_at)?->toIso8601String(),
            optional($t->sla_due_at)?->toIso8601String(),
        ];
    }

    protected function agentQuery()
    {
        return User::query()
            ->with('roles:id,name')
            ->whereHas('roles', fn ($q) => $q->whereIn('name', ['agent', 'admin', 'manager']))
            ->withCount([
                'assignedTickets as assigned_tickets',
                'assignedTickets as resolved_tickets' => fn ($q) => $q->whereIn('status', ['resolved', 'closed']),
                'assignedTickets as open_tickets' => fn ($q) => $q->whereIn('status', ['open', 'in_progress', 'waiting']),
            ])
            ->orderBy('id');
    }

    protected function agentHeadings(): array
    {
        return [
            'ID', 'Name', 'Email', 'Roles', 'Active',
            'Assigned Tickets', 'Resolved Tickets', 'Open Tickets',
            'Last Active', 'Created At',
        ];
    }

    protected function agentRow(User $u): array
    {
        return [
            $u->id,
            $u->name,
            $u->email,
            $u->roles->pluck('name')->implode(', '),
            $u->is_active ? 'yes' : 'no',
            $u->assigned_tickets,
            $u->resolved_tickets,
            $u->open_tickets,
            optional($u->last_active_at)?->toIso8601String(),
            optional($u->created_at)?->toIso8601String(),
        ];
    }

    protected function slaQuery(Request $request)
    {
        $query = Ticket::query()
            ->with(['user:id,name', 'assignedTo:id,name', 'department:id,name'])
            ->whereNotNull('sla_due_at');

        if ($breached = $request->query('breached')) {
            $query->where('sla_breached', $breached === '1');
        }
        if ($from = $request->query('from')) {
            $query->whereDate('created_at', '>=', $from);
        }
        if ($to = $request->query('to')) {
            $query->whereDate('created_at', '<=', $to);
        }

        return $query;
    }

    protected function slaHeadings(): array
    {
        return [
            'UID', 'Subject', 'Status', 'Priority', 'Customer',
            'Assigned To', 'Department', 'Created At', 'First Response At',
            'Resolved At', 'SLA Due At', 'SLA Breached',
        ];
    }

    protected function slaRow(Ticket $t): array
    {
        return [
            $t->uid,
            $t->subject,
            is_object($t->status) ? $t->status->value : $t->status,
            $t->priority,
            optional($t->user)->name,
            optional($t->assignedTo)->name,
            optional($t->department)->name,
            optional($t->created_at)?->toIso8601String(),
            optional($t->first_response_at ?? null)?->toIso8601String(),
            optional($t->resolved_at ?? null)?->toIso8601String(),
            optional($t->sla_due_at)?->toIso8601String(),
            $t->sla_breached ? 'yes' : 'no',
        ];
    }

    protected function aiUsageQuery(Request $request)
    {
        $query = AiUsageLog::query()->with(['provider:id,name', 'model:id,model_id']);

        if ($from = $request->query('from')) {
            $query->whereDate('created_at', '>=', $from);
        }
        if ($to = $request->query('to')) {
            $query->whereDate('created_at', '<=', $to);
        }

        return $query;
    }

    protected function aiUsageHeadings(): array
    {
        return [
            'ID', 'Feature', 'Provider', 'Model',
            'Input Tokens', 'Output Tokens', 'Cost Estimated',
            'Latency Ms', 'Success', 'Created At',
        ];
    }

    protected function aiUsageRow($log): array
    {
        return [
            $log->id,
            $log->feature_key,
            optional($log->provider)->name,
            optional($log->model)->model_id,
            $log->input_tokens,
            $log->output_tokens,
            $log->cost_estimated,
            $log->latency_ms,
            $log->success ? 'yes' : 'no',
            optional($log->created_at)?->toIso8601String(),
        ];
    }

    private function stream(string $filename, callable $writer): StreamedResponse
    {
        return response()->stream(function () use ($writer) {
            $out = fopen('php://output', 'w');
            fwrite($out, "\xEF\xBB\xBF");
            $writer($out);
            fclose($out);
        }, 200, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="'.$filename.'"',
            'Cache-Control' => 'no-store, no-cache',
        ]);
    }
}
