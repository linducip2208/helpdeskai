@extends('layouts.admin')
@section('title', 'Add AI Provider')
@section('content')

@php
    $tierStyles = [
        'emerald' => 'bg-green-lt',
        'lime'    => 'bg-green-lt',
        'amber'   => 'bg-yellow-lt',
        'orange'  => 'bg-orange-lt',
        'rose'    => 'bg-red-lt',
        'violet'  => 'bg-purple-lt',
        'slate'   => 'bg-secondary',
    ];
    $tierGroups = collect($presets ?? [])->groupBy('cost_tier');
    $tierTitles = [
        1 => 'Free / Self-hosted',
        2 => 'Ultra cheap (free tier available)',
        3 => 'Cheap',
        4 => 'Mid',
        5 => 'Expensive (premium)',
        6 => 'Image generation',
    ];
@endphp

<div x-data="aiProviderForm()" class="row justify-content-center">
    <div class="col-12 col-lg-10">
        <a href="{{ route('admin.ai-providers.index') }}" class="btn btn-link px-0">&larr; Back to AI Providers</a>
        <p class="text-muted">Pilih preset di bawah untuk autofill, atau isi manual. Semua field bisa di-edit setelah pilih preset. <strong>Anda tetap pakai API key sendiri</strong> (BYOK).</p>

        @if($presets ?? false)
        <div class="card mb-3">
            <div class="card-header d-flex align-items-center justify-content-between">
                <h3 class="card-title">Quick Start — Preset Templates ({{ count($presets) }})</h3>
                <button type="button" @click="clearPreset()" class="btn btn-sm">Clear selection</button>
            </div>
            <div class="card-body">
                <p class="text-muted mb-3">Diurutkan dari <strong>paling murah</strong> ke paling mahal. Klik untuk autofill — Anda masih bisa edit semua field sebelum simpan.</p>

                @foreach($tierGroups as $tier => $items)
                    <div class="mb-4">
                        <div class="d-flex align-items-center gap-2 mb-2">
                            <span class="text-muted">Tier {{ $tier }} — {{ $tierTitles[$tier] ?? 'Other' }}</span>
                            <div class="flex-fill border-top"></div>
                        </div>
                        <div class="row row-cards">
                            @foreach($items as $preset)
                                <div class="col-6 col-md-4 col-lg-3">
                                    <button type="button"
                                            @click='applyPreset(@json($preset))'
                                            :class="selectedSlug === '{{ $preset['slug'] }}' ? 'border-primary' : ''"
                                            class="card text-start p-3 w-100">
                                        <div class="mb-1">
                                            <span>{{ $preset['name_suggestion'] }}</span>
                                        </div>
                                        <span class="badge {{ $tierStyles[$preset['tier_color']] ?? $tierStyles['slate'] }}">
                                            {{ $preset['tier_label'] }}
                                        </span>
                                        @if($preset['signup_url'] ?? null)
                                            <a href="{{ $preset['signup_url'] }}" target="_blank" @click.stop class="d-block text-truncate mt-2">Get API key &rarr;</a>
                                        @endif
                                    </button>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
        @endif

        <div class="card">
            <div class="card-body">
                <form action="{{ route('admin.ai-providers.store') }}" method="POST">
                    @csrf
                    <div class="mb-3">
                        <label for="name" class="form-label">Provider Name</label>
                        <input type="text" name="name" id="name" x-model="form.name" value="{{ old('name') }}" placeholder="e.g. My DeepSeek, My OpenAI" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label for="api_format" class="form-label">API Format</label>
                        <select name="api_format" id="api_format" x-model="form.api_format" class="form-select" required>
                            <option value="">Select Format</option>
                            <option value="openai_compatible">OpenAI Compatible (OpenAI, DeepSeek, Groq, vLLM, dst.)</option>
                            <option value="anthropic">Anthropic (Claude)</option>
                            <option value="gemini">Google Gemini</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="base_url" class="form-label">Base URL</label>
                        <input type="url" name="base_url" id="base_url" x-model="form.base_url" placeholder="https://api.openai.com/v1" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label for="api_key" class="form-label">API Key</label>
                        <input type="password" name="api_key" id="api_key" :placeholder="form.key_placeholder || 'sk-...'" class="form-control" required>
                        <div class="form-hint">Encrypted at rest. Never exposed in API responses. Anda input sendiri — kita tidak menyimpan default key apapun.</div>
                    </div>
                    <div class="mb-3">
                        <label for="default_models" class="form-label">Default Models <span class="text-muted">(comma-separated)</span></label>
                        <textarea name="default_models" id="default_models" x-model="form.default_models" rows="2" placeholder="gpt-4o-mini, gpt-4o" class="form-control"></textarea>
                        <div class="form-hint">Akan otomatis dibuat di tabel models. Bisa edit / tambah nanti.</div>
                    </div>
                    <div class="mb-3">
                        <label for="extra_headers" class="form-label">Extra Headers (JSON)</label>
                        <textarea name="extra_headers" id="extra_headers" rows="2" placeholder='{"X-Custom-Header": "value"}' class="form-control">{{ old('extra_headers') }}</textarea>
                    </div>
                    <div class="form-footer d-flex justify-content-end gap-2">
                        <a href="{{ route('admin.ai-providers.index') }}" class="btn">Cancel</a>
                        <button type="submit" class="btn btn-primary">Add Provider</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
function aiProviderForm() {
    return {
        selectedSlug: null,
        form: {
            name: @json(old('name', '')),
            api_format: @json(old('api_format', '')),
            base_url: @json(old('base_url', '')),
            default_models: '',
            key_placeholder: 'sk-...',
        },
        applyPreset(preset) {
            this.selectedSlug = preset.slug;
            this.form.name = preset.name_suggestion;
            this.form.api_format = preset.api_format;
            this.form.base_url = preset.base_url;
            this.form.default_models = (preset.models_suggestion || []).join(', ');
            this.form.key_placeholder = preset.key_placeholder || 'sk-...';
        },
        clearPreset() {
            this.selectedSlug = null;
            this.form = { name: '', api_format: '', base_url: '', default_models: '', key_placeholder: 'sk-...' };
        },
    }
}
</script>

@endsection
