@extends('layouts.admin')
@section('title', 'Edit AI Provider')
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

<div x-data="aiProviderEditForm()" class="max-w-5xl mx-auto space-y-6">
    <div>
        <a href="{{ route('admin.ai-providers.index') }}" class="text-sm text-indigo-600 hover:text-indigo-700">&larr; Back to AI Providers</a>
        <h2 class="text-2xl font-bold text-slate-900 mt-1">Edit AI Provider — {{ $provider->name }}</h2>
        <p class="text-sm text-slate-500 mt-1">Switch preset untuk auto-update base URL + format, atau edit field manual. API key kosongkan kalau tidak mau ganti.</p>
    </div>

    @if($presets ?? false)
    <details class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
        <summary class="text-lg font-semibold text-slate-900 cursor-pointer flex items-center justify-between">
            <span>Switch to another preset ({{ count($presets) }} brands available)</span>
            <span class="text-xs text-slate-500 font-normal">click to expand</span>
        </summary>

        <p class="text-xs text-slate-500 my-4">Diurutkan dari <span class="font-semibold text-emerald-700">paling murah</span> ke paling mahal. Klik preset → base URL, API format, models akan ter-update di form bawah. Save untuk apply.</p>

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
                            <div class="text-sm font-semibold text-slate-900 leading-tight mb-1">{{ $preset['name_suggestion'] }}</div>
                            <span class="inline-block text-[10px] font-medium px-2 py-0.5 rounded border {{ $tierStyles[$preset['tier_color']] ?? $tierStyles['slate'] }}">
                                {{ $preset['tier_label'] }}
                            </span>
                        </button>
                    @endforeach
                </div>
            </div>
        @endforeach
    </details>
    @endif

    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
        <form action="{{ route('admin.ai-providers.update', $provider) }}" method="POST">
            @csrf @method('PUT')
            <div class="space-y-5">
                <div>
                    <label for="name" class="block text-sm font-medium text-slate-700 mb-1">Provider Name</label>
                    <input type="text" name="name" id="name" x-model="form.name" class="w-full rounded-lg border-gray-200 text-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50" required>
                </div>
                <div>
                    <label for="api_format" class="block text-sm font-medium text-slate-700 mb-1">API Format</label>
                    <select name="api_format" id="api_format" x-model="form.api_format" class="w-full rounded-lg border-gray-200 text-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50" required>
                        <option value="openai_compatible">OpenAI Compatible</option>
                        <option value="anthropic">Anthropic (Claude)</option>
                        <option value="gemini">Google Gemini</option>
                    </select>
                </div>
                <div>
                    <label for="base_url" class="block text-sm font-medium text-slate-700 mb-1">Base URL</label>
                    <input type="url" name="base_url" id="base_url" x-model="form.base_url" class="w-full rounded-lg border-gray-200 text-sm font-mono focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50" required>
                </div>
                <div>
                    <label for="api_key" class="block text-sm font-medium text-slate-700 mb-1">API Key</label>
                    <input type="password" name="api_key" id="api_key" placeholder="Leave blank to keep current" class="w-full rounded-lg border-gray-200 text-sm font-mono focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                    <p class="text-xs text-slate-400 mt-1">Leave empty to keep the existing key.</p>
                </div>
                <div>
                    <label for="extra_headers" class="block text-sm font-medium text-slate-700 mb-1">Extra Headers (JSON)</label>
                    <textarea name="extra_headers" id="extra_headers" rows="2" class="w-full rounded-lg border-gray-200 text-sm font-mono focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">{{ old('extra_headers', is_array($provider->extra_headers) ? json_encode($provider->extra_headers) : '') }}</textarea>
                </div>
                <div class="flex items-center">
                    <label class="inline-flex items-center text-sm text-slate-700">
                        <input type="checkbox" name="is_active" value="1" {{ $provider->is_active ? 'checked' : '' }} class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500 mr-2">
                        Active
                    </label>
                </div>
                <div class="flex justify-end space-x-3 pt-4 border-t border-gray-100">
                    <a href="{{ route('admin.ai-providers.index') }}" class="px-6 py-2.5 border border-gray-200 text-slate-700 text-sm font-medium rounded-lg hover:bg-gray-50">Cancel</a>
                    <button type="submit" class="px-6 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium rounded-lg">Update Provider</button>
                </div>
            </div>
        </form>
    </div>

    @if($provider->models->count() > 0)
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
        <h3 class="text-lg font-semibold text-slate-900 mb-3">Models ({{ $provider->models->count() }})</h3>
        <ul class="space-y-1 text-sm text-slate-700">
            @foreach($provider->models as $model)
                <li class="flex items-center justify-between py-1 border-b border-gray-50 last:border-0">
                    <span class="font-mono">{{ $model->model_name }}</span>
                    @if($model->is_active)
                        <span class="text-xs px-2 py-0.5 bg-emerald-50 text-emerald-700 rounded">active</span>
                    @else
                        <span class="text-xs px-2 py-0.5 bg-slate-100 text-slate-500 rounded">inactive</span>
                    @endif
                </li>
            @endforeach
        </ul>
    </div>
    @endif
</div>

<script>
function aiProviderEditForm() {
    return {
        selectedSlug: null,
        form: {
            name: @json($provider->name),
            api_format: @json($provider->api_format),
            base_url: @json($provider->base_url),
        },
        applyPreset(preset) {
            this.selectedSlug = preset.slug;
            this.form.name = preset.name_suggestion;
            this.form.api_format = preset.api_format;
            this.form.base_url = preset.base_url;
        },
    }
}
</script>

@endsection
