<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SlaEscalationRule;
use App\Services\ActivityLogService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SlaEscalationRuleController extends Controller
{
    public function index(): View
    {
        return view('admin.sla-escalations.index', [
            'rules' => SlaEscalationRule::latest()->paginate(25),
        ]);
    }

    public function create(): View
    {
        return view('admin.sla-escalations.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'trigger' => 'required|string|in:'.implode(',', SlaEscalationRule::TRIGGERS),
            'after_minutes' => 'required|integer|min:0|max:10080',
            'action_priority' => 'nullable|string|in:low,medium,high,urgent',
            'action_assign_role' => 'nullable|string|in:agent,manager,admin',
            'notify_assignee' => 'boolean',
            'is_active' => 'boolean',
        ]);

        $rule = SlaEscalationRule::create($validated);

        ActivityLogService::logCustom(auth()->id(), 'sla_escalation_create', SlaEscalationRule::class, $rule->id, $rule->name);

        return redirect()->route('admin.sla-escalations.index')->with('success', 'Escalation rule created.');
    }

    public function edit(SlaEscalationRule $slaEscalation): View
    {
        return view('admin.sla-escalations.edit', ['rule' => $slaEscalation]);
    }

    public function update(Request $request, SlaEscalationRule $slaEscalation): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'trigger' => 'required|string|in:'.implode(',', SlaEscalationRule::TRIGGERS),
            'after_minutes' => 'required|integer|min:0|max:10080',
            'action_priority' => 'nullable|string|in:low,medium,high,urgent',
            'action_assign_role' => 'nullable|string|in:agent,manager,admin',
            'notify_assignee' => 'boolean',
            'is_active' => 'boolean',
        ]);

        $slaEscalation->update($validated);

        ActivityLogService::logCustom(auth()->id(), 'sla_escalation_update', SlaEscalationRule::class, $slaEscalation->id, $slaEscalation->name);

        return redirect()->route('admin.sla-escalations.index')->with('success', 'Escalation rule updated.');
    }

    public function destroy(SlaEscalationRule $slaEscalation): RedirectResponse
    {
        $slaEscalation->delete();

        return back()->with('success', 'Escalation rule deleted.');
    }
}
