@extends('layouts.admin')
@section('title', __('Tags'))
@section('content')

<div class="row">
    <div class="col-12 col-lg-8">
        <div class="card">
            <div class="card-header"><h3 class="card-title">{{ __('Tags') }}</h3></div>
            <div class="table-responsive"><table class="table table-vcenter card-table">
                <thead><tr><th>{{ __('Name') }}</th><th>{{ __('Tickets') }}</th><th class="text-end">{{ __('Actions') }}</th></tr></thead>
                <tbody>
                    @forelse($tags ?? [] as $tag)
                    <tr>
                        <td><span class="badge bg-{{ $tag->color }}-lt">{{ $tag->name }}</span></td>
                        <td class="text-muted">{{ $tag->tickets_count ?? $tag->tickets()->count() }}</td>
                        <td class="text-end">
                            <form action="{{ route('admin.tags.destroy', $tag) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete this tag?')">
                                @csrf @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-danger">{{ __('Delete') }}</button>
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="3"><div class="empty"><p class="empty-title">{{ __('No tags yet.') }}</p></div></td></tr>
                    @endforelse
                </tbody>
            </table></div>
            <div class="card-footer d-flex align-items-center justify-content-center">
                {{ ($tags ?? collect())->links() }}
            </div>
        </div>
    </div>
    <div class="col-12 col-lg-4">
        <div class="card">
            <div class="card-header"><h3 class="card-title">{{ __('Add Tag') }}</h3></div>
            <div class="card-body">
                <form action="{{ route('admin.tags.store') }}" method="POST">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label" for="name">{{ __('Name') }}</label>
                        <input type="text" name="name" id="name" class="form-control" required>
                    </div>
                    <button type="submit" class="btn btn-primary w-100">{{ __('Add Tag') }}</button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
