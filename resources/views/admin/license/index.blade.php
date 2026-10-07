@extends('layouts.admin')
@section('title', 'License Status')
@section('content')

<div class="row justify-content-center">
    <div class="col-12 col-lg-10">
        <p class="text-muted mb-3">Commercial Proprietary Software — status lisensi untuk domain ini.</p>

        <div class="card mb-3">
            <div class="card-body">
                <div class="d-flex align-items-center gap-2 mb-3 flex-wrap">
                    @if($paired)
                        <span class="badge bg-green-lt">Paired &amp; Valid</span>
                    @else
                        <span class="badge bg-red-lt">Not Paired / Invalid</span>
                    @endif
                    <span><code>{{ $domain }}</code></span>
                    <span class="ms-auto d-flex gap-2">
                        @if($paired)
                        <form action="{{ route('admin.license.refresh') }}" method="POST" class="d-inline">
                            @csrf
                            <button type="submit" class="btn btn-sm">{{ __('Validate now') }}</button>
                        </form>
                        <form action="{{ route('admin.license.deactivate') }}" method="POST" class="d-inline" onsubmit="return confirm('Deactivate the license on this installation?')">
                            @csrf
                            <button type="submit" class="btn btn-sm btn-danger">{{ __('Deactivate') }}</button>
                        </form>
                        @endif
                    </span>
                </div>

                @if($paired && $data)
                    <div class="row g-2 border-top pt-3">
                        <div class="col-md-6">
                            <div class="text-muted">Product</div>
                            <div>{{ $data['product'] ?? $data['product_name'] ?? 'HelpdeskAI' }}</div>
                        </div>
                        <div class="col-md-6">
                            <div class="text-muted">Plan / Tier</div>
                            <div>{{ $data['plan'] ?? $data['tier'] ?? '—' }}</div>
                        </div>
                        <div class="col-md-6">
                            <div class="text-muted">Activated</div>
                            <div>{{ $data['activated_at'] ?? '—' }}</div>
                        </div>
                        <div class="col-md-6">
                            <div class="text-muted">Expires</div>
                            <div>{{ $data['expires_at'] ?? 'Lifetime' }}</div>
                        </div>
                        <div class="col-md-6">
                            <div class="text-muted">Last validation</div>
                            <div>{{ $lastValidatedAt ? wib($lastValidatedAt) : '—' }}</div>
                        </div>
                        <div class="col-md-6">
                            <div class="text-muted">Support entitlement</div>
                            <div>{{ $data['support_until'] ?? $data['support'] ?? 'Sesuai paket pembelian' }}</div>
                        </div>
                        @if(! empty($data['licensee_email']))
                            <div class="col-md-6">
                                <div class="text-muted">Licensee</div>
                                <div>{{ $data['licensee_email'] }}</div>
                            </div>
                        @endif
                        @if(! empty($data['activation_key']))
                            <div class="col-md-6">
                                <div class="text-muted">Activation Key</div>
                                <div><code>{{ \Illuminate\Support\Str::mask($data['activation_key'], '*', 4, -4) }}</code></div>
                            </div>
                        @endif
                    </div>
                @else
                    <div class="border-top pt-3">
                        <div class="alert alert-warning" role="alert">
                            <div><strong>{{ __('Commercial License Required') }}</strong></div>
                            <div>{{ __('This installation is not licensed. Pair it or contact sales:') }} <strong>{{ config('helpdesk.sales_contact') }}</strong></div>
                        </div>
                        <p class="text-muted">License lock file missing, expired, atau invalid untuk domain <code>{{ $domain }}</code>.</p>
                        <a href="{{ url('/__pair') }}" class="btn btn-primary">Open Pairing Wizard &rarr;</a>
                    </div>
                @endif
            </div>
        </div>

        <div class="card">
            <div class="card-body">
                <h3 class="card-title">{{ __('Commercial purchase') }}</h3>
                <p class="text-muted mb-1">{{ __('Untuk pembelian lisensi dan informasi komersial:') }} <strong>{{ config('helpdesk.sales_contact') }}</strong></p>
                <p class="text-muted small mb-0">Lock file di <code>.license.lock</code> (encrypted AES-256-GCM + RSA-signed payload). Heartbeat ke marketplace tiap 24 jam dengan grace 7 hari kalau marketplace offline. Marketplace: <a href="{{ $marketplaceUrl }}" target="_blank" rel="noopener">{{ $marketplaceUrl ?: 'belum diset di .env' }}</a></p>
            </div>
        </div>
    </div>
</div>

@endsection
