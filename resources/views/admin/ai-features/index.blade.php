@extends('layouts.admin')
@section('title', 'AI Features')
@section('content')

<div class="card">
    <div class="table-responsive">
        <table class="table table-vcenter card-table">
            <thead>
                <tr>
                    <th>Feature</th>
                    <th>Provider</th>
                    <th>Model</th>
                    <th>Prompt</th>
                    <th>Status</th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($features ?? [] as $key => $feature)
                    @php
                        $config = ($configs ?? collect())->get($key);
                        $label = is_array($feature) ? ($feature['name'] ?? $key) : (is_string($feature) ? $feature : $key);
                    @endphp
                    <tr>
                        <td>{{ $label }}</td>
                        <td class="text-muted">{{ optional(optional($config)->provider)->name ?? '—' }}</td>
                        <td class="text-muted"><code>{{ optional(optional($config)->model)->name ?? 'default' }}</code></td>
                        <td class="text-muted">{{ \Illuminate\Support\Str::limit(optional($config)->system_prompt ?? '', 50) }}</td>
                        <td>
                            @php $enabled = $config?->is_enabled ?? false; @endphp
                            @if($enabled)
                                <span class="badge bg-green-lt">Enabled</span>
                            @else
                                <span class="badge bg-secondary">Disabled</span>
                            @endif
                        </td>
                        <td class="text-end">
                            @if($config)
                                <a href="{{ route('admin.ai-features.edit', $config) }}" class="btn btn-sm">Edit</a>
                            @else
                                <span class="text-muted">Not configured</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6"><div class="empty"><p class="empty-title">No AI features configured yet.</p><p class="empty-subtitle text-muted">Configure features after adding a provider.</p></div></td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

@endsection
