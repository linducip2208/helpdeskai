@extends('layouts.admin')
@section('title', 'Knowledge Base')
@section('page-actions')
<a href="{{ route('admin.knowledge.create') }}" class="btn btn-primary">
    New Article
</a>
@endsection
@section('content')

<div class="card mb-3">
    <div class="card-body">
        <form method="GET" action="{{ url()->current() }}">
            <div class="row g-2">
                <div class="col-md-6">
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Search articles..." class="form-control">
                </div>
                <div class="col-md-4">
                    <select name="category_id" class="form-select">
                        <option value="">All Categories</option>
                        @foreach($categories ?? [] as $cat)
                        <option value="{{ $cat->id }}" {{ request('category_id') == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                        @endforeach
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
                    <th>Category</th>
                    <th>Views</th>
                    <th>Status</th>
                    <th>Updated</th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($articles ?? [] as $article)
                <tr>
                    <td>{{ $article->title }}</td>
                    <td class="text-muted">{{ $article->category->name ?? 'N/A' }}</td>
                    <td class="text-muted">{{ $article->views ?? 0 }}</td>
                    <td>
                        @if($article->is_published ?? false)
                            <span class="badge bg-green-lt">Published</span>
                        @else
                            <span class="badge bg-secondary">Draft</span>
                        @endif
                    </td>
                    <td class="text-muted">{{ wib($article->updated_at, 'd F Y', false) }}</td>
                    <td class="text-end">
                        <a href="{{ route('admin.knowledge.edit', $article) }}" class="btn btn-sm">Edit</a>
                        <form action="{{ route('admin.knowledge.destroy', $article) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete?')">
                            @csrf @method('DELETE')
                            <button type="submit" class="btn btn-sm btn-danger">Delete</button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr><td colspan="6"><div class="empty"><p class="empty-title">No articles found.</p><p class="empty-subtitle text-muted">Create your first knowledge base article.</p></div></td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="card-footer d-flex align-items-center justify-content-center">
        {{ ($articles ?? collect())->links() }}
    </div>
</div>

@endsection
