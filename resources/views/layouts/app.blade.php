<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'DronShop') — E-Commerce Premium</title>
    <link rel="stylesheet" href="{{ asset('css/dronshop.css') }}">
    @stack('styles')
</head>
<body>

<!-- ═══════════ NAVBAR ═══════════ -->
<nav class="ds-navbar">
    <a href="{{ route('catalogo.index') }}" class="ds-navbar__brand">
        <div class="ds-navbar__brand-icon">
            <img src="{{ asset('img/dronshop_logo.jpeg') }}" alt="DronShop Logo">
        </div>
        Dron<span>Shop</span>
    </a>

    <form method="GET" action="{{ route('catalogo.index') }}" class="ds-navbar__search">
        <svg class="ds-navbar__search-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
        </svg>
        <input type="text" name="buscar" placeholder="Buscar productos..."
               value="{{ request('buscar') }}">
    </form>

    <ul class="ds-navbar__nav">
        <li><a href="{{ route('catalogo.index') }}"
               class="{{ request()->routeIs('catalogo.*') ? 'active' : '' }}">Catálogo</a></li>
        <li><a href="{{ route('catalogo.index', ['categoria' => 'electronica']) }}">Electrónica</a></li>
        <li><a href="{{ route('catalogo.index', ['categoria' => 'deporte']) }}">Deporte</a></li>
        <li><a href="{{ route('catalogo.index', ['categoria' => 'ropa']) }}">Ropa</a></li>
    </ul>

    <div class="ds-navbar__actions">
        @auth
            @if(auth()->user()->role === 'admin')
                <a href="{{ route('admin.dashboard') }}" class="btn btn-ghost" style="font-size:.8rem">
                    ⚙️ Admin
                </a>
            @endif
            <form method="POST" action="{{ route('logout') }}" style="margin:0">
                @csrf
                <button type="submit" class="btn btn-ghost">Salir</button>
            </form>
        @else
            <a href="{{ route('login') }}" class="btn btn-ghost">Ingresar</a>
            <a href="{{ route('register') }}" class="btn btn-gold">Registrarse</a>
        @endauth
    </div>
</nav>

<!-- ═══════════ CONTENT ═══════════ -->
<main>
    @yield('content')
</main>

<!-- ═══════════ FOOTER ═══════════ -->
<footer class="ds-footer">
    <div class="ds-footer__grid">
        <div class="ds-footer__col">
            <div class="ds-footer__brand-name">Dron<span>Shop</span></div>
            <p class="ds-footer__tagline">
                Tu marketplace premium con entrega inteligente por drones, moto y carro.
                Tecnología, deporte y moda en un solo lugar.
            </p>
            
        </div>
        <div class="ds-footer__col">
            <div class="ds-footer__col-title">Líneas</div>
            <ul>
                <li><a href="{{ route('catalogo.index', ['categoria' => 'electronica']) }}">Electrónica</a></li>
                <li><a href="{{ route('catalogo.index', ['categoria' => 'deporte']) }}">Deporte</a></li>
                <li><a href="{{ route('catalogo.index', ['categoria' => 'ropa']) }}">Ropa</a></li>
            </ul>
        </div>
        <div class="ds-footer__col">
            <div class="ds-footer__col-title">Empresa</div>
            <ul>
                <li><a href="#">Sobre nosotros</a></li>
                <li><a href="#">Proveedores</a></li>
                <li><a href="#">Transportistas</a></li>
            </ul>
        </div>
        <div class="ds-footer__col">
            <div class="ds-footer__col-title">Soporte</div>
            <ul>
                <li><a href="#">Rastrear pedido</a></li>
                <li><a href="#">Contacto</a></li>
                <li><a href="#">Política de envío</a></li>
            </ul>
        </div>
    </div>
    <div class="ds-footer__bottom">
        <span>© {{ date('Y') }} DronShop. Todos los derechos reservados.</span>
        <span>Hecho con 🚀 en Colombia</span>
    </div>
</footer>

@stack('scripts')
</body>
</html>
