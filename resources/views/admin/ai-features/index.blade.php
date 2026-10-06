@extends('layouts.admin')
@section('title', 'AI Features')
@section('content')

<div class="space-y-6">
    <div class="flex items-center justify-between">
        <h2 class="text-2xl font-bold text-slate-900">AI Features</h2>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50 border-b border-gray-100">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Feature</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Provider</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Model</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Prompt</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Status</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-slate-500 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($features ?? [] as $key => $feature)
                        @php
                            $config = ($configs ?? collect())->get($key);
                            $label = is_array($feature) ? ($feature['name'] ?? $key) : (is_string($feature) ? $feature : $key);
                        @endphp
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 text-sm font-medium text-slate-900">{{ $label }}</td>
                            <td class="px-6 py-4 text-sm text-slate-500">{{ optional(optional($config)->provider)->name ?? '—' }}</td>
                            <td class="px-6 py-4 text-sm text-slate-500 font-mono">{{ optional(optional($config)->model)->name ?? 'default' }}</td>
                            <td class="px-6 py-4 text-sm text-slate-500">{{ \Illuminate\Support\Str::limit(optional($config)->system_prompt ?? '', 50) }}</td>
                            <td class="px-6 py-4">
                                @php $enabled = $config?->is_enabled ?? false; @endphp
                                <span class="inline-flex px-2.5 py-0.5 rounded-full text-xs font-medium {{ $enabled ? 'bg-emerald-100 text-emerald-700' : 'bg-slate-100 text-slate-700' }}">
                                    {{ $enabled ? 'Enabled' : 'Disabled' }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-right space-x-2">
                                @if($config)
                                    <a href="{{ route('admin.ai-features.edit', $config) }}" class="text-indigo-600 hover:text-indigo-700 text-sm font-medium">Edit</a>
                                @else
                                    <span class="text-slate-400 text-sm">Not configured</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="px-6 py-12 text-center text-slate-400">No AI features configured yet.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

@endsection
