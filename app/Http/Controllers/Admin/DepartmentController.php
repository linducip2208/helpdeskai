<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Department;
use App\Services\ActivityLogService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DepartmentController extends Controller
{
    public function index(): View
    {
        return view('admin.departments.index', [
            'departments' => Department::withCount(['tickets', 'categories'])->latest()->get(),
        ]);
    }

    public function create(): View
    {
        return view('admin.departments.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:departments',
            'description' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        $department = Department::create($validated);

        ActivityLogService::logCustom(auth()->id(), 'department_create', Department::class, $department->id, $department->name);

        return redirect()->route('admin.departments.index')->with('success', 'Department created.');
    }

    public function edit(Department $department): View
    {
        return view('admin.departments.edit', [
            'department' => $department,
        ]);
    }

    public function update(Request $request, Department $department): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:departments,name,'.$department->id,
            'description' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        $department->update($validated);

        ActivityLogService::logCustom(auth()->id(), 'department_update', Department::class, $department->id, $department->name);

        return redirect()->route('admin.departments.index')->with('success', 'Department updated.');
    }

    public function destroy(Department $department): RedirectResponse
    {
        if ($department->tickets()->exists()) {
            return back()->with('error', 'Cannot delete department with existing tickets.');
        }

        $department->delete();

        ActivityLogService::logCustom(auth()->id(), 'department_delete', Department::class, $department->id, $department->name);

        return redirect()->route('admin.departments.index')->with('success', 'Department deleted.');
    }
}
