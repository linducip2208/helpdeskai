<?php

namespace App\Http\Controllers\User;

use App\Enums\TicketStatus;
use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Department;
use App\Models\Ticket;
use App\Models\TicketAttachment;
use App\Models\TicketCustomField;
use App\Services\ActivityLogService;
use App\Services\AutomationService;
use App\Services\CustomFieldService;
use App\Services\TicketService;
use App\Services\WebhookService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class TicketController extends Controller
{
    protected TicketService $ticketService;

    public function __construct(TicketService $ticketService)
    {
        $this->ticketService = $ticketService;
    }

    public function index(Request $request): View
    {
        $tickets = Ticket::where('user_id', auth()->id())
            ->with(['department', 'assignedTo'])
            ->when($request->status, fn ($q) => $q->where('status', $request->status))
            ->latest()
            ->paginate(15);

        return view('user.tickets.index', ['tickets' => $tickets]);
    }

    public function create(): View
    {
        return view('user.tickets.create', [
            'departments' => Department::where('is_active', true)->get(),
            'categories' => Category::where('is_active', true)->get(),
            'customFields' => app(CustomFieldService::class)->forDepartment(null),
            'fieldsUrl' => route('tickets.fields'),
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
            'body' => 'required|string',
            'department_id' => 'required|exists:departments,id',
            'category_id' => 'nullable|exists:categories,id',
            'priority' => 'nullable|in:low,medium,high,urgent',
        ], $custom->rules($request->integer('department_id') ?: null)));

        $validated['user_id'] = auth()->id();
        $validated['custom_fields'] = $custom->extract($request->integer('department_id') ?: null, $validated);
        $ticket = $this->ticketService->createTicket($validated);

        return redirect()->route('tickets.show', $ticket)->with('success', 'Ticket created.');
    }

    public function show(Ticket $ticket): View
    {
        abort_unless($ticket->user_id === auth()->id(), 403);

        $ticket->load(['user', 'assignedTo', 'department', 'category', 'replies.user', 'replies.attachments', 'attachments']);

        return view('user.tickets.show', [
            'ticket' => $ticket,
            'customFieldValues' => $this->customFieldValues($ticket),
        ]);
    }

    public function rate(Request $request, Ticket $ticket): RedirectResponse
    {
        abort_unless($ticket->user_id === auth()->id(), 403);
        $status = $ticket->status instanceof TicketStatus
            ? $ticket->status->value
            : (string) $ticket->status;
        abort_unless(in_array($status, ['resolved', 'closed'], true), 422, 'You can only rate resolved tickets.');
        abort_if($ticket->satisfaction_rating !== null, 422, 'This ticket has already been rated.');

        $validated = $request->validate([
            'satisfaction_rating' => 'required|integer|min:1|max:5',
            'satisfaction_comment' => 'nullable|string|max:1000',
        ]);

        $ticket->update($validated);

        ActivityLogService::log('ticket_rated', $ticket, $ticket->subject, ['rating' => $validated['satisfaction_rating']]);
        app(AutomationService::class)->fire('csat.submitted', $ticket->fresh() ?? $ticket, ['rating' => $validated['satisfaction_rating']]);
        app(WebhookService::class)->dispatchGeneric('csat.created', [
            'ticket' => ['id' => $ticket->id, 'uid' => $ticket->uid, 'subject' => $ticket->subject],
            'rating' => $validated['satisfaction_rating'],
        ], 'csat-'.$ticket->id.'-'.$validated['satisfaction_rating']);

        return back()->with('success', 'Thank you for your feedback.');
    }

    public function poll(Ticket $ticket): JsonResponse
    {
        abort_unless($ticket->user_id === auth()->id(), 403);

        return response()->json([
            'success' => true,
            'data' => [
                'replies_count' => $ticket->replies()->where('is_internal', false)->count(),
                'last_reply_id' => $ticket->replies()->where('is_internal', false)->max('id'),
                'status' => $ticket->status instanceof TicketStatus ? $ticket->status->value : (string) $ticket->status,
                'updated_at' => $ticket->updated_at->toIso8601String(),
            ],
            'message' => 'Ticket state retrieved.',
        ]);
    }

    public function reply(Request $request, Ticket $ticket): RedirectResponse
    {
        abort_unless($ticket->user_id === auth()->id(), 403);

        $validated = $request->validate(array_merge(
            ['body' => 'required|string|max:20000'],
            TicketService::attachmentRules()
        ));

        $reply = $this->ticketService->addReply($ticket, auth()->id(), $validated['body'], false);

        if (! empty($validated['attachments'])) {
            $this->ticketService->addAttachments($ticket, $reply, $validated['attachments'], auth()->id(), false);
        }

        return redirect()->back()->with('success', 'Reply sent.');
    }

    public function downloadAttachment(TicketAttachment $attachment): StreamedResponse
    {
        abort_unless($attachment->ticket->user_id === auth()->id(), 403);
        abort_if($attachment->is_internal, 403);

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
}
