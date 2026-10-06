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
        <link rel="apple-touch-icon" href="/icons/icon-192.svg">
        <link rel="alternate" type="application/rss+xml" title="{{ config('app.name', 'HelpDesk AI') }} Blog" href="{{ url('/blog/feed.xml') }}">

        <script>
            try {
                document.documentElement.setAttribute('data-bs-theme', localStorage.getItem('helpdeskai-theme') || 'light');
            } catch (e) {}
        </script>
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body>
        <div class="page">
            @include('layouts.navigation')

            <div class="page-wrapper">
                @isset($header)
                    <div class="page-header d-print-none">
                        <div class="container-xl">
                            <div class="row g-2 align-items-center">
                                <div class="col">{{ $header }}</div>
                            </div>
                        </div>
                    </div>
                @endisset

                <div class="page-body">
                    <div class="container-xl">
                        @if(session('success'))
                            <div class="alert alert-success alert-dismissible" role="alert">
                                <div>{{ session('success') }}</div>
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="{{ __('Close') }}"></button>
                            </div>
                        @endif
                        @if(session('error'))
                            <div class="alert alert-danger alert-dismissible" role="alert">
                                <div>{{ session('error') }}</div>
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="{{ __('Close') }}"></button>
                            </div>
                        @endif
                        {{ $slot }}
                    </div>
                </div>

                <footer class="footer footer-transparent d-print-none">
                    <div class="container-xl">
                        <div class="row text-center align-items-center flex-row-reverse">
                            <div class="col-12 col-lg-auto mt-3 mt-lg-0">
                                <ul class="list-inline list-inline-dots mb-0">
                                    <li class="list-inline-item">{{ config('app.name', 'HelpDesk AI') }}</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </footer>
            </div>
        </div>
        <script>
            if ('serviceWorker' in navigator) {
                window.addEventListener('load', () => navigator.serviceWorker.register('/sw.js').catch(() => {}));
            }
        </script>
    </body>
</html>
