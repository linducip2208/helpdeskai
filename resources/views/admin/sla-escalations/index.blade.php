@extends('layouts.admin')
@section('title', __('SLA Escalations'))
@section('page-actions')
    <a href="{{ route('admin.sla-escalations.create') }}" class="btn btn-primary">{{ __('New Rule') }}</a>
@endsection
@section('content')

<div class="card">
    <div class="table-responsive"><table class="table table-vcenter card-table">
        <thead><tr><th>{{ __('Name') }}</th><th>{{ __('Trigger') }}</th><th>{{ __('After') }}</th><th>{{ __('Action') }}</th><th>{{ __('Status') }}</th><th class="text-end">{{ __('Actions') }}</th></tr></thead>
        <tbody>
            @forelse($rules ?? [] as $rule)
            <tr>
                <td><strong>{{ $rule->name }}</strong></td>
                <td class="font-monospace small">{{ $rule->trigger }}</td>
                <td class="text-muted">{{ $rule->after_minutes }}m</td>
                <td class="text-muted small">
                    @if($rule->action_priority){{ __('Priority') }}: {{ $rule->action_priority }}@endif
                    @if($rule->action_assign_role){{ __('Assign') }}: {{ $rule->action_assign_role }}@endif
                </td>
                <td>@if($rule->is_active)<span class="badge bg-green-lt">{{ __('Active') }}</span>@else<span class="badge bg-secondary">{{ __('Inactive') }}</span>@endif</td>
                <td class="text-end text-nowrap">
                    <a href="{{ route('admin.sla-escalations.edit', $rule) }}" class="btn btn-sm">{{ __('Edit') }}</a>
                    <form action="{{ route('admin.sla-escalations.destroy', $rule) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete this rule?')">
                        @csrf @method('DELETE')
                        <button type="submit" class="btn btn-sm btn-danger">{{ __('Delete') }}</button>
                    </form>
                </td>
            </tr>
            @empty
            <tr><td colspan="6"><div class="empty"><p class="empty-title">{{ __('No escalation rules yet.') }}</p></div></td></tr>
            @endforelse
        </tbody>
    </table></div>
    <div class="card-footer d-flex align-items-center justify-content-center">
        {{ ($rules ?? collect())->links() }}
    </div>
</div>
@endsection
