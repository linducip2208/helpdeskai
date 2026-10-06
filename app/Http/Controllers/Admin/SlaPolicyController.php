<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Department;
use App\Models\SlaPolicy;
use App\Services\ActivityLogService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SlaPolicyController extends Controller
{
    public function index(): View
    {
        return view('admin.sla-policies.index', [
            'policies' => SlaPolicy::with('department')->latest()->get(),
        ]);
    }

    public function create(): View
    {
        return view('admin.sla-policies.create', [
            'departments' => Department::where('is_active', true)->orderBy('name')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'department_id' => 'required|exists:departments,id',
            'priority' => 'required|string|in:low,medium,high,urgent',
            'first_response_time' => 'required|integer|min:1',
            'resolution_time' => 'required|integer|min:1',
            'is_active' => 'boolean',
        ]);

        $policy = SlaPolicy::create($validated);

        ActivityLogService::logCustom(auth()->id(), 'sla_create', SlaPolicy::class, $policy->id, $policy->name);

        return redirect()->route('admin.sla-policies.index')->with('success', 'SLA policy created.');
    }

    public function edit(SlaPolicy $slaPolicy): View
    {
        return view('admin.sla-policies.edit', [
            'policy' => $slaPolicy,
            'departments' => Department::where('is_active', true)->orderBy('name')->get(),
        ]);
    }

    public function update(Request $request, SlaPolicy $slaPolicy): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'department_id' => 'required|exists:departments,id',
            'priority' => 'required|string|in:low,medium,high,urgent',
            'first_response_time' => 'required|integer|min:1',
            'resolution_time' => 'required|integer|min:1',
            'is_active' => 'boolean',
        ]);

        $slaPolicy->update($validated);

        ActivityLogService::logCustom(auth()->id(), 'sla_update', SlaPolicy::class, $slaPolicy->id, $slaPolicy->name);

        return redirect()->route('admin.sla-policies.index')->with('success', 'SLA policy updated.');
    }

    public function destroy(SlaPolicy $slaPolicy): RedirectResponse
    {
        $slaPolicy->delete();

        ActivityLogService::logCustom(auth()->id(), 'sla_delete', SlaPolicy::class, $slaPolicy->id, $slaPolicy->name);

        return redirect()->route('admin.sla-policies.index')->with('success', 'SLA policy deleted.');
    }
}
