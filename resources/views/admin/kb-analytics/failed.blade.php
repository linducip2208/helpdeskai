@extends('layouts.admin')
@section('title', __('Failed searches'))
@section('content')

<div class="card mb-3">
    <div class="card-header">
        <h3 class="card-title">{{ __('Top failed queries') }}</h3>
    </div>
    <div class="table-responsive">
        <table class="table table-vcenter card-table">
            <thead>
                <tr>
                    <th>{{ __('Query') }}</th>
                    <th>{{ __('Hits') }}</th>
                </tr>
            </thead>
            <tbody>
                @forelse($topQueries ?? [] as $row)
                <tr>
                    <td>{{ $row['query'] }}</td>
                    <td class="text-muted">{{ $row['hits'] }}</td>
                </tr>
                @empty
                <tr><td colspan="2"><div class="empty"><p class="empty-title">{{ __('No failed searches.') }}</p></div></td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <h3 class="card-title">{{ __('Failed searches') }}</h3>
    </div>
    <div class="table-responsive">
        <table class="table table-vcenter card-table">
            <thead>
                <tr>
                    <th>{{ __('Query') }}</th>
                    <th>{{ __('Language') }}</th>
                    <th>{{ __('User') }}</th>
                    <th>{{ __('Searched at') }}</th>
                </tr>
            </thead>
            <tbody>
                @forelse($searches ?? [] as $search)
                <tr>
                    <td>{{ $search->query }}</td>
                    <td class="text-muted">{{ $search->language ?? '—' }}</td>
                    <td class="text-muted">{{ $search->user->name ?? __('Guest') }}</td>
                    <td class="text-muted">{{ $search->created_at }}</td>
                </tr>
                @empty
                <tr><td colspan="4"><div class="empty"><p class="empty-title">{{ __('No failed searches.') }}</p></div></td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if(isset($searches) && method_exists($searches, 'links'))
    <div class="card-footer d-flex align-items-center justify-content-center">
        {{ $searches->links() }}
    </div>
    @endif
</div>

@endsection
