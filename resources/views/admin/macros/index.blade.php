@extends('layouts.admin')
@section('title', __('Macros'))
@section('page-actions')
    <a href="{{ route('admin.macros.create') }}" class="btn btn-primary">{{ __('New Macro') }}</a>
@endsection
@section('content')

<div class="card">
    <div class="table-responsive"><table class="table table-vcenter card-table">
        <thead><tr><th>{{ __('Name') }}</th><th>{{ __('Visibility') }}</th><th>{{ __('Owner') }}</th><th>{{ __('Status') }}</th><th class="text-end">{{ __('Actions') }}</th></tr></thead>
        <tbody>
            @forelse($macros ?? [] as $macro)
            <tr>
                <td><strong>{{ $macro->name }}</strong></td>
                <td class="text-muted">{{ ucfirst($macro->visibility) }}</td>
                <td class="text-muted">{{ $macro->user->name ?? '—' }}</td>
                <td>@if($macro->is_active)<span class="badge bg-green-lt">{{ __('Active') }}</span>@else<span class="badge bg-secondary">{{ __('Inactive') }}</span>@endif</td>
                <td class="text-end">
                    <form action="{{ route('admin.macros.destroy', $macro) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete this macro?')">
                        @csrf @method('DELETE')
                        <button type="submit" class="btn btn-sm btn-danger">{{ __('Delete') }}</button>
                    </form>
                </td>
            </tr>
            @empty
            <tr><td colspan="5"><div class="empty"><p class="empty-title">{{ __('No macros yet.') }}</p><p class="empty-subtitle text-muted">{{ __('Macros run reply + status + assign + tags in one click.') }}</p></div></td></tr>
            @endforelse
        </tbody>
    </table></div>
    <div class="card-footer d-flex align-items-center justify-content-center">
        {{ ($macros ?? collect())->links() }}
    </div>
</div>
@endsection
