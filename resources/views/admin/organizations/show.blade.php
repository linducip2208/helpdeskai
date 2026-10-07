@extends('layouts.admin')
@section('title', $organization->name)
@section('page-actions')
    <a href="{{ route('admin.organizations.edit', $organization) }}" class="btn btn-primary">{{ __('Edit') }}</a>
@endsection
@section('content')
<div class="card mb-3">
    <div class="card-body">
        <dl class="row mb-0">
            <dt class="col-3">{{ __('Name') }}</dt><dd class="col-9">{{ $organization->name }}</dd>
            <dt class="col-3">{{ __('Domain') }}</dt><dd class="col-9">{{ $organization->domain ?? '—' }}</dd>
            <dt class="col-3">{{ __('Email') }}</dt><dd class="col-9">{{ $organization->email ?? '—' }}</dd>
            <dt class="col-3">{{ __('Phone') }}</dt><dd class="col-9">{{ $organization->phone ?? '—' }}</dd>
            <dt class="col-3">{{ __('Notes') }}</dt><dd class="col-9">{{ $organization->notes ?? '—' }}</dd>
        </dl>
    </div>
</div>
<div class="card">
    <div class="card-header"><h3 class="card-title">{{ __('Users') }}</h3></div>
    <div class="table-responsive">
        <table class="table table-vcenter card-table">
            <thead>
                <tr>
                    <th>{{ __('Name') }}</th>
                    <th>{{ __('Email') }}</th>
                    <th>{{ __('VIP') }}</th>
                </tr>
            </thead>
            <tbody>
                @forelse($users as $user)
                    <tr>
                        <td>{{ $user->name }}</td>
                        <td class="text-muted">{{ $user->email }}</td>
                        <td>@if($user->vip)<span class="badge bg-yellow-lt">{{ __('VIP') }}</span>@else<span class="text-muted">—</span>@endif</td>
                    </tr>
                @empty
                    <tr><td colspan="3"><div class="empty"><p class="empty-title">{{ __('No users in this organization.') }}</p></div></td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if(method_exists($users, 'links'))
        <div class="card-footer">{{ $users->links() }}</div>
    @endif
</div>
@endsection
