@extends('layouts.admin')
@section('title', 'Configure AI Feature')
@section('content')

<div class="row justify-content-center">
    <div class="col-12 col-lg-8">
        <a href="{{ route('admin.ai-features.index') }}" class="btn btn-link px-0">&larr; Back to AI Features</a>
        <p class="text-muted">Configure Feature: <strong>{{ $feature['name'] ?? $config->feature_key }}</strong></p>
        @if(!empty($feature['description']))
            <p class="text-muted">{{ $feature['description'] }}</p>
        @endif
        <div class="card">
            <div class="card-body">
                <form action="{{ route('admin.ai-features.update', $config) }}" method="POST" x-data="{ providerId: '{{ $config->provider_id }}' }">
                    @csrf @method('PUT')
                    <div class="mb-3">
                        <label for="provider_id" class="form-label">Provider</label>
                        <select name="provider_id" id="provider_id" x-model="providerId" class="form-select">
                            <option value="">— Not configured —</option>
                            @foreach($providers as $provider)
                                <option value="{{ $provider->id }}" {{ old('provider_id', $config->provider_id) == $provider->id ? 'selected' : '' }}>{{ $provider->name }} ({{ $provider->api_format }})</option>
                            @endforeach
                        </select>
                        @if($providers->isEmpty())
                            <div class="form-hint">No active AI providers — <a href="{{ route('admin.ai-providers.create') }}">add one first</a>.</div>
                        @endif
                    </div>
                    <div class="mb-3">
                        <label for="model_id" class="form-label">Model</label>
                        <select name="model_id" id="model_id" class="form-select">
                            <option value="">— Provider default —</option>
                            @foreach($providers as $provider)
                                @foreach($provider->models as $model)
                                    <option value="{{ $model->id }}" data-provider="{{ $provider->id }}" {{ old('model_id', $config->model_id) == $model->id ? 'selected' : '' }} x-show="providerId === '{{ $provider->id }}'">{{ $model->name }}</option>
                                @endforeach
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-check form-switch">
                            <input type="checkbox" name="is_enabled" value="1" {{ $config->is_enabled ? 'checked' : '' }} class="form-check-input">
                            <span class="form-check-label">Enabled</span>
                        </label>
                    </div>
                    <div class="form-footer d-flex justify-content-end gap-2">
                        <a href="{{ route('admin.ai-features.index') }}" class="btn">Cancel</a>
                        <button type="submit" class="btn btn-primary">Save Configuration</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

@endsection
