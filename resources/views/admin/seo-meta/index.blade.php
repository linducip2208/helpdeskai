@extends('layouts.admin')
@section('title', 'SEO Meta Overrides')
@section('page-actions')
<a href="{{ route('admin.seo-meta.create') }}" class="btn btn-primary">Add Override</a>
@endsection
@section('content')

<p class="text-muted mb-3">Per-URL override untuk title, description, og:image, dan JSON-LD schema. Berlaku saat URL aplikasi match pattern.</p>

<div class="card">
    <div class="table-responsive">
        <table class="table table-vcenter card-table">
            <thead>
                <tr>
                    <th>URL Pattern</th>
                    <th>Meta Title</th>
                    <th>Canonical</th>
                    <th class="text-center">Noindex</th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($items as $item)
                    <tr>
                        <td><code>{{ $item->url_pattern ?? '—' }}</code></td>
                        <td>{{ $item->meta_title ?? '—' }}</td>
                        <td class="text-muted">{{ $item->canonical_url ?? '—' }}</td>
                        <td class="text-center">
                            @if($item->noindex)
                                <span class="badge bg-red-lt">noindex</span>
                            @else
                                <span class="badge bg-green-lt">index</span>
                            @endif
                        </td>
                        <td class="text-end">
                            <a href="{{ route('admin.seo-meta.edit', $item) }}" class="btn btn-sm">Edit</a>
                            <form method="POST" action="{{ route('admin.seo-meta.destroy', $item) }}" class="d-inline" onsubmit="return confirm('Delete this override?')">
                                @csrf @method('DELETE')
                                <button class="btn btn-sm btn-danger">Delete</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="5"><div class="empty"><p class="empty-title">Belum ada SEO override.</p><p class="empty-subtitle text-muted">Add Override untuk meta per URL.</p></div></td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($items->hasPages())
        <div class="card-footer d-flex align-items-center justify-content-center">{{ $items->links() }}</div>
    @endif
</div>

@endsection
