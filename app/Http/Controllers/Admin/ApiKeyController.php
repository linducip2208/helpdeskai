<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ApiKey;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ApiKeyController extends Controller
{
    public function index(): View
    {
        return view('admin.api-keys.index', [
            'apiKeys' => ApiKey::with('user')->latest()->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:120',
            'permissions' => 'nullable|string|in:read,read-write,full',
        ]);

        $plain = ApiKey::generateKey();

        ApiKey::create([
            'user_id' => $request->user()->id,
            'name' => $validated['name'],
            'permissions' => $validated['permissions'] ?? 'read',
            'key' => hash('sha256', $plain),
        ]);

        return redirect()->back()->with([
            'success' => 'API key created. Copy it now — it will not be shown again.',
            'plain_key' => $plain,
        ]);
    }

    public function toggle(ApiKey $apiKey): RedirectResponse
    {
        $apiKey->update(['is_active' => ! $apiKey->is_active]);

        return redirect()->back()->with('success', 'API key updated.');
    }

    public function destroy(ApiKey $apiKey): RedirectResponse
    {
        $apiKey->delete();

        return redirect()->back()->with('success', 'API key deleted.');
    }
}
