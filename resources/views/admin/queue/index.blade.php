@extends('layouts.admin')
@section('title', __('Queue'))
@section('content')

<div class="row row-cards">
    <div class="col-lg-5">
        <div class="card">
            <div class="card-header"><h3 class="card-title">{{ __('Driver') }}: {{ $driver }}</h3></div>
            <div class="card-body">
                @if($driver !== 'database')
                <div class="alert alert-info" role="alert">
                    <div>{{ __('Queue dashboard reads the database driver. Current driver: :driver', ['driver' => $driver]) }}</div>
                </div>
                @endif
                <div class="subheader mb-2">{{ __('Pending jobs by queue') }}</div>
                @if($pending === null)
                <p class="text-muted">{{ __('No database queue table.') }}</p>
                @elseif($pending->isEmpty())
                <p class="text-muted">{{ __('Queue is empty.') }}</p>
                @else
                <div class="table-responsive"><table class="table table-vcenter">
                    <thead><tr><th>{{ __('Queue') }}</th><th class="text-end">{{ __('Pending') }}</th></tr></thead>
                    <tbody>
                        @foreach($pending as $row)
                        <tr><td class="font-monospace">{{ $row->queue }}</td><td class="text-end">{{ $row->total }}</td></tr>
                        @endforeach
                    </tbody>
                </table></div>
                @endif
                @if($failedCount !== null)
                <div class="d-flex justify-content-between mt-3">
                    <span class="text-muted">{{ __('Failed jobs') }}</span>
                    <strong>{{ $failedCount }}</strong>
                </div>
                @endif
            </div>
        </div>
    </div>
    <div class="col-lg-7">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">{{ __('Recent Failed Jobs') }}</h3>
                <div class="card-actions">
                    @if(($failedCount ?? 0) > 0)
                    <form action="{{ route('admin.queue.flush') }}" method="POST" class="d-inline" onsubmit="return confirm('Delete ALL failed jobs?')">
                        @csrf
                        <button type="submit" class="btn btn-sm btn-danger">{{ __('Flush all') }}</button>
                    </form>
                    @endif
                </div>
            </div>
            <div class="table-responsive"><table class="table table-vcenter card-table">
                <thead><tr><th>{{ __('Failed at') }}</th><th>{{ __('Job') }}</th><th>{{ __('Error') }}</th><th class="text-end">{{ __('Actions') }}</th></tr></thead>
                <tbody>
                    @forelse($failed ?? [] as $job)
                    <tr>
                        <td class="text-muted small">{{ $job->failed_at }}</td>
                        <td class="font-monospace small">{{ \Illuminate\Support\Str::limit($job->uuid, 13) }}</td>
                        <td class="text-muted small">{{ \Illuminate\Support\Str::limit($job->exception, 120) }}</td>
                        <td class="text-end text-nowrap">
                            <form action="{{ route('admin.queue.retry', $job->uuid) }}" method="POST" class="d-inline">
                                @csrf
                                <button type="submit" class="btn btn-sm">{{ __('Retry') }}</button>
                            </form>
                            <form action="{{ route('admin.queue.forget', $job->uuid) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete this failed job?')">
                                @csrf @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-danger">{{ __('Delete') }}</button>
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="4"><div class="empty"><p class="empty-title">{{ __('No failed jobs.') }}</p></div></td></tr>
                    @endforelse
                </tbody>
            </table></div>
        </div>
    </div>
</div>
@endsection
