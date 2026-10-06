<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Department;
use App\Models\Ticket;
use App\Models\TicketAttachment;
use App\Services\TicketService;
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
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'subject' => 'required|string|max:255',
            'body' => 'required|string',
            'department_id' => 'required|exists:departments,id',
            'category_id' => 'nullable|exists:categories,id',
            'priority' => 'nullable|in:low,medium,high,urgent',
        ]);

        $validated['user_id'] = auth()->id();
        $ticket = $this->ticketService->createTicket($validated);

        return redirect()->route('tickets.show', $ticket)->with('success', 'Ticket created.');
    }

    public function show(Ticket $ticket): View
    {
        abort_unless($ticket->user_id === auth()->id(), 403);

        $ticket->load(['user', 'assignedTo', 'department', 'category', 'replies.user', 'replies.attachments', 'attachments']);

        return view('user.tickets.show', ['ticket' => $ticket]);
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
}
