<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ __('Forbidden') }} — {{ config('app.name', 'HelpDesk AI') }}</title>
    @vite(['resources/css/app.css'])
</head>
<body class="d-flex flex-column border-top-wide border-primary">
    <div class="page page-center">
        <div class="container-tight py-4">
            <div class="empty">
                <div class="empty-header">403</div>
                <p class="empty-title">{{ __('Access forbidden') }}</p>
                <p class="empty-subtitle text-muted">
                    {{ $exception->getMessage() ?: __('You do not have permission to access this resource.') }}
                </p>
                <div class="empty-action">
                    <a href="{{ url('/') }}" class="btn btn-primary">
                        {{ __('Take me home') }}
                    </a>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
