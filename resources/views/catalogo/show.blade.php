@extends('layouts.app')
@section('title', $producto->nombre)

@section('content')
<div class="product-detail">

    <!-- Breadcrumb -->
    <nav class="product-detail__breadcrumb">
        <a href="{{ route('catalogo.index') }}">Catálogo</a>
        <span>/</span>
        <a href="{{ route('catalogo.index', ['categoria' => $producto->categoria->slug]) }}">
            {{ $producto->categoria->nombre }}
        </a>
        <span>/</span>
        <span style="color:var(--text-secondary)">{{ $producto->nombre }}</span>
    </nav>

    <!-- Grid principal -->
    <div class="product-detail__grid">

        <!-- Imagen -->
        <div>
            <div class="product-detail__img-wrap">
                <img src="{{ $producto->imagen }}" alt="{{ $producto->nombre }}">
                <div class="product-detail__img-overlay"></div>
            </div>
        </div>

        <!-- Info -->
        <div class="product-detail__info">
            <div class="product-detail__category">
                ⚡ {{ $producto->categoria->nombre }}
            </div>

            <h1 class="product-detail__name">{{ $producto->nombre }}</h1>

            <div class="product-detail__price">
                {{ $producto->precio_formateado }}
            </div>

            @if($producto->stock > 0)
                <div class="stock-badge in-stock">
                    ✓ En stock — {{ $producto->stock }} unidades disponibles
                </div>
            @else
                <div class="stock-badge low-stock">✕ Agotado</div>
            @endif

            <p class="product-detail__desc">{{ $producto->descripcion }}</p>

            <!-- Características -->
            @if($producto->caracteristicas)
                <div>
                    <h3 style="font-size:1rem;margin-bottom:.85rem;color:var(--text-secondary);
                               font-family:'Syne',sans-serif;font-weight:600;">
                        Especificaciones técnicas
                    </h3>
                    <div class="specs-grid">
                        @foreach($producto->caracteristicas as $key => $val)
                            <div class="spec-item">
                                <div class="spec-item__key">{{ $key }}</div>
                                <div class="spec-item__val">{{ $val }}</div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            <!-- Proveedor -->
            <div>
                <h3 style="font-size:.8rem;text-transform:uppercase;letter-spacing:.1em;
                           color:var(--text-muted);margin-bottom:.75rem;">
                    Proveedor
                </h3>
                <div class="proveedor-card">
                    <div class="proveedor-card__avatar">
                        <img src="{{ $producto->proveedor->logo }}" alt="{{ $producto->proveedor->empresa }}">
                    </div>
                    <div>
                        <div class="proveedor-card__empresa">{{ $producto->proveedor->empresa }}</div>
                        <div class="proveedor-card__name">{{ $producto->proveedor->nombre }}</div>
                        <div class="proveedor-card__meta">
                            <span>📧 {{ $producto->proveedor->email }}</span>
                            <span>📞 {{ $producto->proveedor->telefono }}</span>
                            <span>🌎 {{ $producto->proveedor->pais }}</span>
                        </div>
                    </div>
                </div>
                <p style="font-size:.8rem;color:var(--text-muted);margin-top:.75rem;line-height:1.5">
                    {{ $producto->proveedor->descripcion }}
                </p>
            </div>

            <!-- Acción -->
            @auth
                <form method="POST" action="{{ route('carrito.agregar', $producto) }}" style="margin:0">
                    @csrf
                    <input type="hidden" name="cantidad" value="1">
                    <button type="submit" class="btn btn-gold" style="padding:1rem 2rem;font-size:1rem;margin-top:.5rem">
                        🛒 Agregar al carrito
                    </button>
                </form>
                @if(session('success'))
                    <div class="flash flash-success">{{ session('success') }}</div>
                @endif
            @else
                <a href="{{ route('login') }}" class="btn btn-gold" style="padding:1rem 2rem;font-size:1rem;margin-top:.5rem">
                    Inicia sesión para comprar →
                </a>
            @endauth
        </div>
    </div>

    <!-- Productos relacionados -->
    @if($relacionados->count())
        <div style="margin-top:4rem">
            <h2 style="font-size:1.5rem;margin-bottom:1.5rem">
                Más de <span style="color:var(--gold-400)">{{ $producto->categoria->nombre }}</span>
            </h2>
            <div class="products-grid">
                @foreach($relacionados as $rel)
                    <a href="{{ route('catalogo.show', $rel->slug) }}" class="product-card">
                        <div class="product-card__img-wrap">
                            <img src="{{ $rel->imagen }}" alt="{{ $rel->nombre }}" loading="lazy">
                            <div class="product-card__badge">{{ $rel->categoria->nombre }}</div>
                        </div>
                        <div class="product-card__body">
                            <div class="product-card__category">{{ $rel->categoria->nombre }}</div>
                            <div class="product-card__name">{{ $rel->nombre }}</div>
                            <div class="product-card__desc">{{ $rel->descripcion }}</div>
                        </div>
                        <div class="product-card__footer">
                            <div class="product-card__price">{{ $rel->precio_formateado }}</div>
                            <div class="product-card__stock">{{ $rel->stock }} uds.</div>
                        </div>
                    </a>
                @endforeach
            </div>
        </div>
    @endif

</div>
@endsection
