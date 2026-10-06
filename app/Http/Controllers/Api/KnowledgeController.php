<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\KnowledgeArticle;
use App\Models\KnowledgeCategory;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class KnowledgeController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $articles = KnowledgeArticle::with('category')
            ->where('status', 'published')
            ->when($request->search, fn($q) => $q->where(function ($q) use ($request) {
                $q->where('title', 'like', "%{$request->search}%")
                  ->orWhere('content', 'like', "%{$request->search}%");
            }))
            ->latest()
            ->paginate($request->per_page ?? 15);

        return response()->json($articles);
    }

    public function show(KnowledgeArticle $article): JsonResponse
    {
        $article->load(['category', 'user']);
        $article->increment('view_count');

        return response()->json(['data' => $article]);
    }

    public function categories(): JsonResponse
    {
        $categories = KnowledgeCategory::where('is_active', true)
            ->withCount('articles')
            ->orderBy('sort_order')
            ->get();

        return response()->json(['data' => $categories]);
    }

    public function categoryArticles(KnowledgeCategory $category): JsonResponse
    {
        $articles = $category->articles()
            ->where('status', 'published')
            ->latest()
            ->paginate(15);

        return response()->json(['data' => $articles]);
    }
}
