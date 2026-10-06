@extends('layouts.admin')
@section('title', 'Blog Posts')
@section('page-actions')
<a href="{{ route('admin.posts.create') }}" class="btn btn-primary">
    New Post
</a>
@endsection
@section('content')

<div class="card">
    <div class="table-responsive">
        <table class="table table-vcenter card-table">
            <thead>
                <tr>
                    <th>Title</th>
                    <th>Author</th>
                    <th>Category</th>
                    <th>Status</th>
                    <th>Published</th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($posts ?? [] as $post)
                <tr>
                    <td>{{ $post->title }}</td>
                    <td class="text-muted">{{ $post->author->name ?? 'N/A' }}</td>
                    <td class="text-muted">{{ $post->category->name ?? 'N/A' }}</td>
                    <td>
                        @if($post->is_published ?? false)
                            <span class="badge bg-green-lt">Published</span>
                        @else
                            <span class="badge bg-secondary">Draft</span>
                        @endif
                    </td>
                    <td class="text-muted">{{ $post->published_at ? wib($post->published_at, 'd F Y', false) : __('Not published') }}</td>
                    <td class="text-end">
                        <a href="{{ route('admin.posts.edit', $post) }}" class="btn btn-sm">Edit</a>
                        <form action="{{ route('admin.posts.destroy', $post) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete?')">
                            @csrf @method('DELETE')
                            <button type="submit" class="btn btn-sm btn-danger">Delete</button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr><td colspan="6"><div class="empty"><p class="empty-title">No posts found.</p><p class="empty-subtitle text-muted">Create your first blog post.</p></div></td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="card-footer d-flex align-items-center justify-content-center">
        {{ ($posts ?? collect())->links() }}
    </div>
</div>

@endsection
