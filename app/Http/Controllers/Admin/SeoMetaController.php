<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SeoMeta;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SeoMetaController extends Controller
{
    public function index(): View
    {
        return view('admin.seo-meta.index', [
            'items' => SeoMeta::orderBy('url_pattern')->paginate(30),
        ]);
    }

    public function create(): View
    {
        return view('admin.seo-meta.create');
    }

    public function store(Request $request): RedirectResponse
    {
        SeoMeta::create($this->validated($request));

        return redirect()->route('admin.seo-meta.index')->with('success', 'SEO meta saved.');
    }

    public function edit(SeoMeta $seoMetum): View
    {
        return view('admin.seo-meta.edit', ['item' => $seoMetum]);
    }

    public function update(Request $request, SeoMeta $seoMetum): RedirectResponse
    {
        $seoMetum->update($this->validated($request));

        return redirect()->route('admin.seo-meta.index')->with('success', 'SEO meta updated.');
    }

    public function destroy(SeoMeta $seoMetum): RedirectResponse
    {
        $seoMetum->delete();

        return back()->with('success', 'SEO meta deleted.');
    }

    private function validated(Request $request): array
    {
        $data = $request->validate([
            'url_pattern' => 'required|string|max:255',
            'meta_title' => 'nullable|string|max:255',
            'meta_description' => 'nullable|string|max:500',
            'og_image' => 'nullable|url|max:500',
            'canonical_url' => 'nullable|url|max:500',
            'schema_json' => 'nullable|json',
            'noindex' => 'nullable|boolean',
        ]);

        $data['noindex'] = (bool) ($data['noindex'] ?? false);

        return $data;
    }
}
