@extends('layouts.admin')
@section('title', __('Import result'))
@section('content')

<div class="card mb-3">
    <div class="card-header">
        <h3 class="card-title">{{ __('Import #:id — :type', ['id' => $log->id, 'type' => $log->type]) }}</h3>
    </div>
    <div class="card-body">
        <div class="row g-2">
            <div class="col-md-3"><div class="text-muted">{{ __('Filename') }}</div><strong>{{ basename($log->filename) }}</strong></div>
            <div class="col-md-2"><div class="text-muted">{{ __('Total rows') }}</div><strong>{{ $log->total_rows }}</strong></div>
            <div class="col-md-2"><div class="text-muted">{{ __('Imported') }}</div><strong>{{ $log->imported }}</strong></div>
            <div class="col-md-2"><div class="text-muted">{{ __('Failed') }}</div><strong>{{ $log->failed }}</strong></div>
            <div class="col-md-3"><div class="text-muted">{{ __('Status') }}</div><span class="badge bg-secondary">{{ $log->status }}</span></div>
        </div>
        @if(!empty($isDryRun))
        <div class="alert alert-info mt-3">{{ __('This was a dry-run. No records were saved.') }}</div>
        @endif
    </div>
</div>

@if(!empty($dryRun))
<div class="card mb-3">
    <div class="card-header">
        <h3 class="card-title">{{ __('Dry-run result: :valid valid, :invalid invalid of :total rows', ['valid' => $dryRun['valid'], 'invalid' => $dryRun['invalid'], 'total' => $dryRun['total']]) }}</h3>
    </div>
    <div class="table-responsive">
        <table class="table table-vcenter card-table">
            <thead>
                <tr>
                    <th>{{ __('Row') }}</th>
                    <th>{{ __('Errors') }}</th>
                </tr>
            </thead>
            <tbody>
                @forelse($dryRun['errors'] as $error)
                <tr>
                    <td>{{ $error['row'] }}</td>
                    <td class="text-muted">{{ implode(' ', $error['errors']) }}</td>
                </tr>
                @empty
                <tr><td colspan="2"><div class="empty"><p class="empty-title">{{ __('All rows are valid.') }}</p></div></td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endif

@if(!empty($log->errors))
<div class="card">
    <div class="card-header">
        <h3 class="card-title">{{ __('Row errors') }}</h3>
    </div>
    <div class="table-responsive">
        <table class="table table-vcenter card-table">
            <thead>
                <tr>
                    <th>{{ __('Row') }}</th>
                    <th>{{ __('Errors') }}</th>
                </tr>
            </thead>
            <tbody>
                @foreach($log->errors as $error)
                <tr>
                    <td>{{ $error['row'] ?? '—' }}</td>
                    <td class="text-muted">{{ implode(' ', (array) ($error['errors'] ?? [])) }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endif

<a href="{{ route('admin.imports.index') }}" class="btn btn-link px-0 mt-3">&larr; {{ __('Back to imports') }}</a>

@endsection
