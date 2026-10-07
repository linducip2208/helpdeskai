@extends('layouts.admin')
@section('title', __('Search'))
@section('content')

<div class="card mb-3">
    <div class="card-body">
        <form method="GET" action="{{ route('admin.search.index') }}">
            <div class="input-group input-group-lg">
                <input type="text" name="q" value="{{ $query ?? '' }}" class="form-control" placeholder="{{ __('Search tickets, customers, articles…') }}" minlength="2" required>
                <button type="submit" class="btn btn-primary">{{ __('Search') }}</button>
            </div>
        </form>
    </div>
</div>

@if(($query ?? '') !== '')
<div class="row row-cards">
    <div class="col-lg-6">
        <div class="card">
            <div class="card-header"><h3 class="card-title">{{ __('Tickets') }}</h3></div>
            <div class="list-group list-group-flush">
                @forelse($results['tickets'] ?? [] as $ticket)
                <a href="{{ route('admin.tickets.show', $ticket) }}" class="list-group-item">
                    <div class="row align-items-center">
                        <div class="col text-truncate"><strong>{{ $ticket->uid }}</strong> — {{ $ticket->subject }}</div>
                        <div class="col-auto text-muted small">{{ $ticket->department->name ?? '' }}</div>
                    </div>
                </a>
                @empty
                <div class="list-group-item text-muted">{{ __('No tickets found.') }}</div>
                @endforelse
            </div>
        </div>
    </div>
    <div class="col-lg-6">
        <div class="card mb-3">
            <div class="card-header"><h3 class="card-title">{{ __('Customers') }}</h3></div>
            <div class="list-group list-group-flush">
                @forelse($results['users'] ?? [] as $user)
                <div class="list-group-item">
                    <div><strong>{{ $user->name }}</strong></div>
                    <div class="text-muted small">{{ $user->email }}</div>
                </div>
                @empty
                <div class="list-group-item text-muted">{{ __('No customers found.') }}</div>
                @endforelse
            </div>
        </div>
        <div class="card">
            <div class="card-header"><h3 class="card-title">{{ __('Knowledge Articles') }}</h3></div>
            <div class="list-group list-group-flush">
                @forelse($results['articles'] ?? [] as $article)
                <div class="list-group-item">
                    <div><strong>{{ $article->title }}</strong></div>
                    <div class="text-muted small">{{ $article->status }}</div>
                </div>
                @empty
                <div class="list-group-item text-muted">{{ __('No articles found.') }}</div>
                @endforelse
            </div>
        </div>
    </div>
</div>
@endif
@endsection
