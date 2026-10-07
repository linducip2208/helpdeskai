@extends('layouts.admin')
@section('title', __('Import preview'))
@section('content')

<div class="card mb-3">
    <div class="card-header">
        <h3 class="card-title">{{ __('Preview rows') }} ({{ $log->type }} — {{ basename($log->filename) }})</h3>
    </div>
    <div class="table-responsive">
        <table class="table table-vcenter card-table">
            <thead>
                <tr>
                    @foreach($headers ?? [] as $header)
                    <th>{{ $header }}</th>
                    @endforeach
                </tr>
            </thead>
            <tbody>
                @forelse($preview ?? [] as $row)
                <tr>
                    @foreach($headers as $header)
                    <td class="text-muted">{{ $row[$header] ?? '—' }}</td>
                    @endforeach
                </tr>
                @empty
                <tr><td colspan="{{ max(1, count($headers ?? [])) }}"><div class="empty"><p class="empty-title">{{ __('No rows found in file.') }}</p></div></td></tr>
                @endforelse
            </tbody>
        </table>
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

<div class="card">
    <div class="card-header">
        <h3 class="card-title">{{ __('Column mapping & confirm') }}</h3>
    </div>
    <div class="card-body">
        <form method="POST" action="{{ route('admin.imports.confirm', $log) }}">
            @csrf
            <div class="row g-2">
                @foreach($fields ?? [] as $field)
                <div class="col-md-4">
                    <label class="form-label">{{ $field }}</label>
                    <select name="mapping[{{ $field }}]" class="form-select">
                        <option value="">{{ __('— ignore —') }}</option>
                        @foreach($headers ?? [] as $header)
                        <option value="{{ $header }}" {{ ($mapping[$field] ?? '') === $header ? 'selected' : '' }}>{{ $header }}</option>
                        @endforeach
                    </select>
                </div>
                @endforeach
            </div>
            <div class="mt-3">
                <label class="form-check">
                    <input type="checkbox" name="dry_run" value="1" class="form-check-input" {{ !empty($dryRun) ? '' : 'checked' }}>
                    <span class="form-check-label">{{ __('Dry-run only (validate without saving)') }}</span>
                </label>
            </div>
            <div class="form-footer d-flex justify-content-end gap-2">
                <a href="{{ route('admin.imports.index') }}" class="btn">{{ __('Cancel') }}</a>
                <button type="submit" class="btn btn-primary">{{ __('Confirm') }}</button>
            </div>
        </form>
    </div>
</div>

@endsection
