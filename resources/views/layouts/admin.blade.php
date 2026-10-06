<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Admin') — {{ config('app.name', 'HelpDesk AI') }}</title>
    <link rel="manifest" href="/manifest.json">
    <meta name="theme-color" content="#0f172a">
    <link rel="icon" type="image/svg+xml" href="/icons/icon-192.svg">
    <link rel="apple-touch-icon" href="/icons/icon-192.svg">
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700&display=swap" rel="stylesheet" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="font-sans antialiased bg-gray-50 min-h-screen">

@php
    $sections = [
        'Overview'   => [
            ['route' => 'admin.dashboard',          'label' => 'Dashboard',         'icon' => 'M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6'],
            ['route' => 'admin.analytics.index',    'label' => 'Analytics',         'icon' => 'M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z'],
            ['route' => 'admin.ai-usage-logs.index','label' => 'AI Usage Logs',     'icon' => 'M13 10V3L4 14h7v7l9-11h-7z'],
            ['route' => 'admin.activity-log.index', 'label' => 'Activity Log',      'icon' => 'M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z'],
        ],
        'Support'    => [
            ['route' => 'admin.tickets.index',        'label' => 'Tickets',         'icon' => 'M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z'],
            ['route' => 'admin.conversations.index',  'label' => 'Conversations',   'icon' => 'M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z'],
            ['route' => 'admin.users.index',          'label' => 'Users',           'icon' => 'M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z'],
            ['route' => 'admin.canned-responses.index','label' => 'Canned Responses','icon' => 'M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z'],
        ],
        'Content'    => [
            ['route' => 'admin.knowledge.index',            'label' => 'KB Articles',  'icon' => 'M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253'],
            ['route' => 'admin.knowledge-categories.index', 'label' => 'KB Categories','icon' => 'M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10'],
            ['route' => 'admin.knowledge-faqs.index',       'label' => 'FAQs',         'icon' => 'M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z'],
            ['route' => 'admin.posts.index',                'label' => 'Blog',         'icon' => 'M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9a2 2 0 00-2-2h-2m-4-3H9M7 16h6M7 8h6v4H7V8z'],
            ['route' => 'admin.services.index',             'label' => 'Services',     'icon' => 'M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z'],
            ['route' => 'admin.seo-meta.index',             'label' => 'SEO Meta',     'icon' => 'M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z'],
        ],
        'AI'         => [
            ['route' => 'admin.ai-providers.index', 'label' => 'AI Providers',  'icon' => 'M13 10V3L4 14h7v7l9-11h-7z'],
            ['route' => 'admin.ai-features.index',  'label' => 'AI Features',   'icon' => 'M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z'],
        ],
        'Automation' => [
            ['route' => 'admin.sla-policies.index',    'label' => 'SLA Policies',     'icon' => 'M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z'],
            ['route' => 'admin.automation-rules.index','label' => 'Automation Rules', 'icon' => 'M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15'],
            ['route' => 'admin.email-logs.index',      'label' => 'Email Logs',       'icon' => 'M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z'],
            ['route' => 'admin.push-subscriptions.index','label' => 'Push Subscribers','icon' => 'M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9'],
        ],
        'Settings'   => [
            ['route' => 'admin.settings.index',         'label' => 'General Settings', 'icon' => 'M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.066 2.573c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.573 1.066c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.066-2.573c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z'],
            ['route' => 'admin.email-templates.index',  'label' => 'Email Templates',  'icon' => 'M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z'],
            ['route' => 'admin.license.index',          'label' => 'License Status',   'icon' => 'M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z'],
            ['route' => 'admin.api-keys.index',         'label' => 'API Keys',         'icon' => 'M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z'],
            ['route' => 'admin.departments.index',      'label' => 'Departments',      'icon' => 'M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4'],
            ['route' => 'admin.categories.index',       'label' => 'Categories',       'icon' => 'M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z'],
        ],
        'Reports'    => [
            ['route' => 'admin.export.tickets', 'label' => 'Export Tickets CSV', 'icon' => 'M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4', 'external' => true],
            ['route' => 'admin.export.agents',  'label' => 'Export Agents CSV',  'icon' => 'M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4', 'external' => true],
        ],
    ];

    $isRouteActive = function ($route) {
        if (! \Illuminate\Support\Facades\Route::has($route)) {
            return false;
        }
        $base = preg_replace('/\.(index|show|edit|create|update|store|destroy)$/', '.*', $route);
        return request()->routeIs($base);
    };

    $isSectionActive = function ($items) use ($isRouteActive) {
        foreach ($items as $item) {
            if ($isRouteActive($item['route'])) return true;
        }
        return false;
    };
@endphp

