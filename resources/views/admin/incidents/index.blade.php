@extends('layouts.admin')
@section('title', __('Incidents'))
@section('page-actions')
<a href="{{ route('admin.incidents.create') }}" class="btn btn-primary">{{ __('Add Incident') }}</a>
@endsection
@section('content')
<div class="card">
    <div class="table-responsive">
        <table class="table table-vcenter card-table">
            <thead>
                <tr>
                    <th>{{ __('Title') }}</th>
                    <th>{{ __('Severity') }}</th>
                    <th>{{ __('Status') }}</th>
                    <th>{{ __('Owner') }}</th>
                    <th>{{ __('Tickets') }}</th>
                    <th class="text-end">{{ __('Actions') }}</th>
                </tr>
            </thead>
            <tbody>
                @forelse($incidents ?? [] as $incident)
                <tr>
                    <td><a href="{{ route('admin.incidents.show', $incident) }}">{{ $incident->title }}</a></td>
                    <td><span class="badge bg-secondary">{{ ucfirst($incident->severity) }}</span></td>
                    <td><span class="badge bg-blue-lt">{{ ucfirst($incident->status) }}</span></td>
                    <td class="text-muted">{{ $incident->owner?->name ?? '—' }}</td>
                    <td class="text-muted">{{ $incident->tickets_count ?? $incident->tickets->count() }}</td>
                    <td class="text-end">
                        <a href="{{ route('admin.incidents.show', $incident) }}" class="btn btn-sm">{{ __('View') }}</a>
                        <a href="{{ route('admin.incidents.edit', $incident) }}" class="btn btn-sm">{{ __('Edit') }}</a>
                        <form action="{{ route('admin.incidents.destroy', $incident) }}" method="POST" class="d-inline" onsubmit="return confirm('{{ __('Delete?') }}')">
                            @csrf @method('DELETE')
                            <button type="submit" class="btn btn-sm btn-danger">{{ __('Delete') }}</button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr><td colspan="6"><div class="empty"><p class="empty-title">{{ __('No incidents found.') }}</p><p class="empty-subtitle text-muted">{{ __('Add your first incident to get started.') }}</p></div></td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if(isset($incidents) && method_exists($incidents, 'links'))
    <div class="card-footer d-flex justify-content-end">{{ $incidents->links() }}</div>
    @endif
</div>
@endsection
