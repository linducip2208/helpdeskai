<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'HelpDesk AI') }} - AI-Powered Customer Support</title>
    <meta name="description" content="Streamline your customer service with intelligent ticket management, automated responses, and real-time AI assistance.">
    <link rel="icon" type="image/svg+xml" href="/icons/icon-192.svg">
    <script>
        try {
            document.documentElement.setAttribute('data-bs-theme', localStorage.getItem('helpdeskai-theme') || 'light');
        } catch (e) {}
    </script>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="d-flex flex-column min-vh-100">

    <!-- Public Navbar -->
    <header class="navbar navbar-expand-md d-print-none" data-bs-theme="dark" style="background-color: #1a2234;">
        <div class="container-xl" x-data="{ open: false }">
            <button class="navbar-toggler" type="button" @click="open = !open" aria-label="{{ __('Toggle navigation') }}">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="navbar-brand navbar-brand-autodark d-none-navbar-horizontal pe-0 pe-md-3">
                <a href="{{ url('/') }}" class="d-flex align-items-center gap-2 text-decoration-none text-white">
                    <svg width="28" height="28" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"/></svg>
                    <span class="fw-bold fs-3">{{ config('app.name', 'HelpDesk AI') }}</span>
                </a>
            </div>
            <div class="navbar-nav flex-row order-md-last ms-auto">
                <div class="nav-item d-flex align-items-center me-1">
                    <button type="button" onclick="toggleTheme()" class="btn btn-ghost-light btn-icon" title="Toggle dark mode" aria-label="Toggle dark mode">
                        <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M21 12.79A9 9 0 1111.21 3 7 7 0 0021 12.79z"/></svg>
                    </button>
                </div>
                @auth
                    <div class="nav-item d-flex align-items-center gap-2">
                        <a href="{{ url('/dashboard') }}" class="btn btn-ghost-light">Dashboard</a>
                        @if(auth()->user()->hasRole('admin'))
                            <a href="{{ url('/admin') }}" class="btn btn-light">Admin Panel</a>
                        @endif
                    </div>
                @else
                    <div class="nav-item d-flex align-items-center gap-2">
                        <a href="{{ route('login') }}" class="btn btn-ghost-light">Log in</a>
                        <a href="{{ route('register') }}" class="btn btn-light">Sign up</a>
                    </div>
                @endauth
            </div>
            <div class="collapse navbar-collapse" :class="{ 'show': open }" id="navbar-menu">
                <div class="d-flex flex-column flex-md-row flex-fill align-items-stretch align-items-md-center">
                    <ul class="navbar-nav">
                        <li class="nav-item"><a class="nav-link" href="#features"><span class="nav-link-title">Features</span></a></li>
                        <li class="nav-item"><a class="nav-link" href="{{ url('/services') }}"><span class="nav-link-title">Services</span></a></li>
                        <li class="nav-item"><a class="nav-link" href="{{ url('/blog') }}"><span class="nav-link-title">Blog</span></a></li>
                        <li class="nav-item"><a class="nav-link" href="{{ url('/knowledge-base') }}"><span class="nav-link-title">Knowledge Base</span></a></li>
                        <li class="nav-item"><a class="nav-link" href="{{ url('/docs') }}"><span class="nav-link-title">Docs</span></a></li>
                        <li class="nav-item"><a class="nav-link" href="{{ url('/contact') }}"><span class="nav-link-title">Contact</span></a></li>
                    </ul>
                </div>
            </div>
        </div>
    </header>

    <!-- Hero Section -->
    <section class="text-white" data-bs-theme="dark" style="background-color: #1a2234;">
        <div class="container-xl py-5 py-md-6 text-center" style="padding-top: 5rem !important; padding-bottom: 5rem !important;">
            <div class="row justify-content-center">
                <div class="col-lg-9">
                    <h1 class="display-4 fw-bold mb-4">AI-Powered Customer Support</h1>
                    <p class="fs-3 text-white-50 mb-5">
                        Streamline your customer service with intelligent ticket management, automated responses,
                        and real-time AI assistance. Deliver faster, smarter support at scale.
                    </p>
                    <div class="d-flex flex-column flex-sm-row gap-3 justify-content-center">
                        <a href="{{ route('register') }}" class="btn btn-primary btn-lg">
                            Get Started Free
                            <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M13 7l5 5m0 0l-5 5m5-5H6"/></svg>
                        </a>
                        <a href="#features" class="btn btn-ghost-light btn-lg">View Features</a>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Features Section -->
    <section id="features" class="py-5">
        <div class="container-xl">
            <div class="text-center mb-5">
                <h2 class="display-6 fw-bold mb-3">Everything You Need</h2>
                <p class="text-muted fs-3">Powerful tools to manage, automate, and optimize your customer support operations.</p>
            </div>
            <div class="row row-cards">
                <div class="col-md-6 col-lg-4">
                    <div class="card h-100">
                        <div class="card-body">
                            <div class="avatar avatar-lg bg-indigo-lt mb-3">
                                <svg width="24" height="24" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                            </div>
                            <h3 class="card-title">Ticket Management</h3>
                            <p class="text-muted">Organize, prioritize, and resolve customer tickets efficiently with smart routing and automated workflows.</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-6 col-lg-4">
                    <div class="card h-100">
                        <div class="card-body">
                            <div class="avatar avatar-lg bg-violet-lt mb-3">
                                <svg width="24" height="24" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"/></svg>
                            </div>
                            <h3 class="card-title">AI Intelligence</h3>
                            <p class="text-muted">Leverage AI-powered suggestions, auto-categorization, and smart reply drafts to boost agent productivity.</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-6 col-lg-4">
                    <div class="card h-100">
                        <div class="card-body">
                            <div class="avatar avatar-lg bg-green-lt mb-3">
                                <svg width="24" height="24" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/></svg>
                            </div>
                            <h3 class="card-title">Live Chat</h3>
                            <p class="text-muted">Real-time chat with customers, seamless handoff from bot to human, and full conversation history.</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-6 col-lg-4">
                    <div class="card h-100">
                        <div class="card-body">
                            <div class="avatar avatar-lg bg-yellow-lt mb-3">
                                <svg width="24" height="24" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
                            </div>
                            <h3 class="card-title">Analytics</h3>
                            <p class="text-muted">Comprehensive dashboards and reports to track team performance, customer satisfaction, and SLA compliance.</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-6 col-lg-4">
                    <div class="card h-100">
                        <div class="card-body">
                            <div class="avatar avatar-lg bg-red-lt mb-3">
                                <svg width="24" height="24" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M3 5h12M9 3v2m1.048 9.5A18.022 18.022 0 016.412 9m6.088 9h7M11 21l5-10 5 10M12.751 5C11.783 10.77 8.07 15.61 3 18.129"/></svg>
                            </div>
                            <h3 class="card-title">Multi-Language</h3>
                            <p class="text-muted">Automatic translation for global support teams. Communicate with customers in their preferred language.</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-6 col-lg-4">
                    <div class="card h-100">
                        <div class="card-body">
                            <div class="avatar avatar-lg bg-cyan-lt mb-3">
                                <svg width="24" height="24" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                            </div>
                            <h3 class="card-title">Security</h3>
                            <p class="text-muted">Enterprise-grade security with role-based access, audit logs, data encryption, and GDPR compliance built-in.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Tech Stack Section -->
    <section class="py-5 bg-surface-secondary">
        <div class="container-xl">
            <div class="text-center mb-4">
                <h2 class="display-6 fw-bold mb-3">Built With Modern Technology</h2>
                <p class="text-muted">Powered by a robust stack designed for performance, scalability, and developer experience.</p>
            </div>
            <div class="row row-cards justify-content-center">
                <div class="col-6 col-md-3">
                    <div class="card card-sm text-center"><div class="card-body"><div class="fw-bold text-primary fs-2">Laravel</div><div class="text-muted small">Backend Framework</div></div></div>
                </div>
                <div class="col-6 col-md-3">
                    <div class="card card-sm text-center"><div class="card-body"><div class="fw-bold text-primary fs-2">Tabler</div><div class="text-muted small">UI Framework</div></div></div>
                </div>
                <div class="col-6 col-md-3">
                    <div class="card card-sm text-center"><div class="card-body"><div class="fw-bold text-primary fs-2">MySQL</div><div class="text-muted small">Database</div></div></div>
                </div>
                <div class="col-6 col-md-3">
                    <div class="card card-sm text-center"><div class="card-body"><div class="fw-bold text-primary fs-2">AI/LLM</div><div class="text-muted small">Intelligence Layer</div></div></div>
                </div>
            </div>
        </div>
    </section>

    <!-- Quick Links -->
    <section class="py-5">
        <div class="container-xl">
            <div class="text-center mb-4">
                <h2 class="display-6 fw-bold">Explore More</h2>
            </div>
            <div class="row row-cards">
                <div class="col-sm-6 col-lg-4">
                    <a href="{{ url('/knowledge-base') }}" class="card card-link">
                        <div class="card-body"><h3 class="card-title">Knowledge Base</h3><p class="text-muted mb-0">Browse articles and guides</p></div>
                    </a>
                </div>
                <div class="col-sm-6 col-lg-4">
                    <a href="{{ url('/blog') }}" class="card card-link">
                        <div class="card-body"><h3 class="card-title">Blog</h3><p class="text-muted mb-0">Latest news and updates</p></div>
                    </a>
                </div>
                <div class="col-sm-6 col-lg-4">
                    <a href="{{ url('/services') }}" class="card card-link">
                        <div class="card-body"><h3 class="card-title">Services</h3><p class="text-muted mb-0">What we offer</p></div>
                    </a>
                </div>
                <div class="col-sm-6 col-lg-6">
                    <a href="{{ url('/docs') }}" class="card card-link">
                        <div class="card-body"><h3 class="card-title">Docs</h3><p class="text-muted mb-0">Tutorial &amp; demo access</p></div>
                    </a>
                </div>
                <div class="col-sm-6 col-lg-6">
                    <a href="{{ url('/contact') }}" class="card card-link">
                        <div class="card-body"><h3 class="card-title">Contact</h3><p class="text-muted mb-0">Get in touch</p></div>
                    </a>
                </div>
            </div>
        </div>
    </section>

    <!-- CTA -->
    <section class="py-5 text-white" data-bs-theme="dark" style="background-color: #206bc4;">
        <div class="container-xl text-center">
            <h2 class="display-6 fw-bold mb-3">Ready to Transform Your Support?</h2>
            <p class="fs-3 mb-4">Join thousands of teams using HelpDesk AI to deliver exceptional customer experiences.</p>
            <a href="{{ route('register') }}" class="btn btn-light btn-lg">
                Start Free Trial
                <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M13 7l5 5m0 0l-5 5m5-5H6"/></svg>
            </a>
        </div>
    </section>

    <!-- Sell Source Code Popup (shown once per visitor via localStorage) -->
    <div x-data="sellPopup()" x-init="maybeOpen()" x-cloak>
        <div x-show="open" x-transition.opacity @keydown.escape.window="close()"
             class="modal modal-blur fade show d-block" tabindex="-1" role="dialog" style="background-color: rgba(0,0,0,.5);">
            <div class="modal-dialog modal-dialog-centered" role="document" @click.outside="close()">
                <div class="modal-content overflow-hidden">
                    <button @click="close()" aria-label="{{ __('Close') }}" class="btn-close position-absolute top-0 end-0 m-3"></button>
                    <div class="text-white text-center px-4 py-4" data-bs-theme="dark" style="background-color: #4a4ac3;">
                        <h3 class="fw-bold mb-1">Source Code Dijual</h3>
                        <p class="mb-0 small">HelpDesk AI — Laravel + Tabler + AI</p>
                    </div>
                    <div class="modal-body">
                        <p class="text-center">
                            Mau pakai source code <strong>HelpDesk AI</strong> ini untuk project Anda? Full source, semua fitur, plus dokumentasi. Bisa rebrand &amp; jual ulang.
                        </p>
                        <ul class="list-unstyled text-muted small">
                            <li class="mb-1">✓ Laravel 13 + MySQL + Tabler UI</li>
                            <li class="mb-1">✓ 24 preset AI provider (BYOK)</li>
                            <li class="mb-1">✓ pSEO, 2FA, push notifications, license kit</li>
                        </ul>
                        <a href="https://wa.me/6281296052010?text=Halo,%20saya%20tertarik%20beli%20source%20code%20HelpDesk%20AI%20dari%20website%20ini."
                           target="_blank" rel="noopener" @click="close()"
                           class="btn btn-success w-100">
                            Chat WhatsApp 081296052010
                        </a>
                        <button type="button" @click="close()" class="btn btn-link btn-sm w-100 text-muted mt-2">
                            Nanti saja
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <footer class="footer footer-transparent mt-auto">
        <div class="container-xl">
            <div class="row">
                <div class="col-md-3 mb-3">
                    <h4 class="subheader mb-2">{{ config('app.name', 'HelpDesk AI') }}</h4>
                    <p class="text-muted small">AI-powered customer support platform.</p>
                </div>
                <div class="col-6 col-md-3 mb-3">
                    <h4 class="subheader mb-2">Product</h4>
                    <ul class="list-unstyled small">
                        <li><a href="#features">Features</a></li>
                        <li><a href="{{ url('/services') }}">Services</a></li>
                    </ul>
                </div>
                <div class="col-6 col-md-3 mb-3">
                    <h4 class="subheader mb-2">Resources</h4>
                    <ul class="list-unstyled small">
                        <li><a href="{{ url('/knowledge-base') }}">Knowledge Base</a></li>
                        <li><a href="{{ url('/blog') }}">Blog</a></li>
                        <li><a href="{{ url('/docs') }}">Docs</a></li>
                    </ul>
                </div>
                <div class="col-6 col-md-3 mb-3">
                    <h4 class="subheader mb-2">Company</h4>
                    <ul class="list-unstyled small">
                        <li><a href="{{ url('/contact') }}">Contact</a></li>
                    </ul>
                </div>
            </div>
            <div class="border-top pt-3 text-center text-muted small">
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
