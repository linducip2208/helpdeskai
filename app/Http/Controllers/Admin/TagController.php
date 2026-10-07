<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Tag;
use App\Services\ActivityLogService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TagController extends Controller
{
    public function index(): View
    {
        return view('admin.tags.index', [
            'tags' => Tag::withCount('tickets')->orderBy('name')->paginate(25),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'color' => 'nullable|string|max:16',
        ]);

        $tag = Tag::firstOrCreate(
            ['name' => mb_strtolower(trim($validated['name']))],
            ['color' => $validated['color'] ?? 'blue']
        );

        ActivityLogService::logCustom(auth()->id(), 'tag_create', Tag::class, $tag->id, $tag->name);

        return back()->with('success', 'Tag saved.');
    }

    public function destroy(Tag $tag): RedirectResponse
    {
        $tag->delete();

        ActivityLogService::logCustom(auth()->id(), 'tag_delete', Tag::class, $tag->id, $tag->name);

        return back()->with('success', 'Tag deleted.');
    }
}
