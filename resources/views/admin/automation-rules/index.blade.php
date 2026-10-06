@extends('layouts.admin')
@section('title', 'Automation Rules')
@section('page-actions')
<a href="{{ route('admin.automation-rules.create') }}" class="btn btn-primary">
    Add Rule
</a>
@endsection
@section('content')

<div class="card">
    <div class="table-responsive">
        <table class="table table-vcenter card-table">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Trigger</th>
                    <th>Action</th>
                    <th>Status</th>
                    <th>Updated</th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($rules ?? [] as $rule)
                <tr>
                    <td>{{ $rule->name }}</td>
                    <td class="text-muted">{{ $rule->trigger_event ?? 'N/A' }}</td>
                    <td class="text-muted">{{ $rule->action_type ?? 'N/A' }}</td>
                    <td>
                        @if($rule->is_active ?? true)
                            <span class="badge bg-green-lt">Active</span>
                        @else
                            <span class="badge bg-secondary">Inactive</span>
                        @endif
                    </td>
                    <td class="text-muted">{{ wib($rule->updated_at, 'd F Y', false) }}</td>
                    <td class="text-end">
                        <a href="{{ route('admin.automation-rules.edit', $rule) }}" class="btn btn-sm">Edit</a>
                        <form action="{{ route('admin.automation-rules.destroy', $rule) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete?')">
                            @csrf @method('DELETE')
                            <button type="submit" class="btn btn-sm btn-danger">Delete</button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr><td colspan="6"><div class="empty"><p class="empty-title">No automation rules found.</p><p class="empty-subtitle text-muted">Add your first rule to get started.</p></div></td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

@endsection
