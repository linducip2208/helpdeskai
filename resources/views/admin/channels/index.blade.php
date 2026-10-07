@extends('layouts.admin')
@section('title', __('Channels'))
@section('content')

<div class="row row-cards">
    <div class="col-lg-6">
        <div class="card">
            <div class="card-header"><h3 class="card-title">{{ __('Channel Status') }}</h3></div>
            <div class="table-responsive"><table class="table table-vcenter card-table">
                <thead><tr><th>{{ __('Channel') }}</th><th>{{ __('Status') }}</th></tr></thead>
                <tbody>
                    @foreach($channels ?? [] as $name => $info)
                    <tr>
                        <td><strong>{{ ucfirst($name) }}</strong></td>
                        <td>
                            @if($info['enabled'])
                            <span class="badge bg-green-lt">{{ __('Configured') }}</span>
                            @else
                            <span class="badge bg-secondary">{{ __('Not configured') }}</span>
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table></div>
            <div class="card-body">
                <p class="text-muted small mb-0">{{ __('Credentials live in .env (WHATSAPP_* / TELEGRAM_*), never in the database. See deployment docs.') }}</p>
            </div>
        </div>
    </div>
    <div class="col-lg-6">
        <div class="card mb-3">
            <div class="card-header"><h3 class="card-title">{{ __('Inbound Webhook URLs') }}</h3></div>
            <div class="card-body">
                <div class="mb-2"><span class="text-muted small">WhatsApp verify + inbound</span><div class="font-monospace small text-break">{{ $webhookUrls['whatsapp'] ?? '' }}</div></div>
                <div><span class="text-muted small">Telegram inbound</span><div class="font-monospace small text-break">{{ $webhookUrls['telegram'] ?? '' }}</div></div>
            </div>
        </div>
        <div class="card">
            <div class="card-header"><h3 class="card-title">{{ __('Send Test Message') }}</h3></div>
            <div class="card-body">
                <form action="{{ route('admin.channels.test') }}" method="POST">
                    @csrf
                    <div class="row g-2">
                        <div class="col-md-6">
                            <label class="form-label">{{ __('Channel') }}</label>
                            <select name="channel" class="form-select">
                                <option value="whatsapp">WhatsApp</option>
                                <option value="telegram">Telegram</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">{{ __('Recipient ID') }}</label>
                            <input type="text" name="recipient" class="form-control" placeholder="62812… / 123456" required>
                        </div>
                    </div>
                    <div class="mb-3 mt-3">
                        <label class="form-label">{{ __('Message') }}</label>
                        <textarea name="message" rows="3" maxlength="500" class="form-control" required>Hello from HelpDesk AI</textarea>
                    </div>
                    <button type="submit" class="btn btn-primary">{{ __('Send Test') }}</button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
