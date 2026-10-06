<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Ticket;
use App\Services\TicketService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TicketController extends Controller
{
    protected TicketService $ticketService;

    public function __construct(TicketService $ticketService)
    {
        $this->ticketService = $ticketService;
    }

    public function index(Request $request): JsonResponse
    {
        $tickets = Ticket::with(['user', 'assignedTo', 'department'])
            ->when($request->status, fn($q) => $q->where('status', $request->status))
            ->when($request->priority, fn($q) => $q->where('priority', $request->priority))
            ->when($request->assigned_to, fn($q) => $q->where('assigned_to', $request->assigned_to))
            ->latest()
            ->paginate($request->per_page ?? 25);

        return response()->json($tickets);
    }

    public function show(Ticket $ticket): JsonResponse
    {
        $ticket->load(['user', 'assignedTo', 'department', 'category', 'replies.user', 'attachments']);
        return response()->json(['data' => $ticket]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'subject' => 'required|string|max:255',
            'body' => 'required|string',
            'department_id' => 'required|exists:departments,id',
            'category_id' => 'nullable|exists:categories,id',
            'priority' => 'nullable|string|in:low,medium,high,urgent',
        ]);

        $validated['user_id'] = $request->user()->id;
        $ticket = $this->ticketService->createTicket($validated);

        return response()->json(['data' => $ticket->load(['user', 'department'])], 201);
    }

    public function update(Request $request, Ticket $ticket): JsonResponse
    {
        $validated = $request->validate([
            'status' => 'sometimes|string',
            'priority' => 'sometimes|string',
            'assigned_to' => 'sometimes|nullable|exists:users,id',
            'subject' => 'sometimes|string|max:255',
        ]);

        $ticket->update($validated);
        return response()->json(['data' => $ticket]);
    }

    public function destroy(Ticket $ticket): JsonResponse
    {
        $ticket->delete();
        return response()->json(['message' => 'Ticket deleted.'], 200);
    }
}
