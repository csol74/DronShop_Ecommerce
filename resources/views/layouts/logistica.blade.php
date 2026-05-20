<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Logística — @yield('title') | DronShop</title>
    <link rel="stylesheet" href="{{ asset('css/dronshop.css') }}">
    @stack('styles')
</head>
<body>

<nav class="ds-navbar">
    <a href="{{ route('logistica.dashboard') }}" class="ds-navbar__brand">
        <div class="ds-navbar__brand-icon">🚚</div>
        Dron<span>Shop</span>
        <span style="font-size:.7rem;background:#06B6D4;color:#000;
                     padding:.15rem .5rem;border-radius:4px;font-weight:700;
                     margin-left:.25rem;font-family:'DM Sans',sans-serif">LOGÍSTICA</span>
    </a>
    <div class="ds-navbar__actions">
        <span style="font-size:.82rem;color:var(--text-muted)">{{ auth()->user()->name }}</span>
        <form method="POST" action="{{ route('logout') }}" style="margin:0">
            @csrf
            <button type="submit" class="btn btn-ghost" style="font-size:.82rem">Salir</button>
        </form>
    </div>
</nav>

<div class="admin-layout">
    <aside class="admin-sidebar">
        <div class="admin-sidebar__section">Panel</div>
        <a href="{{ route('logistica.dashboard') }}"
           class="admin-nav-link {{ request()->routeIs('logistica.dashboard') ? 'active' : '' }}">
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
            </svg>
            Mis Entregas
        </a>
        <div class="admin-sidebar__section">Info</div>
        <div style="padding:.75rem;font-size:.78rem;color:var(--text-muted);line-height:1.6">
            <div style="color:var(--cyan-400);font-weight:600;margin-bottom:.3rem">
                {{ auth()->user()->name }}
            </div>
            <div>{{ auth()->user()->email }}</div>
            <div style="margin-top:.5rem;color:var(--text-muted)">Rol: Logística</div>
        </div>
    </aside>

    <main class="admin-main">
        @if(session('success'))
            <div class="flash flash-success" style="margin-bottom:1.5rem">{{ session('success') }}</div>
        @endif
        @if(session('error'))
            <div class="flash flash-error" style="margin-bottom:1.5rem">{{ session('error') }}</div>
        @endif
        @yield('content')
    </main>
</div>

@stack('scripts')
</body>
</html>
