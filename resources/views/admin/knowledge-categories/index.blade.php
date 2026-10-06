@extends('layouts.admin')
@section('title', 'Knowledge Base Categories')
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
                    <th>Slug</th>
                    <th>Articles</th>
                    <th>Order</th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($categories ?? [] as $category)
                <tr>
                    <td>{{ $category->name }}</td>
                    <td class="text-muted">{{ $category->slug }}</td>
                    <td class="text-muted">{{ $category->articles_count ?? 0 }}</td>
                    <td class="text-muted">{{ $category->sort_order ?? 0 }}</td>
                    <td class="text-end">
                        <a href="{{ route('admin.knowledge-categories.edit', $category) }}" class="btn btn-sm">Edit</a>
                        <form action="{{ route('admin.knowledge-categories.destroy', $category) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete?')">
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
            <form method="POST" action="{{ route('admin.knowledge-categories.store') }}">
                @csrf
                <div class="mb-3">
                    <label class="form-label">Name</label>
                    <input type="text" name="name" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Description</label>
                    <textarea name="description" rows="2" class="form-control"></textarea>
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
