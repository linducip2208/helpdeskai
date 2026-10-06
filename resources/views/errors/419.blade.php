<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ __('Page expired') }} — {{ config('app.name', 'HelpDesk AI') }}</title>
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
                <div class="empty-header">419</div>
                <p class="empty-title">{{ __('Page expired') }}</p>
                <p class="empty-subtitle text-muted">
                    {{ __('Your session has expired. Please refresh the page and try again.') }}
                </p>
                <div class="empty-action">
                    <a href="{{ url()->previous() }}" class="btn btn-primary">
                        {{ __('Go back and retry') }}
                    </a>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
