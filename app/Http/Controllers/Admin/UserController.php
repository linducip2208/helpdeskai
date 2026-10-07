<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\ActivityLogService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;
use Spatie\Permission\Models\Role;
use Symfony\Component\HttpFoundation\StreamedResponse;

class UserController extends Controller
{
    public function index(Request $request): View
    {
        $users = User::with('roles')
            ->when($request->search, fn ($q) => $q->where(function ($q) use ($request) {
                $q->where('name', 'like', "%{$request->search}%")
                    ->orWhere('email', 'like', "%{$request->search}%");
            }))
            ->when($request->role, fn ($q) => $q->whereHas('roles', fn ($q) => $q->where('name', $request->role)))
            ->withCount('tickets')
            ->latest()
            ->paginate($request->per_page ?? 25)
            ->withQueryString();

        return view('admin.users.index', [
            'users' => $users,
            'roles' => Role::all(),
            'filters' => $request->only(['search', 'role']),
        ]);
    }

    public function show(User $user): View
    {
        $user->load(['roles', 'tickets' => fn ($q) => $q->latest()->take(10)]);

        return view('admin.users.show', [
            'user' => $user,
        ]);
    }

    public function create(): View
    {
        return view('admin.users.create', [
            'roles' => Role::all(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:8',
            'is_active' => 'boolean',
            'roles' => 'array',
            'roles.*' => 'exists:roles,id',
        ]);

        $roles = $validated['roles'] ?? [];
        unset($validated['roles']);
        $validated['password'] = Hash::make($validated['password']);
        $validated['is_active'] = $validated['is_active'] ?? true;

        $user = User::create($validated);
        $user->roles()->sync($roles);
        $this->syncRoleColumn($user);

        ActivityLogService::logCustom(auth()->id(), 'user_create', User::class, $user->id, $user->name);

        return redirect()->route('admin.users.index')->with('success', 'User created.');
    }

    public function destroy(User $user): RedirectResponse
    {
        $name = $user->name;
        $id = $user->id;
        $user->delete();
        ActivityLogService::logCustom(auth()->id(), 'user_delete', User::class, $id, $name);

        return redirect()->route('admin.users.index')->with('success', 'User deleted.');
    }

    public function edit(User $user): View
    {
        return view('admin.users.edit', [
            'user' => $user->load('roles'),
            'roles' => Role::all(),
        ]);
    }

    public function update(Request $request, User $user): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,'.$user->id,
            'is_active' => 'boolean',
            'roles' => 'array',
            'roles.*' => 'exists:roles,id',
            'password' => 'nullable|string|min:8',
        ]);

        if (! empty($validated['password'])) {
            $validated['password'] = Hash::make($validated['password']);
        } else {
            unset($validated['password']);
        }

        $roles = $validated['roles'] ?? [];
        unset($validated['roles']);

        $user->update($validated);
        $user->roles()->sync($roles);
        $this->syncRoleColumn($user);

        ActivityLogService::logCustom(auth()->id(), 'user_update', User::class, $user->id, $user->name);

        return redirect()->route('admin.users.index')->with('success', 'User updated.');
    }

    /**
     * Keep the legacy `role` string column in sync with the primary Spatie role.
     */
    protected function syncRoleColumn(User $user): void
    {
        $primary = $user->roles()->orderBy('name')->first();

        $user->forceFill([
            'role' => $primary?->name ?? 'customer',
        ])->saveQuietly();
    }

    protected function canImpersonate(User $actor, User $target): bool
    {
        if ($actor->hasRole('super-admin')) {
            return true;
        }

        $ranks = ['customer' => 1, 'agent' => 2, 'manager' => 3, 'admin' => 4, 'super-admin' => 5];

        $rankOf = fn (User $u) => $u->roles->pluck('name')
            ->map(fn ($name) => $ranks[$name] ?? 0)
            ->max() ?? 0;

        return $rankOf($actor) > $rankOf($target);
    }

    public function impersonate(User $user): RedirectResponse
    {
        abort_if(session()->has('impersonator_id'), 403, 'Already impersonating a user.');
        abort_if($user->id === auth()->id(), 422, 'You cannot impersonate yourself.');
        abort_unless($this->canImpersonate(auth()->user(), $user), 403, 'You cannot impersonate a user with equal or higher privileges.');

        session(['impersonator_id' => auth()->id()]);
        Auth::login($user);
        session()->regenerate();

        ActivityLogService::logCustom((int) session('impersonator_id'), 'impersonate', User::class, $user->id, $user->name);

        return redirect()->route('dashboard')->with('success', "Impersonating {$user->name}");
    }

    public function stopImpersonate(): RedirectResponse
    {
        $impersonatorId = session('impersonator_id');
        abort_unless($impersonatorId, 403, 'No active impersonation.');

        $impersonator = User::find($impersonatorId);
        abort_unless($impersonator, 403, 'Original session no longer valid.');

        session()->forget('impersonator_id');
        Auth::login($impersonator);
        session()->regenerate();

        ActivityLogService::logCustom($impersonator->id, 'impersonate_stop', User::class, $impersonator->id, $impersonator->name);

        return redirect()->route('admin.users.index')->with('success', 'Impersonation ended.');
    }

    public function exportCsv(): StreamedResponse
    {
        return response()->streamDownload(function () {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['ID', 'Name', 'Email', 'Roles', 'Active', 'Created At']);

            User::with('roles')->orderBy('id')->chunk(500, function ($users) use ($handle) {
                foreach ($users as $user) {
                    fputcsv($handle, [
                        $user->id,
                        $user->name,
                        $user->email,
                        $user->roles->pluck('name')->implode(', '),
                        $user->is_active ? 'Yes' : 'No',
                        $user->created_at->toDateTimeString(),
                    ]);
                }
            });

            fclose($handle);
        }, 'users-export.csv');
    }
}
