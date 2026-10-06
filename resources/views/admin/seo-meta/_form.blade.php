<div class="mb-3">
    <label class="form-label">URL Pattern</label>
    <input type="text" name="url_pattern" value="{{ old('url_pattern', $item->url_pattern ?? '') }}" placeholder="/blog/* or /services/laravel-helpdesk" class="form-control font-monospace" required>
    <div class="form-hint">Gunakan <code>*</code> sebagai wildcard. Contoh: <code>/blog/*</code>, <code>/services/laravel</code>.</div>
</div>
<div class="mb-3">
    <label class="form-label">Meta Title</label>
    <input type="text" name="meta_title" value="{{ old('meta_title', $item->meta_title ?? '') }}" maxlength="255" class="form-control">
</div>
<div class="mb-3">
    <label class="form-label">Meta Description</label>
    <textarea name="meta_description" rows="2" maxlength="500" class="form-control">{{ old('meta_description', $item->meta_description ?? '') }}</textarea>
</div>
<div class="row g-2">
    <div class="col-md-6">
        <label class="form-label">OG Image URL</label>
        <input type="url" name="og_image" value="{{ old('og_image', $item->og_image ?? '') }}" class="form-control font-monospace">
    </div>
    <div class="col-md-6">
        <label class="form-label">Canonical URL</label>
        <input type="url" name="canonical_url" value="{{ old('canonical_url', $item->canonical_url ?? '') }}" class="form-control font-monospace">
    </div>
</div>
<div class="mb-3 mt-3">
    <label class="form-label">JSON-LD Schema (optional)</label>
    <textarea name="schema_json" rows="6" placeholder='{"@context":"https://schema.org","@type":"Article"}' class="form-control font-monospace">{{ old('schema_json', $item->schema_json ?? '') }}</textarea>
</div>
<div class="mb-3">
    <label class="form-check">
        <input type="hidden" name="noindex" value="0">
        <input type="checkbox" name="noindex" value="1" {{ old('noindex', $item->noindex ?? false) ? 'checked' : '' }} class="form-check-input">
        <span class="form-check-label">Noindex (block from search engines)</span>
    </label>
</div>
