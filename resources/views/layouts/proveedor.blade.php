<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Proveedor — @yield('title') | DronShop</title>
    <link rel="stylesheet" href="{{ asset('css/dronshop.css') }}">
    @stack('styles')
</head>
<body>

@php
    $proveedor     = auth()->user()->proveedor;
    $stockBajo     = $proveedor ? $proveedor->productos()->whereColumn('stock','<=','stock_minimo')->where('activo',true)->count() : 0;
@endphp

<!-- Navbar -->
<nav class="ds-navbar">
    <a href="{{ route('proveedor.dashboard') }}" class="ds-navbar__brand">
        <div class="ds-navbar__brand-icon">🤝</div>
        Dron<span>Shop</span>
        <span style="font-size:.7rem;background:#818CF8;color:#fff;
                     padding:.15rem .5rem;border-radius:4px;font-weight:700;
                     margin-left:.25rem;font-family:'DM Sans',sans-serif">PROVEEDOR</span>
    </a>

    <ul class="ds-navbar__nav">
        <li>
            <a href="{{ route('catalogo.index') }}" target="_blank" style="font-size:.82rem">
                👁 Ver tienda
            </a>
        </li>
    </ul>

    <div class="ds-navbar__actions">
        @if($proveedor)
            <span style="font-size:.82rem;color:var(--text-muted)">
                {{ $proveedor->empresa }}
            </span>
        @endif
        <form method="POST" action="{{ route('logout') }}" style="margin:0">
            @csrf
            <button type="submit" class="btn btn-ghost" style="font-size:.82rem">Salir</button>
        </form>
    </div>
</nav>

<!-- Layout -->
<div class="admin-layout">

    <!-- Sidebar -->
    <aside class="admin-sidebar">
        <div class="admin-sidebar__section">Mi Panel</div>

        <a href="{{ route('proveedor.dashboard') }}"
           class="admin-nav-link {{ request()->routeIs('proveedor.dashboard') ? 'active' : '' }}">
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
            </svg>
            Dashboard
        </a>

        <div class="admin-sidebar__section">Catálogo</div>

        <a href="{{ route('proveedor.productos.index') }}"
           class="admin-nav-link {{ request()->routeIs('proveedor.productos.*') ? 'active' : '' }}">
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
            </svg>
            Mis Productos
            @if($stockBajo > 0)
                <span class="admin-nav-link__badge">{{ $stockBajo }}</span>
            @endif
        </a>

        <a href="{{ route('proveedor.productos.create') }}"
           class="admin-nav-link">
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M12 4v16m8-8H4"/>
            </svg>
            Agregar Producto
        </a>

        <!-- Info proveedor -->
        @if($proveedor)
            <div style="margin-top:auto;padding:1rem .75rem;
                        border-top:1px solid var(--border);margin-top:2rem">
                <div style="font-size:.72rem;color:var(--text-muted);
                            text-transform:uppercase;letter-spacing:.08em;margin-bottom:.6rem">
                    Mi cuenta
                </div>
                <div style="font-size:.82rem;color:var(--text-primary);font-weight:600">
                    {{ auth()->user()->name }}
                </div>
                <div style="font-size:.78rem;color:var(--text-muted)">{{ $proveedor->email }}</div>
                <div style="font-size:.78rem;color:var(--cyan-400);margin-top:.25rem">
                    {{ $proveedor->pais }}
                </div>
            </div>
        @endif
    </aside>

    <!-- Contenido -->
    <main class="admin-main">
        @if(session('success'))
            <div class="flash flash-success" style="margin-bottom:1.5rem">
                {{ session('success') }}
            </div>
        @endif
        @if(session('error'))
            <div class="flash flash-error" style="margin-bottom:1.5rem">
                {{ session('error') }}
            </div>
        @endif

        @yield('content')
    </main>
</div>

@stack('scripts')
</body>
</html>
