<div>
    <label class="block text-sm font-medium text-slate-700 mb-1">URL Pattern</label>
    <input type="text" name="url_pattern" value="{{ old('url_pattern', $item->url_pattern ?? '') }}" placeholder="/blog/* or /services/laravel-helpdesk" class="w-full rounded-lg border-gray-200 text-sm font-mono" required>
    <p class="text-xs text-slate-400 mt-1">Gunakan <code>*</code> sebagai wildcard. Contoh: <code>/blog/*</code>, <code>/services/laravel</code>.</p>
</div>
<div>
    <label class="block text-sm font-medium text-slate-700 mb-1">Meta Title</label>
    <input type="text" name="meta_title" value="{{ old('meta_title', $item->meta_title ?? '') }}" maxlength="255" class="w-full rounded-lg border-gray-200 text-sm">
</div>
<div>
    <label class="block text-sm font-medium text-slate-700 mb-1">Meta Description</label>
    <textarea name="meta_description" rows="2" maxlength="500" class="w-full rounded-lg border-gray-200 text-sm">{{ old('meta_description', $item->meta_description ?? '') }}</textarea>
</div>
<div class="grid grid-cols-1 md:grid-cols-2 gap-3">
    <div>
        <label class="block text-sm font-medium text-slate-700 mb-1">OG Image URL</label>
        <input type="url" name="og_image" value="{{ old('og_image', $item->og_image ?? '') }}" class="w-full rounded-lg border-gray-200 text-sm font-mono">
    </div>
    <div>
        <label class="block text-sm font-medium text-slate-700 mb-1">Canonical URL</label>
        <input type="url" name="canonical_url" value="{{ old('canonical_url', $item->canonical_url ?? '') }}" class="w-full rounded-lg border-gray-200 text-sm font-mono">
    </div>
</div>
<div>
    <label class="block text-sm font-medium text-slate-700 mb-1">JSON-LD Schema (optional)</label>
    <textarea name="schema_json" rows="6" placeholder='{"@context":"https://schema.org","@type":"Article"}' class="w-full rounded-lg border-gray-200 text-xs font-mono">{{ old('schema_json', $item->schema_json ?? '') }}</textarea>
</div>
<div>
    <label class="inline-flex items-center text-sm text-slate-700">
        <input type="hidden" name="noindex" value="0">
        <input type="checkbox" name="noindex" value="1" {{ old('noindex', $item->noindex ?? false) ? 'checked' : '' }} class="rounded border-gray-300 mr-2">
        Noindex (block from search engines)
    </label>
</div>
