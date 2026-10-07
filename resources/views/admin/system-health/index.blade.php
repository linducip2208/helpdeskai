@extends('layouts.admin')
@section('title', __('System Health'))
@section('content')

<div class="card">
    <div class="card-header"><h3 class="card-title">{{ __('System Health') }}</h3></div>
    <div class="table-responsive"><table class="table table-vcenter card-table">
        <thead><tr><th>{{ __('Check') }}</th><th>{{ __('Value') }}</th><th>{{ __('Status') }}</th><th>{{ __('Note') }}</th></tr></thead>
        <tbody>
            @foreach($checks ?? [] as $check)
            <tr>
                <td><strong>{{ $check['label'] }}</strong></td>
                <td class="text-muted font-monospace small">{{ $check['value'] }}</td>
                <td>
                    @if($check['level'] === 'info')
                    <span class="badge bg-blue-lt">info</span>
                    @elseif($check['ok'])
                    <span class="badge bg-green-lt">OK</span>
                    @else
                    <span class="badge bg-red-lt">ERROR</span>
                    @endif
                </td>
                <td class="text-muted small">{{ $check['hint'] }}</td>
            </tr>
            @endforeach
        </tbody>
    </table></div>
</div>
@endsection
