@extends('layouts.admin')
@section('title', 'Email Templates')
@section('content')

<div class="card">
    <div class="table-responsive">
        <table class="table table-vcenter card-table">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Subject</th>
                    <th>Type</th>
                    <th>Status</th>
                    <th>Updated</th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($templates ?? [] as $template)
                <tr>
                    <td>{{ $template->name }}</td>
                    <td class="text-muted">{{ $template->subject }}</td>
                    <td class="text-muted">{{ $template->type ?? 'General' }}</td>
                    <td>
                        @if($template->is_active ?? true)
                            <span class="badge bg-green-lt">Active</span>
                        @else
                            <span class="badge bg-secondary">Inactive</span>
                        @endif
                    </td>
                    <td class="text-muted">{{ wib($template->updated_at, 'd F Y', false) }}</td>
                    <td class="text-end">
                        <a href="{{ route('admin.email-templates.edit', $template) }}" class="btn btn-sm">Edit</a>
                    </td>
                </tr>
                @empty
                <tr><td colspan="6"><div class="empty"><p class="empty-title">No templates found.</p><p class="empty-subtitle text-muted">No email templates configured.</p></div></td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="card-footer d-flex align-items-center justify-content-center">
        {{ ($templates ?? collect())->links() }}
    </div>
</div>

@endsection
