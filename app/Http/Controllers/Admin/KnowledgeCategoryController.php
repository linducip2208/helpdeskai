<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\KnowledgeCategory;
use App\Services\ActivityLogService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class KnowledgeCategoryController extends Controller
{
    public function index(): View
    {
        return view('admin.knowledge-categories.index', [
            'categories' => KnowledgeCategory::with('parent')
                ->withCount('articles')
                ->orderBy('sort_order')
                ->get(),
        ]);
    }

    public function create(): View
    {
        return view('admin.knowledge-categories.create', [
            'categories' => KnowledgeCategory::where('is_active', true)->orderBy('name')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:knowledge_categories',
            'description' => 'nullable|string',
            'parent_id' => 'nullable|exists:knowledge_categories,id',
            'is_active' => 'boolean',
            'sort_order' => 'nullable|integer',
        ]);

        $validated['slug'] = Str::slug($validated['name']);

        $category = KnowledgeCategory::create($validated);

        ActivityLogService::logCustom(auth()->id(), 'kb_category_create', KnowledgeCategory::class, $category->id, $category->name);

        return redirect()->route('admin.knowledge-categories.index')->with('success', 'Category created.');
    }

    public function edit(KnowledgeCategory $knowledgeCategory): View
    {
        return view('admin.knowledge-categories.edit', [
            'category' => $knowledgeCategory,
            'categories' => KnowledgeCategory::where('is_active', true)->where('id', '!=', $knowledgeCategory->id)->orderBy('name')->get(),
        ]);
    }

    public function update(Request $request, KnowledgeCategory $knowledgeCategory): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:knowledge_categories,name,'.$knowledgeCategory->id,
            'description' => 'nullable|string',
            'parent_id' => 'nullable|exists:knowledge_categories,id',
            'is_active' => 'boolean',
            'sort_order' => 'nullable|integer',
        ]);

        $validated['slug'] = Str::slug($validated['name']);

        $knowledgeCategory->update($validated);

        ActivityLogService::logCustom(auth()->id(), 'kb_category_update', KnowledgeCategory::class, $knowledgeCategory->id, $knowledgeCategory->name);

        return redirect()->route('admin.knowledge-categories.index')->with('success', 'Category updated.');
    }

    public function destroy(KnowledgeCategory $knowledgeCategory): RedirectResponse
    {
        $knowledgeCategory->delete();

        ActivityLogService::logCustom(auth()->id(), 'kb_category_delete', KnowledgeCategory::class, $knowledgeCategory->id, $knowledgeCategory->name);

        return redirect()->route('admin.knowledge-categories.index')->with('success', 'Category deleted.');
    }
}
