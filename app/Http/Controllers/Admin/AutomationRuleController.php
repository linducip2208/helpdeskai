<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AutomationRule;
use App\Services\ActivityLogService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AutomationRuleController extends Controller
{
    public function index(): View
    {
        return view('admin.automation-rules.index', [
            'rules' => AutomationRule::orderBy('sort_order')->get(),
        ]);
    }

    public function create(): View
    {
        return view('admin.automation-rules.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'trigger_event' => 'required|string',
            'conditions' => 'required|array',
            'actions' => 'required|array',
            'is_active' => 'boolean',
            'sort_order' => 'nullable|integer',
        ]);

        $rule = AutomationRule::create($validated);

        ActivityLogService::log(auth()->id(), 'automation_create', AutomationRule::class, $rule->id, $rule->name);

        return redirect()->route('admin.automation-rules.index')->with('success', 'Automation rule created.');
    }

    public function edit(AutomationRule $automationRule): View
    {
        return view('admin.automation-rules.edit', [
            'rule' => $automationRule,
        ]);
    }

    public function update(Request $request, AutomationRule $automationRule): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'trigger_event' => 'required|string',
            'conditions' => 'required|array',
            'actions' => 'required|array',
            'is_active' => 'boolean',
            'sort_order' => 'nullable|integer',
        ]);

        $automationRule->update($validated);

        ActivityLogService::log(auth()->id(), 'automation_update', AutomationRule::class, $automationRule->id, $automationRule->name);

        return redirect()->route('admin.automation-rules.index')->with('success', 'Automation rule updated.');
    }

    public function destroy(AutomationRule $automationRule): RedirectResponse
    {
        $automationRule->delete();

        ActivityLogService::log(auth()->id(), 'automation_delete', AutomationRule::class, $automationRule->id, $automationRule->name);

        return redirect()->route('admin.automation-rules.index')->with('success', 'Automation rule deleted.');
    }
}
