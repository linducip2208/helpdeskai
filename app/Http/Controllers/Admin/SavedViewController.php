<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SavedView;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class SavedViewController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $views = SavedView::where(function ($q) use ($request) {
            $q->where('user_id', $request->user()->id)
                ->orWhere('is_shared', true);
        })->orderBy('name')->get(['id', 'user_id', 'name', 'is_shared', 'filters']);

        return response()->json($views);
    }

    public function store(Request $request): JsonResponse|RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'filters' => ['nullable', 'array'],
            'is_shared' => ['sometimes', 'boolean'],
        ]);

        $wantsShared = (bool) ($validated['is_shared'] ?? false);
        $canShare = $request->user()->hasRole(['super-admin', 'admin', 'manager']);

        $view = SavedView::create([
            'user_id' => $request->user()->id,
            'name' => $validated['name'],
            'filters' => $validated['filters'] ?? [],
            'is_shared' => $wantsShared && $canShare,
        ]);

        if ($request->expectsJson()) {
            return response()->json($view, 201);
        }

        return back()->with('success', __('Saved view created.'));
    }

    public function destroy(Request $request, SavedView $savedView): JsonResponse|RedirectResponse
    {
        $isOwner = (int) $savedView->user_id === (int) $request->user()->id;

        abort_unless($isOwner || $request->user()->can('manage_users'), 403);

        $savedView->delete();

        if ($request->expectsJson()) {
            return response()->json(['deleted' => true]);
        }

        return back()->with('success', __('Saved view deleted.'));
    }
}
