@extends('layouts.admin')
@section('title', 'Services')
@section('page-actions')
<a href="{{ route('admin.services.create') }}" class="btn btn-primary">
    Add Service
</a>
@endsection
@section('content')

<div class="card">
    <div class="table-responsive">
        <table class="table table-vcenter card-table">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Price</th>
                    <th>Duration</th>
                    <th>Status</th>
                    <th>Created</th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($services ?? [] as $service)
                <tr>
                    <td>{{ $service->name }}</td>
                    <td class="text-muted">{{ $service->price ? '$'.number_format($service->price, 2) : 'Free' }}</td>
                    <td class="text-muted">{{ $service->duration ?? 'N/A' }}</td>
                    <td>
                        @if($service->is_active ?? true)
                            <span class="badge bg-green-lt">Active</span>
                        @else
                            <span class="badge bg-secondary">Inactive</span>
                        @endif
                    </td>
                    <td class="text-muted">{{ wib($service->created_at, 'd F Y', false) }}</td>
                    <td class="text-end">
                        <a href="{{ route('admin.services.edit', $service) }}" class="btn btn-sm">Edit</a>
                        <form action="{{ route('admin.services.destroy', $service) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete?')">
                            @csrf @method('DELETE')
                            <button type="submit" class="btn btn-sm btn-danger">Delete</button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr><td colspan="6"><div class="empty"><p class="empty-title">No services found.</p><p class="empty-subtitle text-muted">Add your first service to get started.</p></div></td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="card-footer d-flex align-items-center justify-content-center">
        {{ ($services ?? collect())->links() }}
    </div>
</div>

@endsection
