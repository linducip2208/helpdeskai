<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CannedResponse;
use App\Models\Category;
use App\Services\ActivityLogService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class CannedResponseController extends Controller
{
    public function index(Request $request): View
    {
        $responses = CannedResponse::with('category')
            ->when($request->search, fn ($q) => $q->where(function ($q) use ($request) {
                $q->where('title', 'like', "%{$request->search}%")
                    ->orWhere('body', 'like', "%{$request->search}%");
            }))
            ->when($request->category_id, fn ($q) => $q->where('category_id', $request->category_id))
            ->latest()
            ->paginate(25);

        return view('admin.canned-responses.index', [
            'responses' => $responses,
            'categories' => Category::where('is_active', true)->orderBy('name')->get(),
            'filters' => $request->only(['search', 'category_id']),
        ]);
    }

    public function create(): View
    {
        return view('admin.canned-responses.create', [
            'categories' => Category::where('is_active', true)->orderBy('name')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'category_id' => 'nullable|exists:categories,id',
            'body' => 'required|string',
            'is_active' => 'boolean',
        ]);

        $validated['slug'] = Str::slug($validated['title']);

        $response = CannedResponse::create($validated);

        ActivityLogService::logCustom(auth()->id(), 'canned_create', CannedResponse::class, $response->id, $response->title);

        return redirect()->route('admin.canned-responses.index')->with('success', 'Canned response created.');
    }

    public function edit(CannedResponse $cannedResponse): View
    {
        return view('admin.canned-responses.edit', [
            'cannedResponse' => $cannedResponse,
            'categories' => Category::where('is_active', true)->orderBy('name')->get(),
        ]);
    }

    public function update(Request $request, CannedResponse $cannedResponse): RedirectResponse
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'category_id' => 'nullable|exists:categories,id',
            'body' => 'required|string',
            'is_active' => 'boolean',
        ]);

        $validated['slug'] = Str::slug($validated['title']);

        $cannedResponse->update($validated);

        ActivityLogService::logCustom(auth()->id(), 'canned_update', CannedResponse::class, $cannedResponse->id, $cannedResponse->title);

        return redirect()->route('admin.canned-responses.index')->with('success', 'Canned response updated.');
    }

    public function destroy(CannedResponse $cannedResponse): RedirectResponse
    {
        $cannedResponse->delete();

        ActivityLogService::logCustom(auth()->id(), 'canned_delete', CannedResponse::class, $cannedResponse->id, $cannedResponse->title);

        return redirect()->route('admin.canned-responses.index')->with('success', 'Canned response deleted.');
    }

    public function use(CannedResponse $cannedResponse): JsonResponse
    {
        abort_unless($cannedResponse->is_active, 404);

        return response()->json([
            'success' => true,
            'data' => ['title' => $cannedResponse->title, 'body' => $cannedResponse->body],
            'message' => 'Canned response retrieved.',
        ]);
    }
}
