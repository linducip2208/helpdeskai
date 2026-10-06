<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Ticket;
use App\Models\User;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ExportController extends Controller
{
    public function tickets(Request $request): StreamedResponse
    {
        $query = Ticket::query()
            ->with(['user:id,name,email', 'assignedTo:id,name', 'department:id,name', 'category:id,name']);

        if ($status = $request->query('status')) {
            $query->where('status', $status);
        }
        if ($priority = $request->query('priority')) {
            $query->where('priority', $priority);
        }
        if ($from = $request->query('from')) {
            $query->whereDate('created_at', '>=', $from);
        }
        if ($to = $request->query('to')) {
            $query->whereDate('created_at', '<=', $to);
        }

        $filename = 'tickets-'.now()->format('Ymd-His').'.csv';

        return $this->stream($filename, function ($out) use ($query) {
            fputcsv($out, [
                'UID', 'Subject', 'Status', 'Priority', 'Source',
                'Customer', 'Customer Email', 'Assigned To',
                'Department', 'Category',
                'Created At', 'First Response At', 'Closed At', 'SLA Due At',
            ]);

            $query->orderBy('id')->chunk(500, function ($rows) use ($out) {
                foreach ($rows as $t) {
                    fputcsv($out, [
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
                    ]);
                }
            });
        });
    }

    public function agents(): StreamedResponse
    {
        $filename = 'agents-'.now()->format('Ymd-His').'.csv';

        return $this->stream($filename, function ($out) {
            fputcsv($out, [
                'ID', 'Name', 'Email', 'Role', 'Active',
                'Assigned Tickets', 'Resolved Tickets', 'Open Tickets',
                'Last Active', 'Created At',
            ]);

            User::query()
                ->whereIn('role', ['agent', 'admin'])
                ->orderBy('id')
                ->chunk(200, function ($users) use ($out) {
                    foreach ($users as $u) {
                        $assigned = Ticket::where('assigned_to', $u->id)->count();
                        $resolved = Ticket::where('assigned_to', $u->id)->whereIn('status', ['resolved', 'closed'])->count();
                        $open = Ticket::where('assigned_to', $u->id)->whereIn('status', ['open', 'in_progress', 'waiting'])->count();

                        fputcsv($out, [
                            $u->id,
                            $u->name,
                            $u->email,
                            $u->role,
                            $u->is_active ? 'yes' : 'no',
                            $assigned,
                            $resolved,
                            $open,
                            optional($u->last_active_at)?->toIso8601String(),
                            optional($u->created_at)?->toIso8601String(),
                        ]);
                    }
                });
        });
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
