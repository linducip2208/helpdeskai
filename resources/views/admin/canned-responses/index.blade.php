@extends('layouts.admin')
@section('title', 'Canned Responses')
@section('page-actions')
<a href="{{ route('admin.canned-responses.create') }}" class="btn btn-primary">
    Add Response
</a>
@endsection
@section('content')

<div class="card mb-3">
    <div class="card-body">
        <form method="GET" action="{{ url()->current() }}">
            <div class="row g-2">
                <div class="col-md-6">
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Search responses..." class="form-control">
                </div>
                <div class="col-md-4">
                    <select name="category_id" class="form-select">
                        <option value="">All Categories</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn w-100">Filter</button>
                </div>
            </div>
        </form>
    </div>
</div>

<div class="card">
    <div class="table-responsive">
        <table class="table table-vcenter card-table">
            <thead>
                <tr>
                    <th>Title</th>
                    <th>Shortcut</th>
                    <th>Category</th>
                    <th>Status</th>
                    <th>Used</th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($responses ?? [] as $response)
                <tr>
                    <td>{{ $response->title }}</td>
                    <td><code>{{ $response->shortcut ?? 'N/A' }}</code></td>
                    <td class="text-muted">{{ $response->category->name ?? 'General' }}</td>
                    <td>
                        @if($response->is_active ?? true)
                            <span class="badge bg-green-lt">Active</span>
                        @else
                            <span class="badge bg-secondary">Inactive</span>
                        @endif
                    </td>
                    <td class="text-muted">{{ $response->usage_count ?? 0 }}</td>
                    <td class="text-end">
                        <a href="{{ route('admin.canned-responses.edit', $response) }}" class="btn btn-sm">Edit</a>
                        <form action="{{ route('admin.canned-responses.destroy', $response) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete?')">
                            @csrf @method('DELETE')
                            <button type="submit" class="btn btn-sm btn-danger">Delete</button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr><td colspan="6"><div class="empty"><p class="empty-title">No canned responses found.</p><p class="empty-subtitle text-muted">Add your first response to get started.</p></div></td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="card-footer d-flex align-items-center justify-content-center">
        {{ ($responses ?? collect())->links() }}
    </div>
</div>

@endsection
