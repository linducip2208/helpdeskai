@extends('layouts.admin')
@section('title', __('Organizations'))
@section('page-actions')
    <a href="{{ route('admin.organizations.create') }}" class="btn btn-primary">{{ __('New Organization') }}</a>
@endsection
@section('content')
<div class="card">
    <div class="table-responsive">
        <table class="table table-vcenter card-table">
            <thead>
                <tr>
                    <th>{{ __('Name') }}</th>
                    <th>{{ __('Domain') }}</th>
                    <th>{{ __('Users') }}</th>
                    <th class="text-end">{{ __('Actions') }}</th>
                </tr>
            </thead>
            <tbody>
                @forelse($organizations as $organization)
                    <tr>
                        <td><a href="{{ route('admin.organizations.show', $organization) }}">{{ $organization->name }}</a></td>
                        <td class="text-muted">{{ $organization->domain ?? '—' }}</td>
                        <td><span class="badge bg-blue-lt">{{ $organization->users_count }}</span></td>
                        <td class="text-end">
                            <a href="{{ route('admin.organizations.show', $organization) }}" class="btn btn-sm">{{ __('View') }}</a>
                            <a href="{{ route('admin.organizations.edit', $organization) }}" class="btn btn-sm">{{ __('Edit') }}</a>
                            <form action="{{ route('admin.organizations.destroy', $organization) }}" method="POST" class="d-inline" onsubmit="return confirm('{{ __('Delete?') }}')">
                                @csrf @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-danger">{{ __('Delete') }}</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="4"><div class="empty"><p class="empty-title">{{ __('No organizations found.') }}</p></div></td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if(method_exists($organizations, 'links'))
        <div class="card-footer">{{ $organizations->links() }}</div>
    @endif
</div>
@endsection
