<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\KnowledgeArticle;
use App\Models\KnowledgeCategory;
use App\Services\ActivityLogService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class KnowledgeArticleController extends Controller
{
    public function index(Request $request): View
    {
        $articles = KnowledgeArticle::with(['category', 'author'])
            ->when($request->search, fn ($q) => $q->where(function ($q) use ($request) {
                $q->where('title', 'like', "%{$request->search}%")
                    ->orWhere('content', 'like', "%{$request->search}%");
            }))
            ->when($request->category_id, fn ($q) => $q->where('category_id', $request->category_id))
            ->latest()
            ->paginate($request->per_page ?? 25)
            ->withQueryString();

        return view('admin.knowledge.index', [
            'articles' => $articles,
            'categories' => KnowledgeCategory::where('is_active', true)->orderBy('name')->get(),
            'filters' => $request->only(['search', 'category_id']),
        ]);
    }

    public function create(): View
    {
        return view('admin.knowledge.create', [
            'categories' => KnowledgeCategory::where('is_active', true)->orderBy('name')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'category_id' => 'required|exists:knowledge_categories,id',
            'content' => 'required|string',
            'excerpt' => 'nullable|string|max:500',
            'meta_title' => 'nullable|string|max:255',
            'meta_description' => 'nullable|string|max:500',
            'is_featured' => 'boolean',
        ]);

        $validated['slug'] = Str::slug($validated['title']);
        $validated['author_id'] = auth()->id();

        $article = KnowledgeArticle::create($validated);

        ActivityLogService::logCustom(auth()->id(), 'kb_article_create', KnowledgeArticle::class, $article->id, $article->title);

        return redirect()->route('admin.knowledge.index')->with('success', 'Article created.');
    }

    public function edit(KnowledgeArticle $article): View
    {
        return view('admin.knowledge.edit', [
            'article' => $article,
            'categories' => KnowledgeCategory::where('is_active', true)->orderBy('name')->get(),
        ]);
    }

    public function update(Request $request, KnowledgeArticle $article): RedirectResponse
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'category_id' => 'required|exists:knowledge_categories,id',
            'content' => 'required|string',
            'excerpt' => 'nullable|string|max:500',
            'meta_title' => 'nullable|string|max:255',
            'meta_description' => 'nullable|string|max:500',
            'is_featured' => 'boolean',
        ]);

        $validated['slug'] = Str::slug($validated['title']);

        $article->update($validated);

        ActivityLogService::logCustom(auth()->id(), 'kb_article_update', KnowledgeArticle::class, $article->id, $article->title);

        return redirect()->route('admin.knowledge.index')->with('success', 'Article updated.');
    }

    public function destroy(KnowledgeArticle $article): RedirectResponse
    {
        $article->delete();

        ActivityLogService::logCustom(auth()->id(), 'kb_article_delete', KnowledgeArticle::class, $article->id, $article->title);

        return redirect()->route('admin.knowledge.index')->with('success', 'Article deleted.');
    }

    public function toggleFeatured(KnowledgeArticle $article): JsonResponse
    {
        $article->update(['is_featured' => ! $article->is_featured]);

        return response()->json(['is_featured' => $article->is_featured]);
    }
}
