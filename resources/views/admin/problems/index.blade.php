@extends('layouts.admin')
@section('title', __('Problems'))
@section('page-actions')
<a href="{{ route('admin.problems.create') }}" class="btn btn-primary">{{ __('Add Problem') }}</a>
@endsection
@section('content')
<div class="card">
    <div class="table-responsive">
        <table class="table table-vcenter card-table">
            <thead>
                <tr>
                    <th>{{ __('Title') }}</th>
                    <th>{{ __('Status') }}</th>
                    <th>{{ __('Owner') }}</th>
                    <th>{{ __('Tickets') }}</th>
                    <th>{{ __('Incidents') }}</th>
                    <th class="text-end">{{ __('Actions') }}</th>
                </tr>
            </thead>
            <tbody>
                @forelse($problems ?? [] as $problem)
                <tr>
                    <td><a href="{{ route('admin.problems.show', $problem) }}">{{ $problem->title }}</a></td>
                    <td><span class="badge bg-blue-lt">{{ ucfirst($problem->status) }}</span></td>
                    <td class="text-muted">{{ $problem->owner?->name ?? '—' }}</td>
                    <td class="text-muted">{{ $problem->tickets_count ?? $problem->tickets->count() }}</td>
                    <td class="text-muted">{{ $problem->incidents_count ?? $problem->incidents->count() }}</td>
                    <td class="text-end">
                        <a href="{{ route('admin.problems.show', $problem) }}" class="btn btn-sm">{{ __('View') }}</a>
                        <a href="{{ route('admin.problems.edit', $problem) }}" class="btn btn-sm">{{ __('Edit') }}</a>
                        <form action="{{ route('admin.problems.destroy', $problem) }}" method="POST" class="d-inline" onsubmit="return confirm('{{ __('Delete?') }}')">
                            @csrf @method('DELETE')
                            <button type="submit" class="btn btn-sm btn-danger">{{ __('Delete') }}</button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr><td colspan="6"><div class="empty"><p class="empty-title">{{ __('No problems found.') }}</p><p class="empty-subtitle text-muted">{{ __('Add your first problem to get started.') }}</p></div></td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if(isset($problems) && method_exists($problems, 'links'))
    <div class="card-footer d-flex justify-content-end">{{ $problems->links() }}</div>
    @endif
</div>
@endsection
