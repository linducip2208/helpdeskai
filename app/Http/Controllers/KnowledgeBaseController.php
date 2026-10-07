<?php

namespace App\Http\Controllers;

use App\Models\KbSearch;
use App\Models\KnowledgeArticle;
use App\Models\KnowledgeCategory;
use Illuminate\Http\Request;
use Illuminate\View\View;

class KnowledgeBaseController extends Controller
{
    public function index(): View
    {
        $categories = KnowledgeCategory::where('is_active', true)
            ->withCount('articles')
            ->orderBy('sort_order')
            ->get();

        $featuredArticles = KnowledgeArticle::with('category')
            ->where('status', 'published')
            ->where('is_featured', true)
            ->latest()
            ->take(6)
            ->get();

        return view('knowledge-base.index', [
            'categories' => $categories,
            'featuredArticles' => $featuredArticles,
        ]);
    }

    public function category(string $slug): View
    {
        $category = KnowledgeCategory::where('slug', $slug)->where('is_active', true)->firstOrFail();

        $articles = $category->articles()
            ->where('status', 'published')
            ->latest()
            ->paginate(15);

        return view('knowledge-base.category', [
            'category' => $category->load('parent'),
            'articles' => $articles,
        ]);
    }

    public function show(string $slug): View
    {
        $article = KnowledgeArticle::where('slug', $slug)
            ->where('status', 'published')
            ->firstOrFail();

        $article->load(['category', 'user']);
        $article->increment('view_count');

        $relatedArticles = KnowledgeArticle::where('category_id', $article->category_id)
            ->where('id', '!=', $article->id)
            ->where('status', 'published')
            ->latest()
            ->take(5)
            ->get();

        return view('knowledge-base.show', [
            'article' => $article,
            'relatedArticles' => $relatedArticles,
        ]);
    }

    public function search(Request $request): View
    {
        $query = $request->get('q');
        $results = collect();

        if ($query) {
            $results = KnowledgeArticle::with('category')
                ->where('status', 'published')
                ->where(function ($q) use ($query) {
                    $q->where('title', 'like', "%{$query}%")
                        ->orWhere('content', 'like', "%{$query}%");
                })
                ->latest()
                ->paginate(15)
                ->withQueryString();

            KbSearch::create([
                'query' => mb_substr($query, 0, 191),
                'results_count' => $results->total(),
                'language' => app()->getLocale(),
                'user_id' => auth()->id(),
            ]);
        }

        return view('knowledge-base.search', [
            'query' => $query,
            'results' => $results,
        ]);
    }
}
