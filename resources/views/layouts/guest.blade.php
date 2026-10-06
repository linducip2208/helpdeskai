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

        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="d-flex flex-column">
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
