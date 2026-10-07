@extends('layouts.admin')
@section('title', __('Teams'))
@section('page-actions')
    <a href="{{ route('admin.teams.create') }}" class="btn btn-primary">{{ __('New Team') }}</a>
@endsection
@section('content')
<div class="card">
    <div class="table-responsive">
        <table class="table table-vcenter card-table">
            <thead>
                <tr>
                    <th>{{ __('Name') }}</th>
                    <th>{{ __('Members') }}</th>
                    <th>{{ __('Status') }}</th>
                    <th class="text-end">{{ __('Actions') }}</th>
                </tr>
            </thead>
            <tbody>
                @forelse($teams as $team)
                    <tr>
                        <td>{{ $team->name }}</td>
                        <td><span class="badge bg-blue-lt">{{ $team->members_count }}</span></td>
                        <td>
                            @if($team->is_active)
                                <span class="badge bg-green-lt">{{ __('Active') }}</span>
                            @else
                                <span class="badge bg-secondary-lt">{{ __('Inactive') }}</span>
                            @endif
                        </td>
                        <td class="text-end">
                            <a href="{{ route('admin.teams.edit', $team) }}" class="btn btn-sm">{{ __('Edit') }}</a>
                            <form action="{{ route('admin.teams.destroy', $team) }}" method="POST" class="d-inline" onsubmit="return confirm('{{ __('Delete?') }}')">
                                @csrf @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-danger">{{ __('Delete') }}</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="4"><div class="empty"><p class="empty-title">{{ __('No teams found.') }}</p></div></td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if(method_exists($teams, 'links'))
        <div class="card-footer">{{ $teams->links() }}</div>
    @endif
</div>
@endsection
