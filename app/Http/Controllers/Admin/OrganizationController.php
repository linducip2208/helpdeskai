<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Organization;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class OrganizationController extends Controller
{
    public function index(Request $request): View
    {
        abort_unless($request->user()->can('organizations.manage'), 403);

        $organizations = Organization::withCount('users')->orderBy('name')->paginate(25);

        return view('admin.organizations.index', ['organizations' => $organizations]);
    }

    public function create(Request $request): View
    {
        abort_unless($request->user()->can('organizations.manage'), 403);

        return view('admin.organizations.create');
    }

    public function store(Request $request): RedirectResponse
    {
        abort_unless($request->user()->can('organizations.manage'), 403);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'domain' => ['nullable', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:50'],
            'notes' => ['nullable', 'string'],
        ]);

        $organization = Organization::create($validated);

        return redirect()->route('admin.organizations.show', $organization)->with('success', __('Organization created.'));
    }

    public function show(Request $request, Organization $organization): View
    {
        abort_unless($request->user()->can('organizations.manage'), 403);

        $users = User::where('organization_id', $organization->id)
            ->orderBy('name')
            ->paginate(25);

        return view('admin.organizations.show', [
            'organization' => $organization,
            'users' => $users,
        ]);
    }

    public function edit(Request $request, Organization $organization): View
    {
        abort_unless($request->user()->can('organizations.manage'), 403);

        return view('admin.organizations.edit', ['organization' => $organization]);
    }

    public function update(Request $request, Organization $organization): RedirectResponse
    {
        abort_unless($request->user()->can('organizations.manage'), 403);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'domain' => ['nullable', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:50'],
            'notes' => ['nullable', 'string'],
        ]);

        $organization->update($validated);

        return redirect()->route('admin.organizations.show', $organization)->with('success', __('Organization updated.'));
    }

    public function destroy(Request $request, Organization $organization): RedirectResponse
    {
        abort_unless($request->user()->can('organizations.manage'), 403);

        $organization->delete();

        return redirect()->route('admin.organizations.index')->with('success', __('Organization deleted.'));
    }
}
