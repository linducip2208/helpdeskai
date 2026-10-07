@extends('layouts.admin')
@section('title', __('Quality (QA)'))
@section('content')

@if(! ($qaEnabled ?? false))
<div class="alert alert-info" role="alert">
    <div>{{ __('AI reply scoring is disabled. Enable it in Settings → AI Policy to score agent replies automatically. Manual metrics below always work.') }}</div>
</div>
@endif

<div class="card mb-3">
    <div class="card-body">
        <form method="GET">
            <div class="row g-2 align-items-end">
                <div class="col-md-4">
                    <label class="form-label">{{ __('From') }}</label>
                    <input type="date" name="from" value="{{ $filters['from'] ?? '' }}" class="form-control">
                </div>
                <div class="col-md-4">
                    <label class="form-label">{{ __('To') }}</label>
                    <input type="date" name="to" value="{{ $filters['to'] ?? '' }}" class="form-control">
                </div>
                <div class="col-md-4">
                    <button type="submit" class="btn btn-primary">{{ __('Apply') }}</button>
                </div>
            </div>
        </form>
    </div>
</div>

<div class="card mb-3">
    <div class="card-header"><h3 class="card-title">{{ __('Agent Quality') }}</h3></div>
    <div class="table-responsive"><table class="table table-vcenter card-table">
        <thead><tr><th>{{ __('Agent') }}</th><th>{{ __('Assigned') }}</th><th>{{ __('Resolved') }}</th><th>{{ __('Reopened') }}</th><th>{{ __('CSAT') }}</th><th>{{ __('AI QA Avg') }}</th></tr></thead>
        <tbody>
            @forelse($agents ?? [] as $agent)
            <tr>
                <td><strong>{{ $agent->name }}</strong></td>
                <td class="text-muted">{{ $agent->assigned }}</td>
                <td class="text-muted">{{ $agent->resolved }}</td>
                <td class="text-muted">{{ ($reopened[$agent->id] ?? 0) }}</td>
                <td class="text-muted">{{ $agent->csat_avg ? round($agent->csat_avg, 1) : '—' }}</td>
                <td class="text-muted">{{ isset($qaAvg[$agent->id]) ? round($qaAvg[$agent->id], 2) : '—' }}</td>
            </tr>
            @empty
            <tr><td colspan="6"><div class="empty"><p class="empty-title">{{ __('No data available') }}</p></div></td></tr>
            @endforelse
        </tbody>
    </table></div>
</div>

<div class="card">
    <div class="card-header"><h3 class="card-title">{{ __('AI Reply Scores') }}</h3></div>
    <div class="table-responsive"><table class="table table-vcenter card-table">
        <thead><tr><th>{{ __('Ticket') }}</th><th>{{ __('Agent') }}</th><th>{{ __('Overall') }}</th><th>{{ __('Detail') }}</th><th>{{ __('Feedback') }}</th></tr></thead>
        <tbody>
            @forelse($scores ?? [] as $score)
            <tr>
                <td><a href="{{ route('admin.tickets.show', $score->ticket_id) }}">{{ $score->ticket->uid ?? '#'.$score->ticket_id }}</a></td>
                <td class="text-muted">{{ $score->reply->user->name ?? '—' }}</td>
                <td><span class="badge {{ ($score->overall ?? 0) >= 4 ? 'bg-green-lt' : (($score->overall ?? 0) >= 3 ? 'bg-yellow-lt' : 'bg-red-lt') }}">{{ $score->overall ?? '—' }}</span></td>
                <td class="text-muted small">A{{ $score->accuracy }}/C{{ $score->completeness }}/T{{ $score->tone }}/E{{ $score->empathy }}/P{{ $score->policy_compliance }}/K{{ $score->knowledge_correctness }}</td>
                <td class="text-muted small">{{ \Illuminate\Support\Str::limit($score->feedback, 80) }}</td>
            </tr>
            @empty
            <tr><td colspan="5"><div class="empty"><p class="empty-title">{{ __('No AI scores yet.') }}</p><p class="empty-subtitle text-muted">{{ __('Scores are advisory only, never punitive.') }}</p></div></td></tr>
            @endforelse
        </tbody>
    </table></div>
    <div class="card-footer d-flex align-items-center justify-content-center">
        {{ ($scores ?? collect())->links() }}
    </div>
</div>
@endsection
