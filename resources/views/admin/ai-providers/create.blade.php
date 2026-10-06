@extends('layouts.admin')
@section('title', 'Add AI Provider')
@section('content')

@php
    $tierStyles = [
        'emerald' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
        'lime'    => 'bg-lime-50 text-lime-700 border-lime-200',
        'amber'   => 'bg-amber-50 text-amber-700 border-amber-200',
        'orange'  => 'bg-orange-50 text-orange-700 border-orange-200',
        'rose'    => 'bg-rose-50 text-rose-700 border-rose-200',
        'violet'  => 'bg-violet-50 text-violet-700 border-violet-200',
        'slate'   => 'bg-slate-50 text-slate-700 border-slate-200',
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

<div x-data="aiProviderForm()" class="max-w-5xl mx-auto space-y-6">
    <div>
        <a href="{{ route('admin.ai-providers.index') }}" class="text-sm text-indigo-600 hover:text-indigo-700">&larr; Back to AI Providers</a>
        <h2 class="text-2xl font-bold text-slate-900 mt-1">Add AI Provider</h2>
        <p class="text-sm text-slate-500 mt-1">Pilih preset di bawah untuk autofill, atau isi manual. Semua field bisa di-edit setelah pilih preset. <strong>Anda tetap pakai API key sendiri</strong> (BYOK).</p>
    </div>

    @if($presets ?? false)
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-lg font-semibold text-slate-900">Quick Start — Preset Templates ({{ count($presets) }})</h3>
            <button type="button" @click="clearPreset()" class="text-xs text-slate-500 hover:text-slate-700">Clear selection</button>
        </div>
        <p class="text-xs text-slate-500 mb-4">Diurutkan dari <span class="font-semibold text-emerald-700">paling murah</span> ke paling mahal. Klik untuk autofill — Anda masih bisa edit semua field sebelum simpan.</p>

        @foreach($tierGroups as $tier => $items)
            <div class="mb-6 last:mb-0">
                <div class="flex items-center gap-2 mb-3">
                    <span class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Tier {{ $tier }} — {{ $tierTitles[$tier] ?? 'Other' }}</span>
                    <div class="flex-1 border-t border-gray-100"></div>
                </div>
                <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-3">
                    @foreach($items as $preset)
                        <button type="button"
                                @click='applyPreset(@json($preset))'
                                :class="selectedSlug === '{{ $preset['slug'] }}' ? 'ring-2 ring-indigo-500 border-indigo-300' : 'border-gray-200 hover:border-indigo-300'"
                                class="text-left p-3 border rounded-lg transition bg-white hover:shadow-sm">
                            <div class="flex items-start justify-between mb-1">
                                <span class="text-sm font-semibold text-slate-900 leading-tight">{{ $preset['name_suggestion'] }}</span>
                            </div>
                            <span class="inline-block text-[10px] font-medium px-2 py-0.5 rounded border {{ $tierStyles[$preset['tier_color']] ?? $tierStyles['slate'] }}">
                                {{ $preset['tier_label'] }}
                            </span>
                            @if($preset['signup_url'] ?? null)
                                <a href="{{ $preset['signup_url'] }}" target="_blank" @click.stop class="block text-[10px] text-indigo-600 hover:text-indigo-700 mt-2 truncate">Get API key &rarr;</a>
                            @endif
                        </button>
                    @endforeach
                </div>
            </div>
        @endforeach
    </div>
    @endif

    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
        <form action="{{ route('admin.ai-providers.store') }}" method="POST">
            @csrf
            <div class="space-y-5">
                <div>
                    <label for="name" class="block text-sm font-medium text-slate-700 mb-1">Provider Name</label>
                    <input type="text" name="name" id="name" x-model="form.name" value="{{ old('name') }}" placeholder="e.g. My DeepSeek, My OpenAI" class="w-full rounded-lg border-gray-200 text-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50" required>
                </div>
                <div>
                    <label for="api_format" class="block text-sm font-medium text-slate-700 mb-1">API Format</label>
                    <select name="api_format" id="api_format" x-model="form.api_format" class="w-full rounded-lg border-gray-200 text-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50" required>
                        <option value="">Select Format</option>
                        <option value="openai_compatible">OpenAI Compatible (OpenAI, DeepSeek, Groq, vLLM, dst.)</option>
                        <option value="anthropic">Anthropic (Claude)</option>
                        <option value="gemini">Google Gemini</option>
                    </select>
                </div>
                <div>
                    <label for="base_url" class="block text-sm font-medium text-slate-700 mb-1">Base URL</label>
                    <input type="url" name="base_url" id="base_url" x-model="form.base_url" placeholder="https://api.openai.com/v1" class="w-full rounded-lg border-gray-200 text-sm font-mono focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50" required>
                </div>
                <div>
                    <label for="api_key" class="block text-sm font-medium text-slate-700 mb-1">API Key</label>
                    <input type="password" name="api_key" id="api_key" :placeholder="form.key_placeholder || 'sk-...'" class="w-full rounded-lg border-gray-200 text-sm font-mono focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50" required>
                    <p class="text-xs text-slate-400 mt-1">Encrypted at rest. Never exposed in API responses. Anda input sendiri — kita tidak menyimpan default key apapun.</p>
                </div>
                <div>
                    <label for="default_models" class="block text-sm font-medium text-slate-700 mb-1">Default Models <span class="text-slate-400 font-normal">(comma-separated)</span></label>
                    <textarea name="default_models" id="default_models" x-model="form.default_models" rows="2" placeholder="gpt-4o-mini, gpt-4o" class="w-full rounded-lg border-gray-200 text-sm font-mono focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50"></textarea>
                    <p class="text-xs text-slate-400 mt-1">Akan otomatis dibuat di tabel models. Bisa edit / tambah nanti.</p>
                </div>
                <div>
                    <label for="extra_headers" class="block text-sm font-medium text-slate-700 mb-1">Extra Headers (JSON)</label>
                    <textarea name="extra_headers" id="extra_headers" rows="2" placeholder='{"X-Custom-Header": "value"}' class="w-full rounded-lg border-gray-200 text-sm font-mono focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">{{ old('extra_headers') }}</textarea>
                </div>
                <div class="flex justify-end space-x-3 pt-4 border-t border-gray-100">
                    <a href="{{ route('admin.ai-providers.index') }}" class="px-6 py-2.5 border border-gray-200 text-slate-700 text-sm font-medium rounded-lg hover:bg-gray-50">Cancel</a>
                    <button type="submit" class="px-6 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium rounded-lg">Add Provider</button>
                </div>
            </div>
        </form>
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
