<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', __('Dashboard')) — {{ config('app.name', 'HelpDesk AI') }}</title>
    <link rel="manifest" href="/manifest.json">
    <meta name="theme-color" content="#066fd1">
    <link rel="icon" type="image/svg+xml" href="/icons/icon-192.svg">
    <link rel="apple-touch-icon" href="/icons/icon-192.svg">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body>
<div class="page" x-data="{ mobileOpen: false, openDropdown: null }">

@php
    $sections = [
        'Overview'   => [
            ['route' => 'admin.dashboard',          'label' => __('Dashboard'),         'icon' => 'M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6'],
            ['route' => 'admin.analytics.index',    'label' => __('Analytics'),         'icon' => 'M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z'],
            ['route' => 'admin.ai-usage-logs.index','label' => __('AI Usage Logs'),     'icon' => 'M13 10V3L4 14h7v7l9-11h-7z'],
            ['route' => 'admin.activity-log.index', 'label' => __('Activity Log'),      'icon' => 'M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z'],
        ],
        'Support'    => [
            ['route' => 'admin.tickets.index',        'label' => __('Tickets'),         'icon' => 'M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z'],
            ['route' => 'admin.conversations.index',  'label' => __('Conversations'),   'icon' => 'M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z'],
            ['route' => 'admin.users.index',          'label' => __('Users'),           'icon' => 'M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z'],
            ['route' => 'admin.canned-responses.index','label' => __('Canned Responses'),'icon' => 'M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z'],
        ],
        'Content'    => [
            ['route' => 'admin.knowledge.index',            'label' => __('KB Articles'),  'icon' => 'M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253'],
            ['route' => 'admin.knowledge-categories.index', 'label' => __('KB Categories'),'icon' => 'M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10'],
            ['route' => 'admin.knowledge-faqs.index',       'label' => __('FAQs'),         'icon' => 'M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z'],
            ['route' => 'admin.posts.index',                'label' => __('Blog'),         'icon' => 'M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9a2 2 0 00-2-2h-2m-4-3H9M7 16h6M7 8h6v4H7V8z'],
            ['route' => 'admin.services.index',             'label' => __('Services'),     'icon' => 'M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z'],
            ['route' => 'admin.seo-meta.index',             'label' => __('SEO Meta'),     'icon' => 'M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z'],
        ],
        'AI'         => [
            ['route' => 'admin.ai-providers.index', 'label' => __('AI Providers'),  'icon' => 'M13 10V3L4 14h7v7l9-11h-7z'],
            ['route' => 'admin.ai-features.index',  'label' => __('AI Features'),   'icon' => 'M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z'],
        ],
        'Automation' => [
            ['route' => 'admin.sla-policies.index',    'label' => __('SLA Policies'),     'icon' => 'M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z'],
            ['route' => 'admin.automation-rules.index','label' => __('Automation Rules'), 'icon' => 'M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15'],
            ['route' => 'admin.email-logs.index',      'label' => __('Email Logs'),       'icon' => 'M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z'],
            ['route' => 'admin.push-subscriptions.index','label' => __('Push Subscribers'),'icon' => 'M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9'],
        ],
        'Settings'   => [
            ['route' => 'admin.settings.index',         'label' => __('General Settings'), 'icon' => 'M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.066 2.573c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.573 1.066c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.066-2.573c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z'],
            ['route' => 'admin.email-templates.index',  'label' => __('Email Templates'),  'icon' => 'M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z'],
            ['route' => 'admin.license.index',          'label' => __('License Status'),   'icon' => 'M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z'],
            ['route' => 'admin.api-keys.index',         'label' => __('API Keys'),         'icon' => 'M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z'],
            ['route' => 'admin.departments.index',      'label' => __('Departments'),      'icon' => 'M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4'],
            ['route' => 'admin.categories.index',       'label' => __('Categories'),       'icon' => 'M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z'],
        ],
        'Reports'    => [
            ['route' => 'admin.export.tickets', 'label' => __('Export Tickets CSV'), 'icon' => 'M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4', 'external' => true],
            ['route' => 'admin.export.agents',  'label' => __('Export Agents CSV'),  'icon' => 'M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4', 'external' => true],
        ],
    ];

    $isRouteActive = function ($route) {
        if (! \Illuminate\Support\Facades\Route::has($route)) {
            return false;
        }
        $base = preg_replace('/\.(index|show|edit|create|update|store|destroy)$/', '.*', $route);
        return request()->routeIs($base);
    };
