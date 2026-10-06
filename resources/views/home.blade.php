<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'HelpDesk AI') }} - AI-Powered Customer Support</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700,800&display=swap" rel="stylesheet" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="font-sans antialiased bg-white">

    <!-- Public Navbar -->
    <nav x-data="{ open: false }" class="absolute inset-x-0 top-0 z-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex h-16 items-center justify-between">
                <a href="{{ url('/') }}" class="flex items-center gap-2 text-white">
                    <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"/></svg>
                    <span class="font-bold text-lg">{{ config('app.name', 'HelpDesk AI') }}</span>
                </a>

                <div class="hidden md:flex items-center gap-8 text-sm">
                    <a href="#features" class="text-slate-200 hover:text-white transition">Features</a>
                    <a href="{{ url('/services') }}" class="text-slate-200 hover:text-white transition">Services</a>
                    <a href="{{ url('/blog') }}" class="text-slate-200 hover:text-white transition">Blog</a>
                    <a href="{{ url('/knowledge-base') }}" class="text-slate-200 hover:text-white transition">Knowledge Base</a>
                    <a href="{{ url('/docs') }}" class="text-slate-200 hover:text-white transition">Docs</a>
                    <a href="{{ url('/contact') }}" class="text-slate-200 hover:text-white transition">Contact</a>
                </div>

                <div class="hidden md:flex items-center gap-3">
                    @auth
                        <a href="{{ url('/dashboard') }}" class="px-4 py-2 text-sm font-medium text-white bg-white/10 hover:bg-white/20 border border-white/20 rounded-lg transition">Dashboard</a>
                        @if(auth()->user()->hasRole('admin'))
                            <a href="{{ url('/admin') }}" class="px-4 py-2 text-sm font-semibold text-indigo-900 bg-white hover:bg-indigo-50 rounded-lg transition">Admin Panel</a>
                        @endif
                    @else
                        <a href="{{ route('login') }}" class="px-4 py-2 text-sm font-medium text-white hover:text-indigo-200 transition">Log in</a>
                        <a href="{{ route('register') }}" class="px-4 py-2 text-sm font-semibold text-indigo-900 bg-white hover:bg-indigo-50 rounded-lg transition shadow-sm">Sign up</a>
                    @endauth
                </div>

                <button @click="open = !open" class="md:hidden p-2 text-white" aria-label="Toggle menu">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" :d="open ? 'M6 18L18 6M6 6l12 12' : 'M4 6h16M4 12h16M4 18h16'"/></svg>
                </button>
            </div>
        </div>

        <!-- Mobile menu -->
        <div x-show="open" x-transition class="md:hidden bg-slate-900/95 backdrop-blur-sm border-t border-white/10">
            <div class="px-4 py-4 space-y-2 text-sm">
                <a href="#features" class="block py-2 text-slate-200 hover:text-white">Features</a>
                <a href="{{ url('/services') }}" class="block py-2 text-slate-200 hover:text-white">Services</a>
                <a href="{{ url('/blog') }}" class="block py-2 text-slate-200 hover:text-white">Blog</a>
                <a href="{{ url('/knowledge-base') }}" class="block py-2 text-slate-200 hover:text-white">Knowledge Base</a>
                <a href="{{ url('/docs') }}" class="block py-2 text-slate-200 hover:text-white">Docs</a>
                <a href="{{ url('/contact') }}" class="block py-2 text-slate-200 hover:text-white">Contact</a>
                <div class="pt-3 mt-3 border-t border-white/10 flex flex-col gap-2">
                    @auth
                        <a href="{{ url('/dashboard') }}" class="block px-4 py-2 text-center text-white bg-white/10 border border-white/20 rounded-lg">Dashboard</a>
                        @if(auth()->user()->hasRole('admin'))
                            <a href="{{ url('/admin') }}" class="block px-4 py-2 text-center text-indigo-900 bg-white rounded-lg font-semibold">Admin Panel</a>
                        @endif
                    @else
                        <a href="{{ route('login') }}" class="block px-4 py-2 text-center text-white border border-white/30 rounded-lg">Log in</a>
                        <a href="{{ route('register') }}" class="block px-4 py-2 text-center text-indigo-900 bg-white rounded-lg font-semibold">Sign up</a>
                    @endauth
                </div>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section class="relative bg-gradient-to-br from-slate-900 via-slate-800 to-indigo-950 text-white">
        <div class="absolute inset-0 bg-[url('data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iNjAiIGhlaWdodD0iNjAiIHZpZXdCb3g9IjAgMCA2MCA2MCIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIj48ZyBmaWxsPSJub25lIiBmaWxsLXJ1bGU9ImV2ZW5vZGQiPjxnIGZpbGw9IiNmZmYiIGZpbGwtb3BhY2l0eT0iMC4wMyI+PGNpcmNsZSBjeD0iMzAiIGN5PSIzMCIgcj0iMiIvPjwvZz48L2c+PC9zdmc+')] opacity-50"></div>
        <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-24 md:py-36">
            <div class="text-center max-w-4xl mx-auto">
                <h1 class="text-4xl md:text-6xl font-extrabold tracking-tight mb-6">
                    AI-Powered Customer Support
                </h1>
                <p class="text-lg md:text-xl text-slate-300 mb-10 max-w-3xl mx-auto leading-relaxed">
                    Streamline your customer service with intelligent ticket management, automated responses,
                    and real-time AI assistance. Deliver faster, smarter support at scale.
                </p>
                <div class="flex flex-col sm:flex-row gap-4 justify-center">
                    <a href="{{ route('register') }}" class="inline-flex items-center px-8 py-4 bg-indigo-500 hover:bg-indigo-600 text-white font-semibold rounded-xl transition shadow-lg shadow-indigo-500/25">
                        Get Started Free
                        <svg class="ml-2 w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/></svg>
                    </a>
                    <a href="#features" class="inline-flex items-center px-8 py-4 bg-white/10 hover:bg-white/20 text-white font-semibold rounded-xl transition backdrop-blur-sm border border-white/10">
                        View Features
                    </a>
                </div>
            </div>
        </div>
        <div class="absolute bottom-0 left-0 right-0 h-16 bg-gradient-to-t from-white to-transparent"></div>
    </section>

    <!-- Features Section -->
    <section id="features" class="py-20 bg-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-16">
                <h2 class="text-3xl md:text-4xl font-bold text-slate-900 mb-4">Everything You Need</h2>
                <p class="text-lg text-slate-500 max-w-2xl mx-auto">Powerful tools to manage, automate, and optimize your customer support operations.</p>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                <!-- Feature 1 -->
                <div class="bg-white rounded-2xl p-8 shadow-sm border border-slate-100 hover:shadow-md transition group">
                    <div class="w-12 h-12 bg-indigo-50 rounded-xl flex items-center justify-center mb-5 group-hover:bg-indigo-100 transition">
                        <svg class="w-6 h-6 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                    </div>
                    <h3 class="text-xl font-semibold text-slate-900 mb-3">Ticket Management</h3>
                    <p class="text-slate-500 leading-relaxed">Organize, prioritize, and resolve customer tickets efficiently with smart routing and automated workflows.</p>
                </div>
                <!-- Feature 2 -->
                <div class="bg-white rounded-2xl p-8 shadow-sm border border-slate-100 hover:shadow-md transition group">
                    <div class="w-12 h-12 bg-violet-50 rounded-xl flex items-center justify-center mb-5 group-hover:bg-violet-100 transition">
                        <svg class="w-6 h-6 text-violet-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"/></svg>
                    </div>
                    <h3 class="text-xl font-semibold text-slate-900 mb-3">AI Intelligence</h3>
                    <p class="text-slate-500 leading-relaxed">Leverage AI-powered suggestions, auto-categorization, and smart reply drafts to boost agent productivity.</p>
                </div>
                <!-- Feature 3 -->
                <div class="bg-white rounded-2xl p-8 shadow-sm border border-slate-100 hover:shadow-md transition group">
                    <div class="w-12 h-12 bg-emerald-50 rounded-xl flex items-center justify-center mb-5 group-hover:bg-emerald-100 transition">
                        <svg class="w-6 h-6 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/></svg>
                    </div>
                    <h3 class="text-xl font-semibold text-slate-900 mb-3">Live Chat</h3>
                    <p class="text-slate-500 leading-relaxed">Real-time chat with customers, seamless handoff from bot to human, and full conversation history.</p>
                </div>
                <!-- Feature 4 -->
                <div class="bg-white rounded-2xl p-8 shadow-sm border border-slate-100 hover:shadow-md transition group">
                    <div class="w-12 h-12 bg-amber-50 rounded-xl flex items-center justify-center mb-5 group-hover:bg-amber-100 transition">
                        <svg class="w-6 h-6 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
                    </div>
                    <h3 class="text-xl font-semibold text-slate-900 mb-3">Analytics</h3>
                    <p class="text-slate-500 leading-relaxed">Comprehensive dashboards and reports to track team performance, customer satisfaction, and SLA compliance.</p>
                </div>
                <!-- Feature 5 -->
                <div class="bg-white rounded-2xl p-8 shadow-sm border border-slate-100 hover:shadow-md transition group">
                    <div class="w-12 h-12 bg-rose-50 rounded-xl flex items-center justify-center mb-5 group-hover:bg-rose-100 transition">
                        <svg class="w-6 h-6 text-rose-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5h12M9 3v2m1.048 9.5A18.022 18.022 0 016.412 9m6.088 9h7M11 21l5-10 5 10M12.751 5C11.783 10.77 8.07 15.61 3 18.129"/></svg>
                    </div>
                    <h3 class="text-xl font-semibold text-slate-900 mb-3">Multi-Language</h3>
                    <p class="text-slate-500 leading-relaxed">Automatic translation for global support teams. Communicate with customers in their preferred language.</p>
                </div>
                <!-- Feature 6 -->
                <div class="bg-white rounded-2xl p-8 shadow-sm border border-slate-100 hover:shadow-md transition group">
                    <div class="w-12 h-12 bg-cyan-50 rounded-xl flex items-center justify-center mb-5 group-hover:bg-cyan-100 transition">
                        <svg class="w-6 h-6 text-cyan-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                    </div>
                    <h3 class="text-xl font-semibold text-slate-900 mb-3">Security</h3>
                    <p class="text-slate-500 leading-relaxed">Enterprise-grade security with role-based access, audit logs, data encryption, and GDPR compliance built-in.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Tech Stack Section -->
    <section class="py-20 bg-slate-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-12">
                <h2 class="text-3xl font-bold text-slate-900 mb-4">Built With Modern Technology</h2>
                <p class="text-slate-500 max-w-2xl mx-auto">Powered by a robust stack designed for performance, scalability, and developer experience.</p>
            </div>
            <div class="grid grid-cols-2 md:grid-cols-4 gap-6 max-w-3xl mx-auto">
                <div class="bg-white rounded-xl p-6 text-center shadow-sm border border-slate-100">
                    <div class="text-2xl font-bold text-indigo-600 mb-1">Laravel</div>
                    <div class="text-sm text-slate-500">Backend Framework</div>
                </div>
                <div class="bg-white rounded-xl p-6 text-center shadow-sm border border-slate-100">
                    <div class="text-2xl font-bold text-indigo-600 mb-1">Tailwind</div>
                    <div class="text-sm text-slate-500">CSS Framework</div>
                </div>
                <div class="bg-white rounded-xl p-6 text-center shadow-sm border border-slate-100">
                    <div class="text-2xl font-bold text-indigo-600 mb-1">MySQL</div>
                    <div class="text-sm text-slate-500">Database</div>
                </div>
                <div class="bg-white rounded-xl p-6 text-center shadow-sm border border-slate-100">
                    <div class="text-2xl font-bold text-indigo-600 mb-1">AI/LLM</div>
                    <div class="text-sm text-slate-500">Intelligence Layer</div>
                </div>
            </div>
        </div>
    </section>

    <!-- Quick Links -->
    <section class="py-20 bg-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-12">
                <h2 class="text-3xl font-bold text-slate-900 mb-4">Explore More</h2>
            </div>
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-6 max-w-6xl mx-auto">
                <a href="{{ url('/knowledge-base') }}" class="bg-slate-50 rounded-xl p-6 hover:bg-indigo-50 transition border border-slate-100 hover:border-indigo-200 group">
                    <h3 class="font-semibold text-slate-900 group-hover:text-indigo-600 mb-2">Knowledge Base</h3>
                    <p class="text-sm text-slate-500">Browse articles and guides</p>
                </a>
                <a href="{{ url('/blog') }}" class="bg-slate-50 rounded-xl p-6 hover:bg-indigo-50 transition border border-slate-100 hover:border-indigo-200 group">
                    <h3 class="font-semibold text-slate-900 group-hover:text-indigo-600 mb-2">Blog</h3>
                    <p class="text-sm text-slate-500">Latest news and updates</p>
                </a>
                <a href="{{ url('/services') }}" class="bg-slate-50 rounded-xl p-6 hover:bg-indigo-50 transition border border-slate-100 hover:border-indigo-200 group">
                    <h3 class="font-semibold text-slate-900 group-hover:text-indigo-600 mb-2">Services</h3>
                    <p class="text-sm text-slate-500">What we offer</p>
                </a>
                <a href="{{ url('/docs') }}" class="bg-slate-50 rounded-xl p-6 hover:bg-indigo-50 transition border border-slate-100 hover:border-indigo-200 group">
                    <h3 class="font-semibold text-slate-900 group-hover:text-indigo-600 mb-2">Docs</h3>
                    <p class="text-sm text-slate-500">Tutorial &amp; demo access</p>
                </a>
                <a href="{{ url('/contact') }}" class="bg-slate-50 rounded-xl p-6 hover:bg-indigo-50 transition border border-slate-100 hover:border-indigo-200 group">
                    <h3 class="font-semibold text-slate-900 group-hover:text-indigo-600 mb-2">Contact</h3>
                    <p class="text-sm text-slate-500">Get in touch</p>
                </a>
            </div>
        </div>
    </section>

    <!-- CTA -->
    <section class="py-20 bg-gradient-to-r from-indigo-600 to-violet-600">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
            <h2 class="text-3xl md:text-4xl font-bold text-white mb-4">Ready to Transform Your Support?</h2>
            <p class="text-indigo-100 mb-8 text-lg">Join thousands of teams using HelpDesk AI to deliver exceptional customer experiences.</p>
            <a href="{{ route('register') }}" class="inline-flex items-center px-8 py-4 bg-white text-indigo-600 font-semibold rounded-xl hover:bg-indigo-50 transition shadow-lg">
                Start Free Trial
                <svg class="ml-2 w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/></svg>
            </a>
        </div>
    </section>

    <!-- Sell Source Code Popup (shown once per visitor via localStorage) -->
    <div x-data="sellPopup()" x-init="maybeOpen()" x-cloak>
        <div x-show="open"
             x-transition.opacity
             @keydown.escape.window="close()"
             class="fixed inset-0 z-[100] bg-slate-900/70 backdrop-blur-sm flex items-center justify-center p-4">
            <div @click.outside="close()"
                 x-show="open"
                 x-transition:enter="transition ease-out duration-200"
                 x-transition:enter-start="opacity-0 scale-95"
                 x-transition:enter-end="opacity-100 scale-100"
                 class="relative bg-white rounded-2xl shadow-2xl max-w-md w-full overflow-hidden">

                <button @click="close()" aria-label="Close" class="absolute top-3 right-3 z-10 p-1.5 rounded-full text-slate-400 hover:text-slate-700 hover:bg-slate-100 transition">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>

                <div class="bg-gradient-to-br from-indigo-600 via-violet-600 to-fuchsia-600 px-6 py-8 text-white text-center">
                    <div class="inline-flex items-center justify-center w-16 h-16 bg-white/15 rounded-2xl mb-3 backdrop-blur-sm">
                        <svg class="w-9 h-9" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4"/></svg>
                    </div>
                    <h3 class="text-2xl font-extrabold tracking-tight">Source Code Dijual</h3>
                    <p class="text-indigo-100 text-sm mt-1">HelpDesk AI — Laravel + Tailwind + AI</p>
                </div>

                <div class="px-6 py-5 space-y-4">
                    <p class="text-sm text-slate-700 text-center">
                        Mau pakai source code <strong>HelpDesk AI</strong> ini untuk project Anda? Full source, semua fitur, plus dokumentasi. Bisa rebrand &amp; jual ulang.
                    </p>

                    <ul class="space-y-1.5 text-xs text-slate-600">
                        <li class="flex items-start gap-2"><svg class="w-4 h-4 text-emerald-500 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg> Laravel 13 + MySQL + Tailwind v4</li>
                        <li class="flex items-start gap-2"><svg class="w-4 h-4 text-emerald-500 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg> 24 preset AI provider (BYOK)</li>
                        <li class="flex items-start gap-2"><svg class="w-4 h-4 text-emerald-500 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg> pSEO, 2FA, push notifications, license kit</li>
                    </ul>

                    <a href="https://wa.me/6281296052010?text=Halo,%20saya%20tertarik%20beli%20source%20code%20HelpDesk%20AI%20dari%20website%20ini."
                       target="_blank" rel="noopener"
                       @click="close()"
                       class="flex items-center justify-center gap-2 w-full bg-[#25D366] hover:bg-[#1ebe5a] text-white font-semibold py-3 rounded-xl transition shadow-lg shadow-emerald-500/20">
                        <svg class="w-5 h-5" viewBox="0 0 24 24" fill="currentColor"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/></svg>
                        Chat WhatsApp 081296052010
                    </a>

                    <button type="button" @click="close()" class="block w-full text-center text-xs text-slate-400 hover:text-slate-600">
                        Nanti saja
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <footer class="bg-slate-900 text-slate-400 py-12">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-2 md:grid-cols-4 gap-8 mb-8">
                <div>
                    <h4 class="text-white font-semibold mb-4">{{ config('app.name', 'HelpDesk AI') }}</h4>
                    <p class="text-sm">AI-powered customer support platform.</p>
                </div>
                <div>
                    <h4 class="text-white font-semibold mb-4">Product</h4>
                    <ul class="space-y-2 text-sm">
                        <li><a href="#" class="hover:text-white transition">Features</a></li>
                        <li><a href="{{ url('/services') }}" class="hover:text-white transition">Services</a></li>
                        <li><a href="#" class="hover:text-white transition">Pricing</a></li>
                    </ul>
                </div>
                <div>
                    <h4 class="text-white font-semibold mb-4">Resources</h4>
                    <ul class="space-y-2 text-sm">
                        <li><a href="{{ url('/knowledge-base') }}" class="hover:text-white transition">Knowledge Base</a></li>
                        <li><a href="{{ url('/blog') }}" class="hover:text-white transition">Blog</a></li>
                        <li><a href="{{ url('/docs') }}" class="hover:text-white transition">Docs</a></li>
                    </ul>
                </div>
                <div>
                    <h4 class="text-white font-semibold mb-4">Company</h4>
                    <ul class="space-y-2 text-sm">
                        <li><a href="#" class="hover:text-white transition">About</a></li>
                        <li><a href="{{ url('/contact') }}" class="hover:text-white transition">Contact</a></li>
                        <li><a href="#" class="hover:text-white transition">Privacy</a></li>
                    </ul>
                </div>
            </div>
            <div class="border-t border-slate-800 pt-8 text-center text-sm">
                <p>&copy; {{ date('Y') }} {{ config('app.name', 'HelpDesk AI') }}. All rights reserved.</p>
            </div>
        </div>
    </footer>

    <script>
        function sellPopup() {
            return {
                open: false,
                storageKey: 'sellPopupShown_v1',
                maybeOpen() {
                    try {
                        if (localStorage.getItem(this.storageKey)) return;
                    } catch (e) {}
                    setTimeout(() => { this.open = true; }, 1500);
                },
                close() {
                    this.open = false;
                    try { localStorage.setItem(this.storageKey, '1'); } catch (e) {}
                },
            }
        }
    </script>

</body>
</html>
