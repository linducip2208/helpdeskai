<header class="navbar navbar-expand-md d-print-none">
    <div class="container-xl">
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbar-menu" aria-controls="navbar-menu" aria-expanded="false" aria-label="{{ __('Toggle navigation') }}">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="navbar-brand navbar-brand-autodark d-none-navbar-horizontal pe-0 pe-md-3">
            <a href="{{ url('/') }}" class="d-flex align-items-center gap-2 text-decoration-none">
                <x-application-logo width="32" height="32" />
                <span class="fw-bold">{{ config('app.name', 'HelpDesk AI') }}</span>
            </a>
        </div>
        <div class="navbar-nav flex-row order-md-last ms-auto">
            <div class="nav-item d-flex align-items-center me-1">
                <button type="button" onclick="toggleTheme()" class="btn btn-ghost-secondary btn-icon" title="{{ __('Toggle dark mode') }}" aria-label="{{ __('Toggle dark mode') }}">
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M21 12.79A9 9 0 1111.21 3 7 7 0 0021 12.79z"/></svg>
                </button>
            </div>
            @auth
                <div class="nav-item dropdown">
                    <a href="#" class="nav-link d-flex lh-1 text-reset p-0" data-bs-toggle="dropdown" aria-label="{{ __('Open user menu') }}">
                        <span class="avatar avatar-sm">{{ strtoupper(substr(Auth::user()->name ?? 'U', 0, 1)) }}</span>
                        <span class="d-none d-xl-block ps-2">
                            <span class="d-block small">{{ Auth::user()->name }}</span>
                        </span>
                    </a>
                    <div class="dropdown-menu dropdown-menu-end dropdown-menu-arrow">
                        <a href="{{ route('dashboard') }}" class="dropdown-item">{{ __('Dashboard') }}</a>
                        <a href="{{ route('profile.edit') }}" class="dropdown-item">{{ __('Profile') }}</a>
                        @if(Auth::user()->hasRole('admin'))
                            <a href="{{ route('admin.dashboard') }}" class="dropdown-item">{{ __('Admin Panel') }}</a>
                        @endif
                        <div class="dropdown-divider"></div>
                        <span class="dropdown-header">{{ app()->getLocale() === 'id' ? 'Bahasa' : 'Language' }}</span>
                        <a href="{{ route('locale.switch', 'id') }}" class="dropdown-item {{ app()->getLocale() === 'id' ? 'active' : '' }}">Bahasa Indonesia</a>
                        <a href="{{ route('locale.switch', 'en') }}" class="dropdown-item {{ app()->getLocale() === 'en' ? 'active' : '' }}">English</a>
                        <div class="dropdown-divider"></div>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="dropdown-item text-danger">{{ __('Log Out') }}</button>
                        </form>
                    </div>
                </div>
            @endauth
            @guest
                <div class="nav-item d-flex align-items-center gap-2">
                    <a href="{{ route('login') }}" class="btn">{{ __('Log in') }}</a>
                    <a href="{{ route('register') }}" class="btn btn-primary">{{ __('Register') }}</a>
                </div>
            @endguest
        </div>
        <div class="collapse navbar-collapse" id="navbar-menu">
            <div class="d-flex flex-column flex-md-row flex-fill align-items-stretch align-items-md-center">
                <ul class="navbar-nav">
                    @auth
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}" href="{{ route('dashboard') }}">
                                <span class="nav-link-title">{{ __('Dashboard') }}</span>
                            </a>
                        </li>
                    @endauth
                    <li class="nav-item">
                        <a class="nav-link" href="{{ url('/') }}"><span class="nav-link-title">{{ __('Home') }}</span></a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('blog.*') ? 'active' : '' }}" href="{{ route('blog.index') }}"><span class="nav-link-title">{{ __('Blog') }}</span></a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('services.*') ? 'active' : '' }}" href="{{ route('services.index') }}"><span class="nav-link-title">{{ __('Services') }}</span></a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('knowledge-base.*') ? 'active' : '' }}" href="{{ route('knowledge-base.index') }}"><span class="nav-link-title">{{ __('Knowledge Base') }}</span></a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="{{ url('/docs') }}"><span class="nav-link-title">{{ __('Docs') }}</span></a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('contact') ? 'active' : '' }}" href="{{ route('contact') }}"><span class="nav-link-title">{{ __('Contact') }}</span></a>
                    </li>
                </ul>
            </div>
        </div>
    </div>
</header>
