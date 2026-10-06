@extends('layouts.admin')
@section('title', 'AI Providers')
@section('page-actions')
<a href="{{ route('admin.ai-providers.create') }}" class="btn btn-primary">
    Add Provider
</a>
@endsection
@section('content')

<div class="card">
    <div class="table-responsive">
        <table class="table table-vcenter card-table">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>API Format</th>
                    <th>Base URL</th>
                    <th>Models</th>
                    <th>Status</th>
                    <th>Added</th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($providers ?? [] as $provider)
                <tr>
                    <td>{{ $provider->name }}</td>
                    <td><code>{{ $provider->api_format }}</code></td>
                    <td class="text-muted">{{ Str::limit($provider->base_url ?? '', 30) }}</td>
                    <td class="text-muted">{{ $provider->models_count ?? 0 }}</td>
                    <td>
                        @if($provider->is_active ?? true)
                            <span class="badge bg-green-lt">Active</span>
                        @else
                            <span class="badge bg-secondary">Inactive</span>
                        @endif
                    </td>
                    <td class="text-muted">{{ wib($provider->created_at, 'd F Y', false) }}</td>
                    <td class="text-end">
                        <a href="{{ route('admin.ai-providers.edit', $provider) }}" class="btn btn-sm">Edit</a>
                        <form action="{{ route('admin.ai-providers.destroy', $provider) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete?')">
                            @csrf @method('DELETE')
                            <button type="submit" class="btn btn-sm btn-danger">Delete</button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr><td colspan="7"><div class="empty"><p class="empty-title">No AI providers configured.</p><p class="empty-subtitle text-muted"><a href="{{ route('admin.ai-providers.create') }}">Add your first provider</a>.</p></div></td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

@endsection
