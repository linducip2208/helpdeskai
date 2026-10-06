<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\KnowledgeFaq;
use App\Models\KnowledgeCategory;
use App\Services\ActivityLogService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class KnowledgeFaqController extends Controller
{
    public function index(Request $request): View
    {
        $faqs = KnowledgeFaq::with('category')
            ->when($request->category_id, fn($q) => $q->where('category_id', $request->category_id))
            ->orderBy('sort_order')
            ->get();

        return view('admin.knowledge-faqs.index', [
            'faqs' => $faqs,
            'categories' => KnowledgeCategory::where('is_active', true)->orderBy('name')->get(),
        ]);
    }

    public function create(): View
    {
        return view('admin.knowledge-faqs.create', [
            'categories' => KnowledgeCategory::where('is_active', true)->orderBy('name')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'category_id' => 'required|exists:knowledge_categories,id',
            'question' => 'required|string|max:500',
            'answer' => 'required|string',
            'sort_order' => 'nullable|integer',
            'is_active' => 'boolean',
        ]);

        $faq = KnowledgeFaq::create($validated);

        ActivityLogService::log(auth()->id(), 'faq_create', KnowledgeFaq::class, $faq->id, $faq->question);

        return redirect()->route('admin.knowledge-faqs.index')->with('success', 'FAQ created.');
    }

    public function edit(KnowledgeFaq $knowledgeFaq): View
    {
        return view('admin.knowledge-faqs.edit', [
            'faq' => $knowledgeFaq,
            'categories' => KnowledgeCategory::where('is_active', true)->orderBy('name')->get(),
        ]);
    }

    public function update(Request $request, KnowledgeFaq $knowledgeFaq): RedirectResponse
    {
        $validated = $request->validate([
            'category_id' => 'required|exists:knowledge_categories,id',
            'question' => 'required|string|max:500',
            'answer' => 'required|string',
            'sort_order' => 'nullable|integer',
            'is_active' => 'boolean',
        ]);

        $knowledgeFaq->update($validated);

        ActivityLogService::log(auth()->id(), 'faq_update', KnowledgeFaq::class, $knowledgeFaq->id, $knowledgeFaq->question);

        return redirect()->route('admin.knowledge-faqs.index')->with('success', 'FAQ updated.');
    }

    public function destroy(KnowledgeFaq $knowledgeFaq): RedirectResponse
    {
        $knowledgeFaq->delete();

        ActivityLogService::log(auth()->id(), 'faq_delete', KnowledgeFaq::class, $knowledgeFaq->id, $knowledgeFaq->question);

        return redirect()->route('admin.knowledge-faqs.index')->with('success', 'FAQ deleted.');
    }
}
