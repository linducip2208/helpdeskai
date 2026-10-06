@extends('layouts.admin')
@section('title', 'API Keys')
@section('page-actions')
<button type="button" onclick="document.getElementById('create-modal').style.display='flex'" class="btn btn-primary">
    Generate Key
</button>
@endsection
@section('content')

@if (session('plain_key'))
    <div class="alert alert-warning">
        <p class="mb-2"><strong>{{ session('success') }}</strong></p>
        <code class="d-block">{{ session('plain_key') }}</code>
    </div>
@elseif (session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
@endif

<div class="card">
    <div class="table-responsive">
        <table class="table table-vcenter card-table">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Key</th>
                    <th>Permissions</th>
                    <th>Last Used</th>
                    <th>Created</th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($apiKeys ?? [] as $key)
                <tr>
                    <td>{{ $key->name }}</td>
                    <td class="text-muted"><code>{{ Str::limit($key->masked_key ?? '••••••••••••••••', 16) }}</code></td>
                    <td class="text-muted">{{ $key->permissions ?? 'read' }}</td>
                    <td class="text-muted">{{ $key->last_used_at?->diffForHumans() ?? 'Never' }}</td>
                    <td class="text-muted">{{ wib($key->created_at, 'd F Y', false) }}</td>
                    <td class="text-end">
                        <form action="{{ route('admin.api-keys.destroy', $key) }}" method="POST" class="d-inline" onsubmit="return confirm('Revoke this key?')">
                            @csrf @method('DELETE')
                            <button type="submit" class="btn btn-sm btn-danger">Revoke</button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr><td colspan="6"><div class="empty"><p class="empty-title">No API keys generated yet.</p><p class="empty-subtitle text-muted">Generate your first key to get started.</p></div></td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<div id="create-modal" style="display:none;position:fixed;inset:0;z-index:50;align-items:center;justify-content:center;background:rgba(0,0,0,.5);">
    <div class="card" style="width:100%;max-width:28rem;margin:0 1rem;">
        <div class="card-header">
            <h3 class="card-title">Generate API Key</h3>
            <div class="card-actions">
                <button onclick="document.getElementById('create-modal').style.display='none'" class="btn btn-sm">&times;</button>
            </div>
        </div>
        <div class="card-body">
            <form method="POST" action="{{ route('admin.api-keys.store') }}">
                @csrf
                <div class="mb-3">
                    <label class="form-label">Key Name</label>
                    <input type="text" name="name" placeholder="e.g. Mobile App, Integration" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Permissions</label>
                    <select name="permissions" class="form-select">
                        <option value="read">Read Only</option>
                        <option value="read-write">Read &amp; Write</option>
                        <option value="full">Full Access</option>
                    </select>
                </div>
                <div class="form-footer d-flex justify-content-end gap-2">
                    <button type="button" onclick="document.getElementById('create-modal').style.display='none'" class="btn">Cancel</button>
                    <button type="submit" class="btn btn-primary">Generate</button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection
