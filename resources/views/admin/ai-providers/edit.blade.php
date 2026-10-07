@extends('layouts.admin')
@section('title', 'Edit AI Provider')
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

<div x-data="aiProviderEditForm()" class="row justify-content-center">
    <div class="col-12 col-lg-10">
        <a href="{{ route('admin.ai-providers.index') }}" class="btn btn-link px-0">&larr; Back to AI Providers</a>
        <p class="text-muted">Switch preset untuk auto-update base URL + format, atau edit field manual. API key kosongkan kalau tidak mau ganti.</p>

        @if($presets ?? false)
        <details class="card mb-3">
            <summary class="card-header d-flex align-items-center justify-content-between">
                <span class="card-title">Switch to another preset ({{ count($presets) }} brands available)</span>
                <span class="text-muted">click to expand</span>
            </summary>
            <div class="card-body">
                <p class="text-muted mb-3">Diurutkan dari <strong>paling murah</strong> ke paling mahal. Klik preset → base URL, API format, models akan ter-update di form bawah. Save untuk apply.</p>

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
                                        <div class="mb-1">{{ $preset['name_suggestion'] }}</div>
                                        <span class="badge {{ $tierStyles[$preset['tier_color']] ?? $tierStyles['slate'] }}">
                                            {{ $preset['tier_label'] }}
                                        </span>
                                    </button>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endforeach
            </div>
        </details>
        @endif

        <div class="card mb-3">
            <div class="card-body">
                <form action="{{ route('admin.ai-providers.update', $provider) }}" method="POST">
                    @csrf @method('PUT')
                    <div class="mb-3">
                        <label for="name" class="form-label">Provider Name</label>
                        <input type="text" name="name" id="name" x-model="form.name" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label for="api_format" class="form-label">API Format</label>
                        <select name="api_format" id="api_format" x-model="form.api_format" class="form-select" required>
                            <option value="openai_compatible">OpenAI Compatible</option>
                            <option value="anthropic">Anthropic (Claude)</option>
                            <option value="gemini">Google Gemini</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="base_url" class="form-label">Base URL</label>
                        <input type="url" name="base_url" id="base_url" x-model="form.base_url" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label for="api_key" class="form-label">API Key</label>
                        <input type="password" name="api_key" id="api_key" placeholder="Leave blank to keep current" class="form-control">
                        <div class="form-hint">Leave empty to keep the existing key.</div>
                    </div>
                    <div class="mb-3">
                        <label for="extra_headers" class="form-label">Extra Headers (JSON)</label>
                        <textarea name="extra_headers" id="extra_headers" rows="2" class="form-control">{{ old('extra_headers', is_array($provider->extra_headers) ? json_encode($provider->extra_headers) : '') }}</textarea>
                    </div>
                    <div class="row g-2">
                        <div class="col-md-3">
                            <label for="priority" class="form-label">Priority (failover order)</label>
                            <input type="number" name="priority" id="priority" min="0" max="1000" value="{{ old('priority', $provider->priority ?? 0) }}" class="form-control">
                        </div>
                        <div class="col-md-3">
                            <label for="timeout_seconds" class="form-label">Timeout (sec)</label>
                            <input type="number" name="timeout_seconds" id="timeout_seconds" min="5" max="300" value="{{ old('timeout_seconds', $provider->timeout_seconds ?? 60) }}" class="form-control">
                        </div>
                        <div class="col-md-3">
                            <label for="max_retries" class="form-label">Attempts</label>
                            <input type="number" name="max_retries" id="max_retries" min="1" max="5" value="{{ old('max_retries', $provider->max_retries ?? 2) }}" class="form-control">
                        </div>
                        <div class="col-md-3">
                            <label for="organization" class="form-label">Organization</label>
                            <input type="text" name="organization" id="organization" value="{{ old('organization', $provider->organization ?? '') }}" class="form-control">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-check form-switch">
                            <input type="checkbox" name="is_active" value="1" {{ $provider->is_active ? 'checked' : '' }} class="form-check-input">
                            <span class="form-check-label">Active</span>
                        </label>
                    </div>
                    <div class="form-footer d-flex justify-content-end gap-2">
                        <a href="{{ route('admin.ai-providers.index') }}" class="btn">Cancel</a>
                        <button type="submit" class="btn btn-primary">Update Provider</button>
                    </div>
                </form>
            </div>
        </div>

        @if($provider->models->count() > 0)
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Models ({{ $provider->models->count() }})</h3>
            </div>
            <div class="list-group list-group-flush">
                @foreach($provider->models as $model)
                    <div class="list-group-item d-flex align-items-center justify-content-between gap-2">
                        <span>
                            <code>{{ $model->model_id }}</code>
                            <span class="text-muted small">{{ $model->display_name }} · {{ $model->capability }}</span>
                            @if($model->cost_input_per_1m || $model->cost_output_per_1m)
                            <span class="text-muted small">· ${{ $model->cost_input_per_1m }}/1M in, ${{ $model->cost_output_per_1m }}/1M out</span>
                            @endif
                        </span>
                        <span class="d-flex align-items-center gap-2">
                            @if($model->is_active)
                                <span class="badge bg-green-lt">active</span>
                            @else
                                <span class="badge bg-secondary">inactive</span>
                            @endif
                            <form action="{{ route('admin.ai-providers.models.destroy', [$provider, $model]) }}" method="POST" onsubmit="return confirm('Remove this model?')">
                                @csrf @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-danger">Remove</button>
                            </form>
                        </span>
                    </div>
                @endforeach
            </div>
        </div>
        @endif

        <div class="card mt-3">
            <div class="card-header"><h3 class="card-title">Add Model</h3></div>
            <div class="card-body">
                <form action="{{ route('admin.ai-providers.models.store', $provider) }}" method="POST">
                    @csrf
                    <div class="row g-2">
                        <div class="col-md-6">
                            <label class="form-label" for="model_id">Model ID</label>
                            <input type="text" name="model_id" id="model_id" class="form-control" placeholder="gpt-4o-mini" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label" for="display_name">Display name</label>
                            <input type="text" name="display_name" id="display_name" class="form-control">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label" for="capability">Capability</label>
                            <select name="capability" id="capability" class="form-select">
                                @foreach(['chat', 'reasoning', 'embedding', 'image', 'audio', 'vision'] as $cap)
                                <option value="{{ $cap }}">{{ ucfirst($cap) }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label" for="cost_input_per_1m">Cost $/1M input</label>
                            <input type="number" step="0.0001" min="0" name="cost_input_per_1m" id="cost_input_per_1m" class="form-control">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label" for="cost_output_per_1m">Cost $/1M output</label>
                            <input type="number" step="0.0001" min="0" name="cost_output_per_1m" id="cost_output_per_1m" class="form-control">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label" for="context_window">Context window</label>
                            <input type="number" min="0" name="context_window" id="context_window" class="form-control">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label" for="max_output_tokens">Max output tokens</label>
                            <input type="number" min="0" name="max_output_tokens" id="max_output_tokens" class="form-control">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label" for="model_priority">Priority</label>
                            <input type="number" min="0" max="1000" name="priority" id="model_priority" value="0" class="form-control">
                        </div>
                    </div>
                    <button type="submit" class="btn btn-primary mt-3">Add Model</button>
                </form>
            </div>
        </div>
    </div>
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
