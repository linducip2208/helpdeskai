<?php

namespace App\Http\Controllers\Api;

use App\Enums\TicketStatus;
use App\Http\Controllers\Controller;
use App\Models\Ticket;
use App\Services\TicketService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class TicketController extends Controller
{
    protected TicketService $ticketService;

    public function __construct(TicketService $ticketService)
    {
        $this->ticketService = $ticketService;
    }

    protected function isStaff(Request $request): bool
    {
        return $request->user()->hasRole(['admin', 'manager', 'agent']);
    }

    protected function baseQuery(Request $request)
    {
        $query = Ticket::with(['user', 'assignedTo', 'department']);

        if (! $this->isStaff($request)) {
            $query->where('user_id', $request->user()->id);
        }

        return $query;
    }

    protected function authorizeTicket(Request $request, Ticket $ticket): void
    {
        $user = $request->user();

        $allowed = $this->isStaff($request)
            || $ticket->user_id === $user->id
            || $ticket->assigned_to === $user->id;

        abort_unless($allowed, 403, 'You are not authorized to access this ticket.');
    }

    public function index(Request $request): JsonResponse
    {
        $tickets = $this->baseQuery($request)
            ->when($request->status, fn ($q) => $q->where('status', $request->status))
            ->when($request->priority, fn ($q) => $q->where('priority', $request->priority))
            ->when($request->assigned_to, fn ($q) => $q->where('assigned_to', $request->assigned_to))
            ->latest()
            ->paginate(min((int) ($request->per_page ?? 25), 100));

        return response()->json([
            'success' => true,
            'data' => $tickets,
            'message' => 'Tickets retrieved.',
        ]);
    }

    public function show(Request $request, Ticket $ticket): JsonResponse
    {
        $this->authorizeTicket($request, $ticket);

        $ticket->load(['user', 'assignedTo', 'department', 'category', 'replies.user', 'attachments']);

        if (! $this->isStaff($request)) {
            $ticket->setRelation('replies', $ticket->replies->where('is_internal', false)->values());
        }

        return response()->json([
            'success' => true,
            'data' => $ticket,
            'message' => 'Ticket retrieved.',
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'subject' => 'required|string|max:255',
            'body' => 'required|string',
            'department_id' => 'required|exists:departments,id',
            'category_id' => 'nullable|exists:categories,id',
            'priority' => ['nullable', 'string', Rule::in(['low', 'medium', 'high', 'urgent'])],
        ]);

        $validated['user_id'] = $request->user()->id;
        $ticket = $this->ticketService->createTicket($validated);

        return response()->json([
            'success' => true,
            'data' => $ticket->load(['user', 'department']),
            'message' => 'Ticket created.',
        ], 201);
    }

    public function update(Request $request, Ticket $ticket): JsonResponse
    {
        $this->authorizeTicket($request, $ticket);

        if (! $this->isStaff($request)) {
            abort_unless($ticket->user_id === $request->user()->id, 403);

            $validated = $request->validate([
                'subject' => 'sometimes|string|max:255',
            ]);

            $ticket = $this->ticketService->updateTicket($ticket, $validated, $request->user()->id);

            return response()->json([
                'success' => true,
                'data' => $ticket,
                'message' => 'Ticket updated.',
            ]);
        }

        $validated = $request->validate([
            'status' => ['sometimes', 'string', Rule::in(array_column(TicketStatus::cases(), 'value'))],
            'priority' => ['sometimes', 'string', Rule::in(['low', 'medium', 'high', 'urgent'])],
            'assigned_to' => 'sometimes|nullable|exists:users,id',
            'department_id' => 'sometimes|exists:departments,id',
            'category_id' => 'sometimes|nullable|exists:categories,id',
            'subject' => 'sometimes|string|max:255',
        ]);

        $ticket = $this->ticketService->updateTicket($ticket, $validated, $request->user()->id);

        return response()->json([
            'success' => true,
            'data' => $ticket,
            'message' => 'Ticket updated.',
        ]);
    }

    public function destroy(Request $request, Ticket $ticket): JsonResponse
    {
        abort_unless($request->user()->hasRole(['admin', 'manager']), 403, 'Only staff managers can delete tickets.');

        $this->ticketService->deleteTicket($ticket, $request->user()->id);

        return response()->json([
            'success' => true,
            'data' => null,
            'message' => 'Ticket deleted.',
        ], 200);
    }
}
