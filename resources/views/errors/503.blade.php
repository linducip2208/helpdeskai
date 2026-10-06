<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ __('Service unavailable') }} — {{ config('app.name', 'HelpDesk AI') }}</title>
    @vite(['resources/css/app.css'])
</head>
<body class="d-flex flex-column border-top-wide border-primary">
    <div class="page page-center">
        <div class="container-tight py-4">
            <div class="empty">
                <div class="empty-header">503</div>
                <p class="empty-title">{{ __('Service unavailable') }}</p>
                <p class="empty-subtitle text-muted">
                    {{ __('We are performing scheduled maintenance. Please check back soon.') }}
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
