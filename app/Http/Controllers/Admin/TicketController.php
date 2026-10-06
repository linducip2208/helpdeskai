<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Department;
use App\Models\Ticket;
use App\Models\TicketReply;
use App\Models\User;
use App\Services\ActivityLogService;
use App\Services\TicketService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TicketController extends Controller
{
    public function __construct(protected TicketService $ticketService) {}


    public function index(Request $request): View
    {
        $tickets = Ticket::with(['user', 'assignedTo', 'department', 'category'])
            ->when($request->status, fn($q) => $q->where('status', $request->status))
            ->when($request->priority, fn($q) => $q->where('priority', $request->priority))
            ->when($request->department_id, fn($q) => $q->where('department_id', $request->department_id))
            ->when($request->assigned_to, fn($q) => $q->where('assigned_to', $request->assigned_to))
            ->when($request->search, fn($q) => $q->where(function ($q) use ($request) {
                $q->where('subject', 'like', "%{$request->search}%")
                  ->orWhere('uid', 'like', "%{$request->search}%");
            }))
            ->latest()
            ->paginate($request->per_page ?? 25)
            ->withQueryString();

        return view('admin.tickets.index', [
            'tickets' => $tickets,
            'filters' => $request->only(['status', 'priority', 'department_id', 'assigned_to', 'search']),
            'agents' => User::whereHas('roles', fn($q) => $q->where('name', 'agent'))->get(['id', 'name']),
        ]);
    }

    public function show(Ticket $ticket): View
    {
        $ticket->load(['user', 'assignedTo', 'department', 'category', 'replies.user', 'attachments']);

        return view('admin.tickets.show', [
            'ticket' => $ticket,
            'agents' => User::whereHas('roles', fn($q) => $q->where('name', 'agent'))->get(['id', 'name']),
        ]);
    }

    public function update(Request $request, Ticket $ticket): RedirectResponse
    {
        $validated = $request->validate([
            'status' => 'sometimes|string',
            'priority' => 'sometimes|string',
            'assigned_to' => 'sometimes|nullable|exists:users,id',
            'department_id' => 'sometimes|exists:departments,id',
            'subject' => 'sometimes|string|max:255',
        ]);

        $ticket->update($validated);

        ActivityLogService::log(
            auth()->id(),
            'ticket_update',
            Ticket::class,
            $ticket->id,
            $ticket->subject,
            $validated
        );

        return back()->with('success', 'Ticket updated.');
    }

    public function destroy(Ticket $ticket): RedirectResponse
    {
        $ticket->delete();

        ActivityLogService::log(auth()->id(), 'ticket_delete', Ticket::class, $ticket->id, $ticket->subject);

        return redirect()->route('admin.tickets.index')->with('success', 'Ticket deleted.');
    }

    public function bulkAction(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'exists:tickets,id',
            'action' => 'required|string|in:status_change,assign,delete',
            'status' => 'required_if:action,status_change|string',
            'assigned_to' => 'required_if:action,assign|exists:users,id',
        ]);

        $query = Ticket::whereIn('id', $validated['ids']);

        if ($validated['action'] === 'status_change') {
            $query->update(['status' => $validated['status']]);
        } elseif ($validated['action'] === 'assign') {
            $query->update(['assigned_to' => $validated['assigned_to']]);
        } elseif ($validated['action'] === 'delete') {
            $query->delete();
        }

        ActivityLogService::log(auth()->id(), 'ticket_bulk_action', Ticket::class, null, 'Bulk action: ' . $validated['action'], $validated);

        return back()->with('success', 'Bulk action completed.');
    }

    public function star(Ticket $ticket): \Illuminate\Http\JsonResponse
    {
        $ticket->update(['is_starred' => ! $ticket->is_starred]);

        return response()->json(['is_starred' => $ticket->is_starred]);
    }

    public function create(): View
    {
        return view('admin.tickets.create', [
            'departments' => Department::all(['id', 'name']),
            'categories' => Category::all(['id', 'name']),
            'agents' => User::whereHas('roles', fn($q) => $q->where('name', 'agent'))->get(['id', 'name']),
            'customers' => User::whereHas('roles', fn($q) => $q->where('name', 'customer'))->get(['id', 'name']),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'subject' => 'required|string|max:255',
            'description' => 'required|string',
            'user_id' => 'required|exists:users,id',
            'department_id' => 'nullable|exists:departments,id',
            'category_id' => 'nullable|exists:categories,id',
            'assigned_to' => 'nullable|exists:users,id',
            'priority' => 'nullable|string',
            'status' => 'nullable|string',
        ]);

        $validated['body'] = $validated['description'];
        unset($validated['description']);
        $validated['priority'] = $validated['priority'] ?? 'medium';
        $validated['status'] = $validated['status'] ?? 'open';

        $ticket = $this->ticketService->createTicket($validated);

        return redirect()->route('admin.tickets.show', $ticket)->with('success', 'Ticket created.');
    }

    public function edit(Ticket $ticket): View
    {
        return view('admin.tickets.edit', [
            'ticket' => $ticket,
            'departments' => Department::all(['id', 'name']),
            'categories' => Category::all(['id', 'name']),
            'agents' => User::whereHas('roles', fn($q) => $q->where('name', 'agent'))->get(['id', 'name']),
        ]);
    }

    public function reply(Request $request, Ticket $ticket): RedirectResponse
    {
        $validated = $request->validate([
            'body' => 'required|string',
            'is_internal' => 'nullable|boolean',
        ]);

        $this->ticketService->addReply(
            $ticket,
            auth()->id(),
            $validated['body'],
            (bool) ($validated['is_internal'] ?? false),
        );

        return back()->with('success', 'Reply added.');
    }

    public function suggest(Ticket $ticket): \Illuminate\Http\JsonResponse
    {
        $suggestion = $this->ticketService->suggestReply($ticket);

        if (! $suggestion) {
            return response()->json(['error' => 'AI suggestion not available. Enable the ticket.suggest feature with a provider/model first.'], 422);
        }

        return response()->json(['suggestion' => $suggestion]);
    }

    public function assign(Request $request, Ticket $ticket): RedirectResponse
    {
        $validated = $request->validate([
            'assigned_to' => 'required|exists:users,id',
        ]);

        $ticket->update(['assigned_to' => $validated['assigned_to']]);
        ActivityLogService::log(auth()->id(), 'ticket_assign', Ticket::class, $ticket->id, $ticket->subject, $validated);

        return back()->with('success', 'Ticket assigned.');
    }

    public function updateStatus(Request $request, Ticket $ticket): RedirectResponse
    {
        $validated = $request->validate(['status' => 'required|string']);
        $ticket->update($validated);
        ActivityLogService::log(auth()->id(), 'ticket_status', Ticket::class, $ticket->id, $ticket->subject, $validated);

        return back()->with('success', 'Status updated.');
    }

    public function updatePriority(Request $request, Ticket $ticket): RedirectResponse
    {
        $validated = $request->validate(['priority' => 'required|string']);
        $ticket->update($validated);
        ActivityLogService::log(auth()->id(), 'ticket_priority', Ticket::class, $ticket->id, $ticket->subject, $validated);

        return back()->with('success', 'Priority updated.');
    }
}
