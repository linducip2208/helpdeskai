@extends('layouts.admin')
@section('title', 'Configure AI Feature')
@section('content')

<div class="max-w-2xl mx-auto space-y-6">
    <div>
        <a href="{{ route('admin.ai-features.index') }}" class="text-sm text-indigo-600 hover:text-indigo-700">&larr; Back to AI Features</a>
        <h2 class="text-2xl font-bold text-slate-900 mt-1">Configure Feature: <span class="text-indigo-600">{{ $feature['name'] ?? $config->feature_key }}</span></h2>
        @if(!empty($feature['description']))
            <p class="text-sm text-slate-500 mt-1">{{ $feature['description'] }}</p>
        @endif
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
        <form action="{{ route('admin.ai-features.update', $config) }}" method="POST" x-data="{ providerId: '{{ $config->provider_id }}' }">
            @csrf @method('PUT')
            <div class="space-y-5">
                <div>
                    <label for="provider_id" class="block text-sm font-medium text-slate-700 mb-1">Provider</label>
                    <select name="provider_id" id="provider_id" x-model="providerId" class="w-full rounded-lg border-gray-200 text-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                        <option value="">— Not configured —</option>
                        @foreach($providers as $provider)
                            <option value="{{ $provider->id }}" {{ old('provider_id', $config->provider_id) == $provider->id ? 'selected' : '' }}>{{ $provider->name }} ({{ $provider->api_format }})</option>
                        @endforeach
                    </select>
                    @if($providers->isEmpty())
                        <p class="mt-1 text-xs text-amber-600">No active AI providers — <a href="{{ route('admin.ai-providers.create') }}" class="underline">add one first</a>.</p>
                    @endif
                </div>
                <div>
                    <label for="model_id" class="block text-sm font-medium text-slate-700 mb-1">Model</label>
                    <select name="model_id" id="model_id" class="w-full rounded-lg border-gray-200 text-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                        <option value="">— Provider default —</option>
                        @foreach($providers as $provider)
                            @foreach($provider->models as $model)
                                <option value="{{ $model->id }}" data-provider="{{ $provider->id }}" {{ old('model_id', $config->model_id) == $model->id ? 'selected' : '' }} x-show="providerId === '{{ $provider->id }}'">{{ $model->name }}</option>
                            @endforeach
                        @endforeach
                    </select>
                </div>
                <div class="flex items-center">
                    <label class="inline-flex items-center text-sm text-slate-700">
                        <input type="checkbox" name="is_enabled" value="1" {{ $config->is_enabled ? 'checked' : '' }} class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500 mr-2">
                        Enabled
                    </label>
                </div>
                <div class="flex justify-end space-x-3 pt-4 border-t border-gray-100">
                    <a href="{{ route('admin.ai-features.index') }}" class="px-6 py-2.5 border border-gray-200 text-slate-700 text-sm font-medium rounded-lg hover:bg-gray-50">Cancel</a>
                    <button type="submit" class="px-6 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium rounded-lg">Save Configuration</button>
                </div>
            </div>
        </form>
    </div>
</div>

@endsection
