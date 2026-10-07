<?php

namespace App\Http\Controllers\Admin;

use App\Enums\TicketStatus;
use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\Organization;
use App\Models\Ticket;
use App\Models\TicketAttachment;
use App\Models\TicketReply;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class CustomerController extends Controller
{
    public function show(Request $request, User $customer): View
    {
        abort_unless($request->user()->can('customers.view'), 403);

        $ticketIds = Ticket::where('user_id', $customer->id)->pluck('id');

        $organization = $customer->organization_id
            ? Organization::find($customer->organization_id)
            : null;

        $tags = $ticketIds->isNotEmpty()
            ? DB::table('ticket_tag')
                ->join('tags', 'tags.id', '=', 'ticket_tag.tag_id')
                ->whereIn('ticket_tag.ticket_id', $ticketIds)
                ->distinct()
                ->pluck('tags.name')
            : collect();

        $ticketsQuery = Ticket::where('user_id', $customer->id);
        $total = (clone $ticketsQuery)->count();
        $open = (clone $ticketsQuery)->whereNotIn('status', [TicketStatus::Resolved->value, TicketStatus::Closed->value])->count();
        $resolved = (clone $ticketsQuery)->whereIn('status', [TicketStatus::Resolved->value, TicketStatus::Closed->value])->count();

        $timings = (clone $ticketsQuery)
            ->whereNotNull('resolved_at')
            ->get(['created_at', 'resolved_at']);
        $avgResolutionSeconds = $timings->isNotEmpty()
            ? $timings->avg(fn ($t) => $t->resolved_at->diffInSeconds($t->created_at))
            : null;

        $responses = (clone $ticketsQuery)
            ->whereNotNull('first_response_at')
            ->get(['created_at', 'first_response_at']);
        $avgFirstResponseSeconds = $responses->isNotEmpty()
            ? $responses->avg(fn ($t) => $t->first_response_at->diffInSeconds($t->created_at))
            : null;

        $slaCompliant = (clone $ticketsQuery)
            ->whereIn('status', [TicketStatus::Resolved->value, TicketStatus::Closed->value])
            ->where('sla_breached', false)
            ->count();

        $csatAvg = (clone $ticketsQuery)->whereNotNull('satisfaction_rating')->avg('satisfaction_rating');

        $reopenCount = $ticketIds->isNotEmpty()
            ? ActivityLog::where('action', 'ticket_reopen')
                ->where('target_type', Ticket::class)
                ->whereIn('target_id', $ticketIds)
                ->count()
            : 0;

        $logs = $ticketIds->isNotEmpty()
            ? ActivityLog::where(function ($q) use ($ticketIds, $customer) {
                $q->where(function ($q2) use ($ticketIds) {
                    $q2->where('target_type', Ticket::class)->whereIn('target_id', $ticketIds);
                })->orWhere(function ($q2) use ($customer) {
                    $q2->where('target_type', User::class)->where('target_id', $customer->id);
                });
            })->with('user:id,name')->latest()->take(100)->get()->map(fn ($log) => [
                'at' => $log->created_at,
                'kind' => 'activity',
                'label' => $log->action,
                'detail' => $log->target_label,
                'by' => $log->user->name ?? null,
            ])
            : collect();

        $replies = $ticketIds->isNotEmpty()
            ? TicketReply::whereIn('ticket_id', $ticketIds)->with('user:id,name')->latest()->take(100)->get()->map(fn ($reply) => [
                'at' => $reply->created_at,
                'kind' => $reply->is_internal ? 'internal-note' : 'reply',
                'label' => $reply->is_internal ? __('Internal note') : __('Reply'),
                'detail' => mb_substr((string) $reply->body, 0, 200),
                'by' => $reply->user->name ?? null,
            ])
            : collect();

        $timeline = $logs->concat($replies)->sortByDesc('at')->values();

        $page = max(1, (int) $request->integer('page', 1));
        $perPage = 20;
        $paginated = new LengthAwarePaginator(
            $timeline->forPage($page, $perPage)->values(),
            $timeline->count(),
            $perPage,
            $page,
            ['path' => $request->url(), 'query' => $request->query()]
        );

        $relatedTickets = (clone $ticketsQuery)->with(['assignedTo', 'department'])->latest()->take(5)->get();

        $attachments = $ticketIds->isNotEmpty()
            ? TicketAttachment::whereIn('ticket_id', $ticketIds)
                ->where('is_internal', false)
                ->latest()->take(10)->get()
            : collect();

        return view('admin.customers.show', [
            'customer' => $customer,
            'organization' => $organization,
            'tags' => $tags,
            'stats' => [
                'total' => $total,
                'open' => $open,
                'resolved' => $resolved,
                'avg_resolution_hours' => $avgResolutionSeconds !== null ? round(((float) $avgResolutionSeconds) / 3600, 1) : null,
                'avg_first_response_hours' => $avgFirstResponseSeconds !== null ? round(((float) $avgFirstResponseSeconds) / 3600, 1) : null,
                'sla_compliance' => $resolved > 0 ? round($slaCompliant / $resolved * 100, 1) : null,
                'csat_avg' => $csatAvg !== null ? round((float) $csatAvg, 1) : null,
                'reopen_rate' => $total > 0 ? round($reopenCount / $total * 100, 1) : 0.0,
                'reopen_count' => $reopenCount,
            ],
            'timeline' => $paginated,
            'relatedTickets' => $relatedTickets,
            'attachments' => $attachments,
        ]);
    }

    public function update(Request $request, User $customer): RedirectResponse
    {
        abort_unless($request->user()->can('manage_users'), 403);

        $validated = $request->validate([
            'internal_notes' => ['nullable', 'string', 'max:5000'],
            'vip' => ['sometimes', 'boolean'],
        ]);

        $customer->forceFill([
            'internal_notes' => $validated['internal_notes'] ?? null,
            'vip' => (bool) ($validated['vip'] ?? false),
        ])->save();

        return back()->with('success', __('Customer notes updated.'));
    }
}
