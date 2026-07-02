<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config('app.name', 'DataBridge') }}</title>
    <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-slate-100 text-slate-950 antialiased">
    <div class="app-shell" data-sidebar-shell>
        <aside class="sidebar">
            <div class="brand-block">
                <div class="brand-mark">DB</div>
                <div class="brand-text">
                    <strong>DataBridge</strong>
                    <span>VFP Sync Panel</span>
                </div>
            </div>

            <nav class="sidebar-nav" aria-label="Main navigation">
                <a href="{{ route('dashboard') }}" class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                    <span class="nav-icon">D</span>
                    <span class="nav-label">Dashboard</span>
                </a>
                <a href="{{ route('sales.register') }}" class="nav-link {{ request()->routeIs('sales.*') ? 'active' : '' }}">
                    <span class="nav-icon">S</span>
                    <span class="nav-label">Sale Register</span>
                </a>
            </nav>
        </aside>

        <div class="main-panel">
            <header class="topbar">
                <button class="icon-button" type="button" data-sidebar-toggle aria-label="Toggle sidebar">
                    <span></span>
                    <span></span>
                    <span></span>
                </button>

                <div class="topbar-title">
                    <span>Live Transfer Workspace</span>
                    <strong>{{ $title ?? 'Dashboard' }}</strong>
                </div>

                <div class="user-chip">
                    <span>{{ auth()->user()->user_code }}</span>
                    <strong>{{ auth()->user()->name }}</strong>
                </div>

                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button class="logout-button" type="submit">Logout</button>
                </form>
            </header>

            <main class="content-area">
                {{ $slot }}
            </main>
        </div>
    </div>
</body>
</html>
