@extends('layouts.admin')
@section('title', __('Imports'))
@section('content')

<div class="card mb-3">
    <div class="card-header">
        <h3 class="card-title">{{ __('New import') }}</h3>
    </div>
    <div class="card-body">
        <form method="POST" action="{{ route('admin.imports.upload') }}" enctype="multipart/form-data">
            @csrf
            <div class="row g-2">
                <div class="col-md-3">
                    <label for="type" class="form-label">{{ __('Type') }}</label>
                    <select name="type" id="type" class="form-select" required>
                        <option value="customers">{{ __('Customers') }}</option>
                        <option value="tickets">{{ __('Tickets') }}</option>
                    </select>
                </div>
                <div class="col-md-7">
                    <label for="file" class="form-label">{{ __('File (CSV/XLSX, max 10MB)') }}</label>
                    <input type="file" name="file" id="file" class="form-control" accept=".csv,.xlsx,.xls,.txt" required>
                </div>
                <div class="col-md-2 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary w-100">{{ __('Upload') }}</button>
                </div>
            </div>
        </form>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <h3 class="card-title">{{ __('Import history') }}</h3>
    </div>
    <div class="table-responsive">
        <table class="table table-vcenter card-table">
            <thead>
                <tr>
                    <th>{{ __('ID') }}</th>
                    <th>{{ __('Type') }}</th>
                    <th>{{ __('Filename') }}</th>
                    <th>{{ __('Total') }}</th>
                    <th>{{ __('Imported') }}</th>
                    <th>{{ __('Failed') }}</th>
                    <th>{{ __('Status') }}</th>
                    <th>{{ __('By') }}</th>
                    <th class="text-end">{{ __('Actions') }}</th>
                </tr>
            </thead>
            <tbody>
                @forelse($logs ?? [] as $log)
                <tr>
                    <td>{{ $log->id }}</td>
                    <td class="text-muted">{{ $log->type }}</td>
                    <td class="text-muted">{{ basename($log->filename) }}</td>
                    <td class="text-muted">{{ $log->total_rows }}</td>
                    <td class="text-muted">{{ $log->imported }}</td>
                    <td class="text-muted">{{ $log->failed }}</td>
                    <td><span class="badge bg-secondary">{{ $log->status }}</span></td>
                    <td class="text-muted">{{ $log->user->name ?? '—' }}</td>
                    <td class="text-end">
                        <a href="{{ route('admin.imports.show', $log) }}" class="btn btn-sm">{{ __('View') }}</a>
                    </td>
                </tr>
                @empty
                <tr><td colspan="9"><div class="empty"><p class="empty-title">{{ __('No imports yet.') }}</p></div></td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if(isset($logs) && method_exists($logs, 'links'))
    <div class="card-footer d-flex align-items-center justify-content-center">
        {{ $logs->links() }}
    </div>
    @endif
</div>

@endsection
