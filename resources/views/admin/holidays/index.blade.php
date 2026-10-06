@extends('layouts.admin')
@section('title', __('Holidays'))
@section('content')

<div class="row">
    <div class="col-12 col-lg-8">
        <div class="card">
            <div class="card-header"><h3 class="card-title">{{ __('Holidays') }}</h3></div>
            <div class="table-responsive"><table class="table table-vcenter card-table">
                <thead><tr><th>{{ __('Date') }}</th><th>{{ __('Name') }}</th><th class="text-end">{{ __('Actions') }}</th></tr></thead>
                <tbody>
                    @forelse($holidays ?? [] as $holiday)
                    <tr>
                        <td class="text-muted">{{ wib($holiday->date, 'd F Y', false) }}</td>
                        <td>{{ $holiday->name }}</td>
                        <td class="text-end">
                            <form action="{{ route('admin.holidays.destroy', $holiday) }}" method="POST" class="d-inline" onsubmit="return confirm('Remove this holiday?')">
                                @csrf @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-danger">{{ __('Delete') }}</button>
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="3"><div class="empty"><p class="empty-title">{{ __('No holidays yet.') }}</p><p class="empty-subtitle text-muted">{{ __('SLA deadlines skip these dates.') }}</p></div></td></tr>
                    @endforelse
                </tbody>
            </table></div>
            <div class="card-footer d-flex align-items-center justify-content-center">
                {{ ($holidays ?? collect())->links() }}
            </div>
        </div>
    </div>
    <div class="col-12 col-lg-4">
        <div class="card">
            <div class="card-header"><h3 class="card-title">{{ __('Add Holiday') }}</h3></div>
            <div class="card-body">
                <form action="{{ route('admin.holidays.store') }}" method="POST">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label" for="date">{{ __('Date') }}</label>
                        <input type="date" name="date" id="date" value="{{ old('date') }}" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label" for="name">{{ __('Name') }}</label>
                        <input type="text" name="name" id="name" value="{{ old('name') }}" class="form-control" placeholder="Idul Fitri" required>
                    </div>
                    <button type="submit" class="btn btn-primary w-100">{{ __('Add Holiday') }}</button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