<div x-data="{ mobileOpen: false, openDropdown: null }">
    {{-- TOP NAVBAR --}}
    <header class="bg-[#0f172a] text-slate-200 shadow-md sticky top-0 z-40">
        <div class="max-w-screen-2xl mx-auto px-4 lg:px-6">
            <div class="flex items-center h-16 gap-4">
                {{-- Logo --}}
                <a href="{{ route('admin.dashboard') }}" class="flex items-center gap-2 text-white shrink-0">
                    <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"/></svg>
                    <span class="font-bold text-lg hidden sm:block">{{ config('app.name', 'HelpDesk AI') }}</span>
                </a>

                {{-- Desktop Nav --}}
                <nav class="hidden lg:flex items-center gap-1 ml-4 flex-1">
                    @foreach($sections as $sectionName => $items)
                        @php
                            $isActive = $isSectionActive($items);
                            $singleItem = count($items) === 1 ? $items[0] : null;
                        @endphp

                        @if($singleItem)
                            <a href="{{ \Illuminate\Support\Facades\Route::has($singleItem['route']) ? route($singleItem['route']) : '#' }}"
                               class="px-3 py-2 rounded-lg text-sm font-medium transition flex items-center gap-1.5 {{ $isActive ? 'bg-slate-800 text-white' : 'text-slate-300 hover:bg-slate-800/60 hover:text-white' }}">
                                {{ $singleItem['label'] }}
                            </a>
                        @else
                            <div class="relative" x-data="{ open: false }" @mouseenter="open = true" @mouseleave="open = false">
                                <button @click="open = !open"
                                    class="px-3 py-2 rounded-lg text-sm font-medium transition flex items-center gap-1.5 {{ $isActive ? 'bg-slate-800 text-white' : 'text-slate-300 hover:bg-slate-800/60 hover:text-white' }}">
                                    {{ $sectionName }}
                                    <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd"/></svg>
                                </button>
                                <div x-show="open" x-cloak
                                    x-transition:enter="transition ease-out duration-150" x-transition:enter-start="opacity-0 -translate-y-1" x-transition:enter-end="opacity-100 translate-y-0"
                                    class="absolute left-0 top-full pt-1 min-w-[220px] z-50">
                                    <div class="bg-white rounded-lg shadow-xl border border-gray-100 py-2">
                                        @foreach($items as $item)
                                            @php
                                                $itemActive = $isRouteActive($item['route']);
                                                $href = \Illuminate\Support\Facades\Route::has($item['route']) ? route($item['route']) : '#';
                                                $target = ($item['external'] ?? false) ? '_blank' : '_self';
                                            @endphp
                                            <a href="{{ $href }}" target="{{ $target }}"
                                                class="flex items-center gap-3 px-4 py-2 text-sm transition {{ $itemActive ? 'bg-indigo-50 text-indigo-700' : 'text-slate-700 hover:bg-gray-50' }}">
                                                <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $item['icon'] }}"/></svg>
                                                <span>{{ $item['label'] }}</span>
                                            </a>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                        @endif
                    @endforeach
                </nav>

                <div class="flex-1 lg:hidden"></div>

                {{-- Right side: bell + user + view site --}}
                <div class="flex items-center gap-2 shrink-0">
                    {{-- Notifications Bell --}}
                    <div class="relative" x-data="adminNotifBell()" x-init="fetchUnread()">
                        <button @click="open = !open; if (open) markRead()" class="relative p-2 rounded-lg text-slate-300 hover:bg-slate-800/60 hover:text-white transition">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/></svg>
                            <span x-show="count > 0" x-cloak class="absolute -top-0.5 -right-0.5 min-w-[18px] h-[18px] px-1 bg-red-500 text-white text-[10px] font-bold rounded-full flex items-center justify-center" x-text="count > 9 ? '9+' : count"></span>
                        </button>
                        <div x-show="open" x-cloak @click.away="open = false"
                            x-transition class="absolute right-0 mt-2 w-80 bg-white rounded-lg shadow-xl border border-gray-100 z-50">
                            <div class="flex items-center justify-between px-4 py-2.5 border-b border-gray-100">
                                <h4 class="text-sm font-semibold text-slate-900">Notifications</h4>
                                <a href="{{ route('admin.notifications.index') }}" class="text-xs text-indigo-600 hover:text-indigo-700">View all</a>
                            </div>
                            <div class="max-h-80 overflow-y-auto">
                                <template x-if="items.length === 0">
                                    <div class="px-4 py-8 text-center text-sm text-slate-400">No new notifications</div>
                                </template>
                                <template x-for="n in items" :key="n.id">
                                    <a :href="n.action_url || '#'" class="flex gap-3 px-4 py-3 border-b border-gray-50 hover:bg-gray-50 transition">
                                        <div class="w-2 h-2 mt-2 rounded-full" :class="n.read_at ? 'bg-gray-300' : 'bg-indigo-500'"></div>
                                        <div class="flex-1 min-w-0">
                                            <p class="text-sm text-slate-900 truncate" x-text="n.title"></p>
                                            <p class="text-xs text-slate-500 truncate" x-text="n.body"></p>
                                            <p class="text-[10px] text-slate-400 mt-1" x-text="n.created_at"></p>
                                        </div>
                                    </a>
                                </template>
                            </div>
                        </div>
                    </div>

                    {{-- View Site --}}
                    <a href="{{ url('/') }}" target="_blank" title="View public site" class="hidden sm:flex p-2 rounded-lg text-slate-300 hover:bg-slate-800/60 hover:text-white transition">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                    </a>

                    {{-- User Dropdown --}}
                    <div class="relative" x-data="{ open: false }">
                        <button @click="open = !open" class="flex items-center gap-2 p-1.5 pr-2 rounded-lg hover:bg-slate-800/60 transition">
                            <div class="w-8 h-8 bg-indigo-500 rounded-full flex items-center justify-center text-white text-sm font-semibold">
                                {{ strtoupper(substr(auth()->user()->name ?? 'A', 0, 1)) }}
                            </div>
                            <span class="hidden md:block text-sm font-medium text-slate-200">{{ auth()->user()->name ?? 'Admin' }}</span>
                            <svg class="w-3 h-3 text-slate-400 hidden md:block" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd"/></svg>
                        </button>
                        <div x-show="open" x-cloak @click.away="open = false"
                            x-transition class="absolute right-0 mt-2 w-52 bg-white rounded-lg shadow-xl border border-gray-100 py-1 z-50">
                            <div class="px-4 py-2 border-b border-gray-100">
                                <p class="text-sm font-semibold text-slate-900 truncate">{{ auth()->user()->name ?? 'Admin' }}</p>
                                <p class="text-xs text-slate-500 truncate">{{ auth()->user()->email ?? '' }}</p>
                            </div>
                            <a href="{{ route('profile.edit') }}" class="block px-4 py-2 text-sm text-slate-700 hover:bg-gray-50">Profile &amp; 2FA</a>
                            <a href="{{ url('/dashboard') }}" class="block px-4 py-2 text-sm text-slate-700 hover:bg-gray-50">User Dashboard</a>
                            <div class="border-t border-gray-100 my-1"></div>
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit" class="w-full text-left px-4 py-2 text-sm text-rose-600 hover:bg-rose-50">Logout</button>
                            </form>
                        </div>
                    </div>

                    {{-- Mobile hamburger --}}
                    <button @click="mobileOpen = !mobileOpen" class="lg:hidden p-2 rounded-lg text-slate-300 hover:bg-slate-800/60 hover:text-white transition">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" :d="mobileOpen ? 'M6 18L18 6M6 6l12 12' : 'M4 6h16M4 12h16M4 18h16'"/></svg>
                    </button>
                </div>
            </div>
        </div>

        {{-- Mobile Menu Panel --}}
        <div x-show="mobileOpen" x-cloak x-transition class="lg:hidden bg-[#0b1220] border-t border-slate-800 max-h-[calc(100vh-4rem)] overflow-y-auto">
            <div class="px-4 py-3 space-y-4">
                @foreach($sections as $sectionName => $items)
                    <div>
                        <button @click="openDropdown = openDropdown === '{{ $sectionName }}' ? null : '{{ $sectionName }}'"
                            class="w-full flex items-center justify-between py-2 text-xs font-semibold text-slate-400 uppercase tracking-wider">
                            <span>{{ $sectionName }}</span>
                            <svg class="w-4 h-4 transition" :class="openDropdown === '{{ $sectionName }}' ? 'rotate-180' : ''" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd"/></svg>
                        </button>
                        <div x-show="openDropdown === '{{ $sectionName }}'" x-collapse class="space-y-1 mt-1">
                            @foreach($items as $item)
                                @php
                                    $itemActive = $isRouteActive($item['route']);
                                    $href = \Illuminate\Support\Facades\Route::has($item['route']) ? route($item['route']) : '#';
                                    $target = ($item['external'] ?? false) ? '_blank' : '_self';
                                @endphp
                                <a href="{{ $href }}" target="{{ $target }}"
                                    class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm {{ $itemActive ? 'bg-indigo-600 text-white' : 'text-slate-300 hover:bg-slate-800/60 hover:text-white' }}">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $item['icon'] }}"/></svg>
                                    {{ $item['label'] }}
                                </a>
                            @endforeach
                        </div>
                    </div>
                @endforeach

                <div class="pt-3 border-t border-slate-800">
                    <a href="{{ url('/') }}" target="_blank" class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm text-slate-300 hover:bg-slate-800/60 hover:text-white">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                        View Public Site
                    </a>
                </div>
            </div>
        </div>
    </header>

    {{-- Page Content --}}
    <main class="max-w-screen-2xl mx-auto p-4 lg:p-6">
        @if(session('success'))
            <div class="mb-4 p-3 bg-emerald-50 border border-emerald-200 text-emerald-800 rounded-lg text-sm">{{ session('success') }}</div>
        @endif
        @if(session('error'))
            <div class="mb-4 p-3 bg-rose-50 border border-rose-200 text-rose-800 rounded-lg text-sm">{{ session('error') }}</div>
        @endif
        @yield('content')
    </main>
</div>

<script>
function adminNotifBell() {
    return {
        open: false,
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
