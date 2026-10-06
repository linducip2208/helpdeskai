@extends('layouts.admin')
@section('title', 'Departments')
@section('page-actions')
<button type="button" onclick="document.getElementById('create-modal').style.display='flex'" class="btn btn-primary">
    Add Department
</button>
@endsection
@section('content')

<div class="card">
    <div class="table-responsive">
        <table class="table table-vcenter card-table">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Description</th>
                    <th>Status</th>
                    <th>Created</th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($departments ?? [] as $department)
                <tr>
                    <td>{{ $department->name }}</td>
                    <td class="text-muted">{{ Str::limit($department->description ?? '', 60) }}</td>
                    <td>
                        @if($department->is_active ?? true)
                            <span class="badge bg-green-lt">Active</span>
                        @else
                            <span class="badge bg-secondary">Inactive</span>
                        @endif
                    </td>
                    <td class="text-muted">{{ wib($department->created_at, 'd F Y', false) }}</td>
                    <td class="text-end">
                        <a href="{{ route('admin.departments.edit', $department) }}" class="btn btn-sm">Edit</a>
                        <form action="{{ route('admin.departments.destroy', $department) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete?')">
                            @csrf @method('DELETE')
                            <button type="submit" class="btn btn-sm btn-danger">Delete</button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr><td colspan="5"><div class="empty"><p class="empty-title">No departments found.</p><p class="empty-subtitle text-muted">Add your first department to get started.</p></div></td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<div id="create-modal" style="display:none;position:fixed;inset:0;z-index:50;align-items:center;justify-content:center;background:rgba(0,0,0,.5);">
    <div class="card" style="width:100%;max-width:28rem;margin:0 1rem;">
        <div class="card-header">
            <h3 class="card-title">Add Department</h3>
            <div class="card-actions">
                <button onclick="document.getElementById('create-modal').style.display='none'" class="btn btn-sm">&times;</button>
            </div>
        </div>
        <div class="card-body">
            <form action="{{ route('admin.departments.store') }}" method="POST">
                @csrf
                <div class="mb-3">
                    <label class="form-label">Name</label>
                    <input type="text" name="name" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Description</label>
                    <textarea name="description" rows="2" class="form-control"></textarea>
                </div>
                <div class="mb-3">
                    <label class="form-check form-switch">
                        <input type="checkbox" name="is_active" value="1" checked class="form-check-input">
                        <span class="form-check-label">Active</span>
                    </label>
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
