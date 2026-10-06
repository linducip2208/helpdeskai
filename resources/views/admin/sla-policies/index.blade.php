@extends('layouts.admin')
@section('title', 'SLA Policies')
@section('page-actions')
<a href="{{ route('admin.sla-policies.create') }}" class="btn btn-primary">
    Add SLA Policy
</a>
@endsection
@section('content')

<div class="card">
    <div class="table-responsive">
        <table class="table table-vcenter card-table">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Response Time</th>
                    <th>Resolution Time</th>
                    <th>Priority</th>
                    <th>Status</th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($policies ?? [] as $policy)
                <tr>
                    <td>{{ $policy->name }}</td>
                    <td class="text-muted">{{ $policy->first_response_time ?? 'N/A' }} min</td>
                    <td class="text-muted">{{ $policy->resolution_time ?? 'N/A' }} min</td>
                    <td class="text-muted">{{ ucfirst($policy->priority ?? 'normal') }}</td>
                    <td>
                        @if($policy->is_active ?? true)
                            <span class="badge bg-green-lt">Active</span>
                        @else
                            <span class="badge bg-secondary">Inactive</span>
                        @endif
                    </td>
                    <td class="text-end">
                        <a href="{{ route('admin.sla-policies.edit', $policy) }}" class="btn btn-sm">Edit</a>
                        <form action="{{ route('admin.sla-policies.destroy', $policy) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete?')">
                            @csrf @method('DELETE')
                            <button type="submit" class="btn btn-sm btn-danger">Delete</button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr><td colspan="6"><div class="empty"><p class="empty-title">No SLA policies found.</p><p class="empty-subtitle text-muted">Add your first SLA policy to get started.</p></div></td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

@endsection
