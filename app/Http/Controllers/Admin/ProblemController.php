<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\Incident;
use App\Models\KnowledgeArticle;
use App\Models\Problem;
use App\Models\Ticket;
use App\Models\User;
use App\Services\ActivityLogService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ProblemController extends Controller
{
    public function index(): View
    {
        return view('admin.problems.index', [
            'problems' => Problem::with(['owner', 'tickets', 'incidents'])->latest()->paginate(20),
        ]);
    }

    public function create(): View
    {
        return view('admin.problems.create', [
            'users' => User::orderBy('name')->get(['id', 'name']),
            'articles' => KnowledgeArticle::orderBy('title')->get(['id', 'title']),
        ]);
    }

    protected function rules(): array
    {
        return [
            'title' => 'required|string|max:255',
            'root_cause' => 'nullable|string',
            'symptoms' => 'nullable|string',
            'workaround' => 'nullable|string',
            'permanent_fix' => 'nullable|string',
            'status' => 'sometimes|in:open,investigating,resolved,closed',
            'owner_id' => 'nullable|exists:users,id',
            'knowledge_article_id' => 'nullable|exists:knowledge_articles,id',
        ];
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate($this->rules());

        $problem = Problem::create($validated);

        ActivityLogService::logCustom(auth()->id(), 'problem_create', Problem::class, $problem->id, $problem->title);

        return redirect()->route('admin.problems.show', $problem)->with('success', __('Problem created.'));
    }

    public function show(Problem $problem): View
    {
        $problem->load(['owner', 'article', 'tickets', 'incidents']);

        $logs = ActivityLog::where('target_type', Problem::class)
            ->where('target_id', $problem->id)
            ->with('user')
            ->latest()
            ->get();

        return view('admin.problems.show', [
            'problem' => $problem,
            'logs' => $logs,
        ]);
    }

    public function edit(Problem $problem): View
    {
        return view('admin.problems.edit', [
            'problem' => $problem,
            'users' => User::orderBy('name')->get(['id', 'name']),
            'articles' => KnowledgeArticle::orderBy('title')->get(['id', 'title']),
        ]);
    }

    public function update(Request $request, Problem $problem): RedirectResponse
    {
        $validated = $request->validate($this->rules());

        $problem->update($validated);

        ActivityLogService::logCustom(auth()->id(), 'problem_update', Problem::class, $problem->id, $problem->title);

        return redirect()->route('admin.problems.show', $problem)->with('success', __('Problem updated.'));
    }

    public function destroy(Problem $problem): RedirectResponse
    {
        $label = $problem->title;
        $id = $problem->id;
        $problem->delete();

        ActivityLogService::logCustom(auth()->id(), 'problem_delete', Problem::class, $id, $label);

        return redirect()->route('admin.problems.index')->with('success', __('Problem deleted.'));
    }

    public function attachTicket(Request $request, Problem $problem): RedirectResponse
    {
        $validated = $request->validate([
            'ticket_id' => 'required|exists:tickets,id',
        ]);

        if ($problem->tickets()->whereKey($validated['ticket_id'])->exists()) {
            return back()->with('error', __('Ticket already linked to this problem.'));
        }

        $problem->tickets()->attach($validated['ticket_id']);

        ActivityLogService::logCustom(auth()->id(), 'problem_ticket_attach', Problem::class, $problem->id, $problem->title, [
            'ticket_id' => $validated['ticket_id'],
        ]);

        return back()->with('success', __('Ticket linked.'));
    }

    public function detachTicket(Problem $problem, Ticket $ticket): RedirectResponse
    {
        $problem->tickets()->detach($ticket->id);

        ActivityLogService::logCustom(auth()->id(), 'problem_ticket_detach', Problem::class, $problem->id, $problem->title, [
            'ticket_id' => $ticket->id,
        ]);

        return back()->with('success', __('Ticket unlinked.'));
    }

    public function attachIncident(Request $request, Problem $problem): RedirectResponse
    {
        $validated = $request->validate([
            'incident_id' => 'required|exists:incidents,id',
        ]);

        if ($problem->incidents()->whereKey($validated['incident_id'])->exists()) {
            return back()->with('error', __('Incident already linked to this problem.'));
        }

        $problem->incidents()->attach($validated['incident_id']);

        ActivityLogService::logCustom(auth()->id(), 'problem_incident_attach', Problem::class, $problem->id, $problem->title, [
            'incident_id' => $validated['incident_id'],
        ]);

        return back()->with('success', __('Incident linked.'));
    }

    public function detachIncident(Problem $problem, Incident $incident): RedirectResponse
    {
        $problem->incidents()->detach($incident->id);

        ActivityLogService::logCustom(auth()->id(), 'problem_incident_detach', Problem::class, $problem->id, $problem->title, [
            'incident_id' => $incident->id,
        ]);

        return back()->with('success', __('Incident unlinked.'));
    }

    public function transition(Request $request, Problem $problem): RedirectResponse
    {
        $validated = $request->validate([
            'status' => 'required|in:open,investigating,resolved,closed,investigating_reopen',
        ]);

        $status = $validated['status'] === 'investigating_reopen' ? 'investigating' : $validated['status'];
        $problem->status = $status;
        $problem->save();

        ActivityLogService::logCustom(auth()->id(), 'problem_status', Problem::class, $problem->id, $problem->title, [
            'status' => $status,
        ]);

        return back()->with('success', __('Problem status updated.'));
    }
}
