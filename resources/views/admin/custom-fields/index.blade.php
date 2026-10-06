@extends('layouts.admin')
@section('title', __('Custom Fields'))
@section('page-actions')
    <a href="{{ route('admin.custom-fields.create') }}" class="btn btn-primary">{{ __('Add Field') }}</a>
@endsection
@section('content')

<div class="card">
    <div class="table-responsive"><table class="table table-vcenter card-table">
        <thead><tr><th>{{ __('Label') }}</th><th>{{ __('Name') }}</th><th>{{ __('Type') }}</th><th>{{ __('Department') }}</th><th>{{ __('Required') }}</th><th>{{ __('Status') }}</th><th class="text-end">{{ __('Actions') }}</th></tr></thead>
        <tbody>
            @forelse($fields ?? [] as $field)
            <tr>
                <td><strong>{{ $field->label }}</strong></td>
                <td class="text-muted font-monospace small">{{ $field->name }}</td>
                <td><span class="badge bg-blue-lt">{{ $field->type }}</span></td>
                <td class="text-muted">{{ $field->department->name ?? __('All') }}</td>
                <td>@if($field->is_required)<span class="badge bg-yellow-lt">{{ __('Yes') }}</span>@else<span class="text-muted">—</span>@endif</td>
                <td>
                    @if($field->is_active)<span class="badge bg-green-lt">{{ __('Active') }}</span>
                    @else<span class="badge bg-secondary">{{ __('Inactive') }}</span>@endif
                </td>
                <td class="text-end text-nowrap">
                    <a href="{{ route('admin.custom-fields.edit', $field) }}" class="btn btn-sm">{{ __('Edit') }}</a>
                    <form action="{{ route('admin.custom-fields.destroy', $field) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete this field? Existing ticket values are kept as-is.')">
                        @csrf @method('DELETE')
                        <button type="submit" class="btn btn-sm btn-danger">{{ __('Delete') }}</button>
                    </form>
                </td>
            </tr>
            @empty
            <tr><td colspan="7"><div class="empty"><p class="empty-title">{{ __('No custom fields yet.') }}</p><p class="empty-subtitle text-muted">{{ __('Add fields to collect structured data on tickets.') }}</p></div></td></tr>
            @endforelse
        </tbody>
    </table></div>
    <div class="card-footer d-flex align-items-center justify-content-center">
        {{ ($fields ?? collect())->links() }}
    </div>
</div>
@endsection
