<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ __('Server error') }} — {{ config('app.name', 'HelpDesk AI') }}</title>
    <script>
        try {
            document.documentElement.setAttribute('data-bs-theme', localStorage.getItem('helpdeskai-theme') || 'light');
        } catch (e) {}
    </script>
    @vite(['resources/css/app.css'])
</head>
<body class="d-flex flex-column border-top-wide border-primary">
    <div class="page page-center">
        <div class="container-tight py-4">
            <div class="empty">
                <div class="empty-header">500</div>
                <p class="empty-title">{{ __('Something went wrong') }}</p>
                <p class="empty-subtitle text-muted">
                    {{ __('An unexpected error occurred. Our team has been notified. Please try again later.') }}
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
