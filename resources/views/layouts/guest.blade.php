<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'HelpDesk AI') }}</title>

        <link rel="manifest" href="/manifest.json">
        <meta name="theme-color" content="#066fd1">
        <link rel="icon" type="image/svg+xml" href="/icons/icon-192.svg">

        <script>
            try {
                document.documentElement.setAttribute('data-bs-theme', localStorage.getItem('helpdeskai-theme') || 'light');
            } catch (e) {}
        </script>
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="d-flex flex-column">
        <div class="position-absolute top-0 end-0 p-3">
            <button type="button" onclick="toggleTheme()" class="btn btn-ghost-secondary btn-icon" title="{{ __('Toggle dark mode') }}" aria-label="{{ __('Toggle dark mode') }}">
                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M21 12.79A9 9 0 1111.21 3 7 7 0 0021 12.79z"/></svg>
            </button>
        </div>
        <div class="page page-center">
            <div class="container container-tight py-4">
                <div class="text-center mb-4">
                    <a href="/" class="navbar-brand navbar-brand-autodark d-inline-flex align-items-center gap-2">
                        <x-application-logo width="40" height="40" />
                        <span class="fw-bold fs-2">{{ config('app.name', 'HelpDesk AI') }}</span>
                    </a>
                </div>
                {{ $slot }}
            </div>
        </div>
    </body>
</html>
