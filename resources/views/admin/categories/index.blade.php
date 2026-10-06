@extends('layouts.admin')
@section('title', 'Categories')
@section('page-actions')
<button type="button" onclick="document.getElementById('create-modal').style.display='flex'" class="btn btn-primary">
    Add Category
</button>
@endsection
@section('content')

<div class="card">
    <div class="table-responsive">
        <table class="table table-vcenter card-table">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Department</th>
                    <th>Tickets</th>
                    <th>Status</th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($categories ?? [] as $category)
                <tr>
                    <td>{{ $category->name }}</td>
                    <td class="text-muted">{{ $category->department->name ?? 'N/A' }}</td>
                    <td class="text-muted">{{ $category->tickets_count ?? 0 }}</td>
                    <td>
                        @if($category->is_active ?? true)
                            <span class="badge bg-green-lt">Active</span>
                        @else
                            <span class="badge bg-secondary">Inactive</span>
                        @endif
                    </td>
                    <td class="text-end">
                        <a href="{{ route('admin.categories.edit', $category) }}" class="btn btn-sm">Edit</a>
                        <form action="{{ route('admin.categories.destroy', $category) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete?')">
                            @csrf @method('DELETE')
                            <button type="submit" class="btn btn-sm btn-danger">Delete</button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr><td colspan="5"><div class="empty"><p class="empty-title">No categories found.</p><p class="empty-subtitle text-muted">Add your first category to get started.</p></div></td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<div id="create-modal" style="display:none;position:fixed;inset:0;z-index:50;align-items:center;justify-content:center;background:rgba(0,0,0,.5);">
    <div class="card" style="width:100%;max-width:28rem;margin:0 1rem;">
        <div class="card-header">
            <h3 class="card-title">Add Category</h3>
            <div class="card-actions">
                <button onclick="document.getElementById('create-modal').style.display='none'" class="btn btn-sm">&times;</button>
            </div>
        </div>
        <div class="card-body">
            <form action="{{ route('admin.categories.store') }}" method="POST">
                @csrf
                <div class="mb-3">
                    <label class="form-label">Name</label>
                    <input type="text" name="name" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Department</label>
                    <select name="department_id" class="form-select">
                        <option value="">Select Department</option>
                        @foreach($departments ?? [] as $dept)
                        <option value="{{ $dept->id }}">{{ $dept->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-footer d-flex justify-content-end gap-2">
                    <button type="button" onclick="document.getElementById('create-modal').style.display='none'" class="btn">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save</button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection
