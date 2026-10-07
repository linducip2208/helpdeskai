@extends('layouts.admin')
@section('title', __('Automation Runs'))
@section('content')

<div class="card mb-3">
    <div class="card-body">
        <form method="GET">
            <div class="row g-2 align-items-end">
                <div class="col-md-4">
                    <label class="form-label">{{ __('Rule') }}</label>
                    <select name="rule_id" class="form-select">
                        <option value="">{{ __('All rules') }}</option>
                        @foreach($rules ?? [] as $rule)
                        <option value="{{ $rule->id }}" {{ ($filters['rule_id'] ?? '') == $rule->id ? 'selected' : '' }}>{{ $rule->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label">{{ __('Status') }}</label>
                    <select name="status" class="form-select">
                        <option value="">{{ __('All statuses') }}</option>
                        @foreach(['pending', 'running', 'success', 'failed', 'skipped'] as $status)
                        <option value="{{ $status }}" {{ ($filters['status'] ?? '') === $status ? 'selected' : '' }}>{{ ucfirst($status) }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4">
                    <button type="submit" class="btn btn-primary">{{ __('Apply') }}</button>
                </div>
            </div>
        </form>
    </div>
</div>

<div class="card">
    <div class="table-responsive"><table class="table table-vcenter card-table">
        <thead><tr><th>{{ __('Rule') }}</th><th>{{ __('Ticket') }}</th><th>{{ __('Trigger') }}</th><th>{{ __('Status') }}</th><th>{{ __('Actions') }}</th><th>{{ __('Duration') }}</th><th>{{ __('At') }}</th></tr></thead>
        <tbody>
            @forelse($runs ?? [] as $run)
            <tr>
                <td>{{ $run->rule->name ?? '#'.$run->rule_id }}</td>
                <td>
                    @if($run->ticket)
                    <a href="{{ route('admin.tickets.show', $run->ticket) }}">{{ $run->ticket->uid }}</a>
                    @else
                    <span class="text-muted">—</span>
                    @endif
                </td>
                <td class="text-muted font-monospace small">{{ $run->trigger }}</td>
                <td>
                    @if($run->status === 'success')
                    <span class="badge bg-green-lt">{{ __('Success') }}</span>
                    @elseif($run->status === 'failed')
                    <span class="badge bg-red-lt" title="{{ $run->error }}">{{ __('Failed') }}</span>
                    @elseif($run->status === 'skipped')
                    <span class="badge bg-secondary">{{ __('Skipped') }}</span>
                    @else
                    <span class="badge bg-yellow-lt">{{ ucfirst($run->status) }}</span>
                    @endif
                </td>
                <td class="text-muted small">{{ is_array($run->actions_executed) ? implode(', ', $run->actions_executed) : '—' }}</td>
                <td class="text-muted">{{ $run->duration_ms !== null ? $run->duration_ms.'ms' : '—' }}</td>
                <td class="text-muted">{{ wib($run->created_at) }}</td>
            </tr>
            @empty
            <tr><td colspan="7"><div class="empty"><p class="empty-title">{{ __('No automation runs yet.') }}</p></div></td></tr>
            @endforelse
        </tbody>
    </table></div>
    <div class="card-footer d-flex align-items-center justify-content-center">
        {{ ($runs ?? collect())->links() }}
    </div>
</div>
@endsection