@endphp

    {{-- Sidebar --}}
    <aside class="navbar navbar-vertical navbar-expand-lg" data-bs-theme="dark">
        <div class="container-fluid">
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#sidebar-menu" aria-controls="sidebar-menu" aria-expanded="false" aria-label="{{ __('Toggle navigation') }}">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="navbar-brand navbar-brand-autodark">
                <a href="{{ route('admin.dashboard') }}" class="d-flex align-items-center gap-2 text-decoration-none">
                    <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"/></svg>
                    <span class="fw-bold">{{ config('app.name', 'HelpDesk AI') }}</span>
                </a>
            </div>
            <div class="collapse navbar-collapse" id="sidebar-menu">
                <ul class="navbar-nav pt-lg-3">
                    @foreach($sections as $sectionName => $items)
                        <li class="nav-item mt-2">
                            <span class="nav-link text-uppercase text-muted small fw-bold">{{ $sectionName }}</span>
                        </li>
                        @foreach($items as $item)
                            @php
                                $itemActive = $isRouteActive($item['route']);
                                $href = \Illuminate\Support\Facades\Route::has($item['route']) ? route($item['route']) : '#';
                                $target = ($item['external'] ?? false) ? '_blank' : '_self';
                            @endphp
                            <li class="nav-item">
                                <a class="nav-link {{ $itemActive ? 'active' : '' }}" href="{{ $href }}" target="{{ $target }}" @if($itemActive) aria-current="page" @endif>
                                    <span class="nav-link-icon d-md-none d-lg-inline-block">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="{{ $item['icon'] }}"/></svg>
                                    </span>
                                    <span class="nav-link-title">{{ $item['label'] }}</span>
                                </a>
                            </li>
                        @endforeach
                    @endforeach
                </ul>
            </div>
        </div>
    </aside>

    <div class="page-wrapper">
        @if(session()->has('impersonator_id'))
            <div class="alert alert-warning alert-important rounded-0 mb-0" role="alert">
                <div class="d-flex align-items-center gap-2 container-xl">
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 9v4m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/></svg>
                    <span>{{ __('You are impersonating :name', ['name' => auth()->user()->name]) }}</span>
                    <form method="POST" action="{{ route('impersonation.stop') }}" class="ms-auto">
                        @csrf
                        <button type="submit" class="btn btn-sm btn-warning">{{ __('Stop impersonating') }}</button>
                    </form>
                </div>
            </div>
        @endif

        {{-- Topbar --}}
        <header class="navbar navbar-expand-md d-print-none">
            <div class="container-xl">
                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbar-menu" aria-controls="navbar-menu" aria-expanded="false" aria-label="{{ __('Toggle navigation') }}">
                    <span class="navbar-toggler-icon"></span>
                </button>
                <div class="navbar-nav flex-row order-md-last ms-auto">
                    <div class="nav-item d-none d-md-flex me-2">
                        <a href="{{ url('/') }}" target="_blank" class="btn btn-ghost-secondary" title="{{ __('View public site') }}">
                            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                            {{ __('View site') }}
                        </a>
                    </div>
                    {{-- Notifications --}}
                    <div class="nav-item dropdown" x-data="adminNotifBell()" x-init="fetchUnread()">
                        <a href="#" class="nav-link d-flex lh-1 text-reset p-0" data-bs-toggle="dropdown" aria-label="{{ __('Notifications') }}" @click="markRead()">
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/></svg>
                            <span x-show="count > 0" x-cloak class="badge bg-red" x-text="count > 9 ? '9+' : count"></span>
                        </a>
                        <div class="dropdown-menu dropdown-menu-arrow dropdown-menu-end dropdown-menu-card">
                            <div class="card">
                                <div class="card-header d-flex justify-content-between align-items-center">
                                    <h3 class="card-title">{{ __('Notifications') }}</h3>
                                    <a href="{{ route('admin.notifications.index') }}" class="small">{{ __('View all') }}</a>
                                </div>
                                <div class="list-group list-group-flush list-group-hoverable" style="max-height: 20rem; overflow-y: auto;">
                                    <template x-if="items.length === 0">
                                        <div class="list-group-item text-center text-muted py-4">{{ __('No new notifications') }}</div>
                                    </template>
                                    <template x-for="n in items" :key="n.id">
                                        <a :href="n.action_url || '#'" class="list-group-item">
                                            <div class="row align-items-center">
                                                <div class="col-auto"><span class="status-dot" :class="n.read_at ? 'bg-secondary' : 'bg-blue'"></span></div>
                                                <div class="col text-truncate">
                                                    <span class="d-block" x-text="n.title"></span>
                                                    <small class="d-block text-muted text-truncate" x-text="n.body"></small>
                                                    <small class="d-block text-muted" x-text="n.created_at"></small>
                                                </div>
                                            </div>
                                        </a>
                                    </template>
                                </div>
                            </div>
                        </div>
                    </div>
                    {{-- User --}}
                    <div class="nav-item dropdown">
                        <a href="#" class="nav-link d-flex lh-1 text-reset p-0" data-bs-toggle="dropdown" aria-label="{{ __('Open user menu') }}">
                            <span class="avatar avatar-sm">{{ strtoupper(substr(auth()->user()->name ?? 'A', 0, 1)) }}</span>
                            <span class="d-none d-xl-block ps-2">
                                <span class="d-block small">{{ auth()->user()->name ?? 'Admin' }}</span>
                                <span class="d-block text-muted small mt-1">{{ auth()->user()->email ?? '' }}</span>
                            </span>
                        </a>
                        <div class="dropdown-menu dropdown-menu-end dropdown-menu-arrow">
                            <a href="{{ route('profile.edit') }}" class="dropdown-item">{{ __('Profile & 2FA') }}</a>
                            <a href="{{ url('/dashboard') }}" class="dropdown-item">{{ __('User Dashboard') }}</a>
                            <div class="dropdown-divider"></div>
                            <span class="dropdown-header">{{ app()->getLocale() === 'id' ? 'Bahasa' : 'Language' }}</span>
                            <a href="{{ route('locale.switch', 'id') }}" class="dropdown-item {{ app()->getLocale() === 'id' ? 'active' : '' }}">Bahasa Indonesia</a>
                            <a href="{{ route('locale.switch', 'en') }}" class="dropdown-item {{ app()->getLocale() === 'en' ? 'active' : '' }}">English</a>
                            <div class="dropdown-divider"></div>
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit" class="dropdown-item text-danger">{{ __('Logout') }}</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </header>

        <div class="page-wrapper">
            <div class="page-header d-print-none">
                <div class="container-xl">
                    <div class="row g-2 align-items-center">
                        <div class="col">
                            @hasSection('breadcrumbs')
                                <div class="page-pretitle">@yield('breadcrumbs')</div>
                            @endif
                            <h2 class="page-title">@yield('title', __('Dashboard'))</h2>
                        </div>
                        <div class="col-auto ms-auto d-print-none">
                            @yield('page-actions')
                        </div>
                    </div>
                </div>
            </div>
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
                    @if($errors->any())
                        <div class="alert alert-danger" role="alert">
                            <ul class="mb-0">
                                @foreach($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif
                    @yield('content')
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function adminNotifBell() {
    return {
        count: 0,
        items: [],
        async fetchUnread() {
            try {
                const res = await fetch('{{ route('admin.notifications.bell') }}', { headers: { 'Accept': 'application/json' } });
                if (!res.ok) return;
                const data = await res.json();
                this.count = data.unread_count || 0;
                this.items = data.items || [];
            } catch (e) { /* offline ok */ }
        },
        async markRead() {
            if (this.count === 0) return;
            try {
                await fetch('{{ route('admin.notifications.mark-read') }}', { method: 'POST', headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' } });
                this.count = 0;
            } catch (e) {}
        },
    }
}
</script>

<script>
    if ('serviceWorker' in navigator) {
        window.addEventListener('load', () => navigator.serviceWorker.register('/sw.js').catch(() => {}));
    }
</script>
</body>
</html>
