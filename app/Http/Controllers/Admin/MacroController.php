<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Macro;
use App\Models\Ticket;
use App\Services\ActivityLogService;
use App\Services\TicketService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class MacroController extends Controller
{
    public function index(): View
    {
        return view('admin.macros.index', [
            'macros' => Macro::with('user:id,name')->latest()->paginate(25),
        ]);
    }

    public function create(): View
    {
        return view('admin.macros.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'visibility' => 'required|string|in:personal,shared',
            'actions.reply' => 'nullable|string|max:5000',
            'actions.internal_note' => 'nullable|string|max:5000',
            'actions.status' => 'nullable|string|in:open,in_progress,waiting,answered,resolved,closed',
            'actions.priority' => 'nullable|string|in:low,medium,high,urgent',
            'actions.assigned_to' => 'nullable|exists:users,id',
            'actions.add_tags' => 'nullable|string|max:500',
            'actions.remove_tags' => 'nullable|string|max:500',
        ]);

        $actions = array_filter([
            'reply' => $validated['actions']['reply'] ?? null,
            'internal_note' => $validated['actions']['internal_note'] ?? null,
            'status' => $validated['actions']['status'] ?? null,
            'priority' => $validated['actions']['priority'] ?? null,
            'assigned_to' => $validated['actions']['assigned_to'] ?? null,
            'add_tags' => isset($validated['actions']['add_tags']) ? array_filter(array_map('trim', explode(',', $validated['actions']['add_tags']))) : [],
            'remove_tags' => isset($validated['actions']['remove_tags']) ? array_filter(array_map('trim', explode(',', $validated['actions']['remove_tags']))) : [],
        ]);

        Macro::create([
            'name' => $validated['name'],
            'visibility' => $validated['visibility'],
            'user_id' => auth()->id(),
            'actions' => $actions,
            'is_active' => true,
        ]);

        ActivityLogService::logCustom(auth()->id(), 'macro_create', Macro::class, null, $validated['name']);

        return redirect()->route('admin.macros.index')->with('success', 'Macro created.');
    }

    public function destroy(Macro $macro): RedirectResponse
    {
        abort_unless($macro->user_id === auth()->id() || auth()->user()->can('macros.manage'), 403);
        $macro->delete();

        return back()->with('success', 'Macro deleted.');
    }

    public function apply(Request $request, Macro $macro, Ticket $ticket): RedirectResponse
    {
        abort_unless($macro->visibleTo($request->user()), 403);
        abort_unless($request->user()->can('tickets.update'), 403);

        app(TicketService::class)->applyMacro($ticket, $macro->actions ?? [], $request->user()->id);

        return back()->with('success', "Macro '{$macro->name}' applied.");
    }
}
