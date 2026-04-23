<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin — @yield('title', 'Panel') | DronShop</title>
    <link rel="stylesheet" href="{{ asset('css/dronshop.css') }}">
    @stack('styles')
</head>
<body>

@php
    $pendientes = \App\Models\Orden::where('estado','pendiente')->count();
    $stockBajo  = \App\Models\Producto::whereColumn('stock','<=','stock_minimo')->where('activo',true)->count();
@endphp

<!-- Navbar admin -->
<nav class="ds-navbar">
    <a href="{{ route('admin.dashboard') }}" class="ds-navbar__brand">
        <div class="ds-navbar__brand-icon">⚙️</div>
        Dron<span>Shop</span>
        <span style="font-size:.7rem;background:var(--gold-500);color:#0a0a0a;
                     padding:.15rem .5rem;border-radius:4px;font-weight:700;
                     margin-left:.25rem;font-family:'DM Sans',sans-serif">ADMIN</span>
    </a>

    <ul class="ds-navbar__nav">
        <li><a href="{{ route('catalogo.index') }}" target="_blank"
               style="font-size:.82rem">👁 Ver tienda</a></li>
    </ul>

    <div class="ds-navbar__actions">
        <span style="font-size:.82rem;color:var(--text-muted)">
            {{ auth()->user()->name }}
        </span>
        <form method="POST" action="{{ route('logout') }}" style="margin:0">
            @csrf
            <button type="submit" class="btn btn-ghost" style="font-size:.82rem">Salir</button>
        </form>
    </div>
</nav>

<!-- Layout principal -->
<div class="admin-layout">

    <!-- Sidebar -->
    <aside class="admin-sidebar">
        <div class="admin-sidebar__section">Principal</div>

        <a href="{{ route('admin.dashboard') }}"
           class="admin-nav-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
            </svg>
            Dashboard
        </a>

        <div class="admin-sidebar__section">Catálogo</div>

        <a href="{{ route('admin.productos.index') }}"
           class="admin-nav-link {{ request()->routeIs('admin.productos.*') ? 'active' : '' }}">
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
            </svg>
            Productos
            @if($stockBajo > 0)
                <span class="admin-nav-link__badge">{{ $stockBajo }}</span>
            @endif
        </a>

        <a href="{{ route('admin.proveedores.index') }}"
           class="admin-nav-link {{ request()->routeIs('admin.proveedores.*') ? 'active' : '' }}">
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
            </svg>
            Proveedores
        </a>

        <div class="admin-sidebar__section">Ventas</div>

        <a href="{{ route('admin.ordenes.index') }}"
           class="admin-nav-link {{ request()->routeIs('admin.ordenes.*') ? 'active' : '' }}">
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
            </svg>
            Órdenes
            @if($pendientes > 0)
                <span class="admin-nav-link__badge">{{ $pendientes }}</span>
            @endif
        </a>

        <div class="admin-sidebar__section">Usuarios</div>

        <a href="{{ route('admin.usuarios.index') }}"
           class="admin-nav-link {{ request()->routeIs('admin.usuarios.*') ? 'active' : '' }}">
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
            </svg>
            Usuarios
        </a>
    </aside>

    <!-- Contenido -->
    <main class="admin-main">
        @if(session('success'))
            <div class="flash flash-success" style="margin-bottom:1.5rem">✓ {{ session('success') }}</div>
        @endif
        @if(session('error'))
            <div class="flash flash-error" style="margin-bottom:1.5rem">✕ {{ session('error') }}</div>
        @endif

        @yield('content')
    </main>
</div>

@stack('scripts')
</body>
</html>
