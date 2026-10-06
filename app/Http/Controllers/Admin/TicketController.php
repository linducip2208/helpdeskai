<?php

namespace App\Http\Controllers\Admin;

use App\Events\TicketViewing;
use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Department;
use App\Models\Ticket;
use App\Models\TicketAttachment;
use App\Models\TicketCustomField;
use App\Models\User;
use App\Services\ActivityLogService;
use App\Services\CustomFieldService;
use App\Services\TicketService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class TicketController extends Controller
{
    public function __construct(protected TicketService $ticketService) {}

    public function index(Request $request): View
    {
        $tickets = Ticket::with(['user', 'assignedTo', 'department', 'category'])
            ->when($request->status, fn ($q) => $q->where('status', $request->status))
            ->when($request->priority, fn ($q) => $q->where('priority', $request->priority))
            ->when($request->department_id, fn ($q) => $q->where('department_id', $request->department_id))
            ->when($request->assigned_to, fn ($q) => $q->where('assigned_to', $request->assigned_to))
            ->when($request->search, fn ($q) => $q->where(function ($q) use ($request) {
                $q->where('subject', 'like', "%{$request->search}%")
                    ->orWhere('uid', 'like', "%{$request->search}%");
            }))
            ->latest()
            ->paginate($request->per_page ?? 25)
            ->withQueryString();

        return view('admin.tickets.index', [
            'tickets' => $tickets,
            'filters' => $request->only(['status', 'priority', 'department_id', 'assigned_to', 'search']),
            'agents' => User::whereHas('roles', fn ($q) => $q->where('name', 'agent'))->get(['id', 'name']),
        ]);
    }

    public function show(Ticket $ticket): View
    {
        $ticket->load(['user', 'assignedTo', 'department', 'category', 'replies.user', 'replies.attachments', 'attachments']);

        broadcast(new TicketViewing($ticket, auth()->user()));

        return view('admin.tickets.show', [
            'ticket' => $ticket,
            'agents' => User::whereHas('roles', fn ($q) => $q->where('name', 'agent'))->get(['id', 'name']),
            'customFieldValues' => $this->customFieldValues($ticket),
            'timeEntries' => $ticket->timeEntries()->with('user:id,name')->latest()->take(10)->get(),
            'timeTotal' => (int) $ticket->timeEntries()->sum('minutes'),
        ]);
    }

    public function update(Request $request, Ticket $ticket): RedirectResponse
    {
        $validated = $request->validate([
            'status' => ['sometimes', 'string', Rule::in(['open', 'in_progress', 'waiting', 'answered', 'resolved', 'closed'])],
            'priority' => ['sometimes', 'string', Rule::in(['low', 'medium', 'high', 'urgent'])],
            'assigned_to' => 'sometimes|nullable|exists:users,id',
            'department_id' => 'sometimes|exists:departments,id',
            'subject' => 'sometimes|string|max:255',
        ]);

        $ticket->update($validated);

        ActivityLogService::logCustom(
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

        ActivityLogService::logCustom(auth()->id(), 'ticket_delete', Ticket::class, $ticket->id, $ticket->subject);

        return redirect()->route('admin.tickets.index')->with('success', 'Ticket deleted.');
    }

    public function bulkAction(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'exists:tickets,id',
            'action' => 'required|string|in:status_change,assign,delete',
            'status' => ['required_if:action,status_change', 'string', Rule::in(['open', 'in_progress', 'waiting', 'answered', 'resolved', 'closed'])],
            'assigned_to' => 'required_if:action,assign|exists:users,id',
        ]);

        $query = Ticket::whereIn('id', $validated['ids']);

        if ($validated['action'] === 'status_change') {
            $query->update(['status' => $validated['status']]);
        } elseif ($validated['action'] === 'assign') {
            abort_unless($request->user()->can('tickets.assign'), 403);
            $query->update(['assigned_to' => $validated['assigned_to']]);
        } elseif ($validated['action'] === 'delete') {
            abort_unless($request->user()->can('tickets.delete'), 403);
            $query->delete();
        }

        ActivityLogService::logCustom(auth()->id(), 'ticket_bulk_action', Ticket::class, null, 'Bulk action: '.$validated['action'], $validated);

        return back()->with('success', 'Bulk action completed.');
    }

    public function star(Ticket $ticket): JsonResponse
    {
        $ticket->update(['is_starred' => ! $ticket->is_starred]);

        return response()->json(['is_starred' => $ticket->is_starred]);
    }

    public function create(): View
    {
        return view('admin.tickets.create', [
            'departments' => Department::all(['id', 'name']),
            'categories' => Category::all(['id', 'name']),
            'agents' => User::whereHas('roles', fn ($q) => $q->where('name', 'agent'))->get(['id', 'name']),
            'customers' => User::whereHas('roles', fn ($q) => $q->where('name', 'customer'))->get(['id', 'name']),
            'customFields' => app(CustomFieldService::class)->forDepartment(null),
            'fieldsUrl' => route('admin.tickets.fields'),
        ]);
    }

    public function customFields(Request $request): JsonResponse
    {
        $fields = app(CustomFieldService::class)->forDepartment($request->integer('department_id') ?: null);

        return response()->json([
            'success' => true,
            'data' => $fields->filter(fn ($f) => $f->department_id !== null)->values()->map(fn ($f) => [
                'name' => $f->name,
                'label' => $f->label,
                'type' => $f->type,
                'required' => (bool) $f->is_required,
                'options' => $f->optionList(),
            ]),
            'message' => 'Custom fields retrieved.',
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $custom = app(CustomFieldService::class);

        $validated = $request->validate(array_merge([
            'subject' => 'required|string|max:255',
            'description' => 'required|string',
            'user_id' => 'required|exists:users,id',
            'department_id' => 'nullable|exists:departments,id',
            'category_id' => 'nullable|exists:categories,id',
            'assigned_to' => 'nullable|exists:users,id',
            'priority' => ['nullable', 'string', Rule::in(['low', 'medium', 'high', 'urgent'])],
            'status' => ['nullable', 'string', Rule::in(['open', 'in_progress', 'waiting', 'answered', 'resolved', 'closed'])],
        ], $custom->rules($request->integer('department_id') ?: null)));

        $validated['body'] = $validated['description'];
        unset($validated['description']);
        $validated['priority'] = $validated['priority'] ?? 'medium';
        $validated['status'] = $validated['status'] ?? 'open';
        $validated['custom_fields'] = $custom->extract($request->integer('department_id') ?: null, $validated);

        $ticket = $this->ticketService->createTicket($validated);

        return redirect()->route('admin.tickets.show', $ticket)->with('success', 'Ticket created.');
    }

    public function edit(Ticket $ticket): View
    {
        return view('admin.tickets.edit', [
            'ticket' => $ticket,
            'departments' => Department::all(['id', 'name']),
            'categories' => Category::all(['id', 'name']),
            'agents' => User::whereHas('roles', fn ($q) => $q->where('name', 'agent'))->get(['id', 'name']),
        ]);
    }

    public function reply(Request $request, Ticket $ticket): RedirectResponse
    {
        $validated = $request->validate(array_merge([
            'body' => 'required|string',
            'is_internal' => 'nullable|boolean',
        ], TicketService::attachmentRules()));

        $isInternal = (bool) ($validated['is_internal'] ?? false);

        $reply = $this->ticketService->addReply(
            $ticket,
            auth()->id(),
            $validated['body'],
            $isInternal,
        );

        if (! empty($validated['attachments'])) {
            $this->ticketService->addAttachments($ticket, $reply, $validated['attachments'], auth()->id(), $isInternal);
        }

        return back()->with('success', 'Reply added.');
    }

    public function downloadAttachment(TicketAttachment $attachment): StreamedResponse
    {
        $path = $attachment->path;

        abort_unless($path && Storage::disk('local')->exists($path), 404);

        return Storage::disk('local')->download($path, $attachment->original_name);
    }

    /**
     * @return array<string, string>
     */
    protected function customFieldValues(Ticket $ticket): array
    {
        $values = $ticket->custom_fields ?? [];

        if ($values === []) {
            return [];
        }

        $labels = TicketCustomField::whereIn('name', array_keys($values))->pluck('label', 'name');

        $out = [];
        foreach ($values as $name => $value) {
            $out[$labels[$name] ?? $name] = (string) $value;
        }

        return $out;
    }

    public function merge(Request $request, Ticket $ticket): RedirectResponse
    {
        $validated = $request->validate([
            'target_uid' => 'required|string|max:32',
        ]);

        $secondary = Ticket::where('uid', $validated['target_uid'])->first();
        abort_if(! $secondary, 422, 'Target ticket not found.');

        $this->ticketService->mergeTickets($ticket, $secondary, auth()->id());

        return redirect()->route('admin.tickets.show', $ticket)->with('success', "Merged {$secondary->uid} into {$ticket->uid}.");
    }

    public function logTime(Request $request, Ticket $ticket): RedirectResponse
    {
        $validated = $request->validate([
            'minutes' => 'required|integer|min:1|max:1440',
            'note' => 'nullable|string|max:255',
            'worked_at' => 'nullable|date|before_or_equal:today',
        ]);

        $this->ticketService->logTime(
            $ticket,
            auth()->id(),
            $validated['minutes'],
            $validated['note'] ?? null,
            $validated['worked_at'] ?? null
        );

        return back()->with('success', 'Time logged.');
    }

    public function suggest(Ticket $ticket): JsonResponse
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
        ActivityLogService::logCustom(auth()->id(), 'ticket_assign', Ticket::class, $ticket->id, $ticket->subject, $validated);

        return back()->with('success', 'Ticket assigned.');
    }

    public function updateStatus(Request $request, Ticket $ticket): RedirectResponse
    {
        $validated = $request->validate(['status' => ['required', 'string', Rule::in(['open', 'in_progress', 'waiting', 'answered', 'resolved', 'closed'])]]);
        $ticket->update($validated);
        ActivityLogService::logCustom(auth()->id(), 'ticket_status', Ticket::class, $ticket->id, $ticket->subject, $validated);

        return back()->with('success', 'Status updated.');
    }

    public function updatePriority(Request $request, Ticket $ticket): RedirectResponse
    {
        $validated = $request->validate(['priority' => ['required', 'string', Rule::in(['low', 'medium', 'high', 'urgent'])]]);
        $ticket->update($validated);
        ActivityLogService::logCustom(auth()->id(), 'ticket_priority', Ticket::class, $ticket->id, $ticket->subject, $validated);

        return back()->with('success', 'Priority updated.');
    }
}
