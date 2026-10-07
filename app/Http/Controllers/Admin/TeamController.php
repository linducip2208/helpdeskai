<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Team;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TeamController extends Controller
{
    public function index(Request $request): View
    {
        abort_unless($request->user()->can('teams.manage'), 403);

        $teams = Team::withCount('members')->orderBy('name')->paginate(25);

        return view('admin.teams.index', ['teams' => $teams]);
    }

    public function create(Request $request): View
    {
        abort_unless($request->user()->can('teams.manage'), 403);

        return view('admin.teams.create', [
            'users' => User::orderBy('name')->get(['id', 'name', 'email']),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        abort_unless($request->user()->can('teams.manage'), 403);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'is_active' => ['sometimes', 'boolean'],
            'members' => ['nullable', 'array'],
            'members.*' => ['integer', 'exists:users,id'],
        ]);

        $team = Team::create([
            'name' => $validated['name'],
            'description' => $validated['description'] ?? null,
            'is_active' => (bool) ($validated['is_active'] ?? true),
        ]);
        $team->members()->sync($validated['members'] ?? []);

        return redirect()->route('admin.teams.index')->with('success', __('Team created.'));
    }

    public function edit(Request $request, Team $team): View
    {
        abort_unless($request->user()->can('teams.manage'), 403);

        $team->load('members:id');

        return view('admin.teams.edit', [
            'team' => $team,
            'users' => User::orderBy('name')->get(['id', 'name', 'email']),
            'memberIds' => $team->members->pluck('id')->all(),
        ]);
    }

    public function update(Request $request, Team $team): RedirectResponse
    {
        abort_unless($request->user()->can('teams.manage'), 403);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'is_active' => ['sometimes', 'boolean'],
            'members' => ['nullable', 'array'],
            'members.*' => ['integer', 'exists:users,id'],
        ]);

        $team->update([
            'name' => $validated['name'],
            'description' => $validated['description'] ?? null,
            'is_active' => (bool) ($validated['is_active'] ?? false),
        ]);
        $team->members()->sync($validated['members'] ?? []);

        return redirect()->route('admin.teams.index')->with('success', __('Team updated.'));
    }

    public function destroy(Request $request, Team $team): RedirectResponse
    {
        abort_unless($request->user()->can('teams.manage'), 403);

        $team->members()->detach();
        $team->delete();

        return redirect()->route('admin.teams.index')->with('success', __('Team deleted.'));
    }
}
