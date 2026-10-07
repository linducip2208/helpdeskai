<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\Incident;
use App\Models\Ticket;
use App\Models\User;
use App\Services\ActivityLogService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class IncidentController extends Controller
{
    public function index(): View
    {
        return view('admin.incidents.index', [
            'incidents' => Incident::with(['owner', 'tickets'])->latest()->paginate(20),
        ]);
    }

    public function create(): View
    {
        return view('admin.incidents.create', [
            'users' => User::orderBy('name')->get(['id', 'name']),
        ]);
    }

    protected function rules(): array
    {
        return [
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'severity' => 'required|in:low,medium,high,critical',
            'status' => 'sometimes|in:identified,investigating,mitigated,resolved,closed',
            'owner_id' => 'nullable|exists:users,id',
            'root_cause' => 'nullable|string',
            'resolution' => 'nullable|string',
            'started_at' => 'nullable|date',
            'resolved_at' => 'nullable|date',
        ];
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate($this->rules());

        $incident = Incident::create($validated);

        ActivityLogService::logCustom(auth()->id(), 'incident_create', Incident::class, $incident->id, $incident->title);

        return redirect()->route('admin.incidents.show', $incident)->with('success', __('Incident created.'));
    }

    public function show(Incident $incident): View
    {
        $incident->load(['owner', 'tickets']);

        $logs = ActivityLog::where('target_type', Incident::class)
            ->where('target_id', $incident->id)
            ->with('user')
            ->latest()
            ->get();

        return view('admin.incidents.show', [
            'incident' => $incident,
            'logs' => $logs,
        ]);
    }

    public function edit(Incident $incident): View
    {
        return view('admin.incidents.edit', [
            'incident' => $incident,
            'users' => User::orderBy('name')->get(['id', 'name']),
        ]);
    }

    public function update(Request $request, Incident $incident): RedirectResponse
    {
        $validated = $request->validate($this->rules());

        $incident->update($validated);

        ActivityLogService::logCustom(auth()->id(), 'incident_update', Incident::class, $incident->id, $incident->title);

        return redirect()->route('admin.incidents.show', $incident)->with('success', __('Incident updated.'));
    }

    public function destroy(Incident $incident): RedirectResponse
    {
        $label = $incident->title;
        $id = $incident->id;
        $incident->delete();

        ActivityLogService::logCustom(auth()->id(), 'incident_delete', Incident::class, $id, $label);

        return redirect()->route('admin.incidents.index')->with('success', __('Incident deleted.'));
    }

    public function attachTicket(Request $request, Incident $incident): RedirectResponse
    {
        $validated = $request->validate([
            'ticket_id' => 'required|exists:tickets,id',
        ]);

        if ($incident->tickets()->whereKey($validated['ticket_id'])->exists()) {
            return back()->with('error', __('Ticket already linked to this incident.'));
        }

        $incident->tickets()->attach($validated['ticket_id']);

        ActivityLogService::logCustom(auth()->id(), 'incident_ticket_attach', Incident::class, $incident->id, $incident->title, [
            'ticket_id' => $validated['ticket_id'],
        ]);

        return back()->with('success', __('Ticket linked.'));
    }

    public function detachTicket(Incident $incident, Ticket $ticket): RedirectResponse
    {
        $incident->tickets()->detach($ticket->id);

        ActivityLogService::logCustom(auth()->id(), 'incident_ticket_detach', Incident::class, $incident->id, $incident->title, [
            'ticket_id' => $ticket->id,
        ]);

        return back()->with('success', __('Ticket unlinked.'));
    }

    public function transition(Request $request, Incident $incident): RedirectResponse
    {
        $validated = $request->validate([
            'status' => 'required|in:identified,investigating,mitigated,resolved,closed,investigating_reopen',
        ]);

        $status = $validated['status'];
        if ($status === 'investigating_reopen') {
            $status = 'investigating';
            $incident->resolved_at = null;
        } elseif (in_array($status, ['resolved', 'closed'], true)) {
            $incident->resolved_at = $incident->resolved_at ?? now();
        }

        $incident->status = $status;
        $incident->save();

        ActivityLogService::logCustom(auth()->id(), 'incident_status', Incident::class, $incident->id, $incident->title, [
            'status' => $status,
        ]);

        return back()->with('success', __('Incident status updated.'));
    }
}
