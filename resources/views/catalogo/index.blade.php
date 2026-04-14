@extends('layouts.app')
@section('title', 'Catálogo')

@section('content')

<!-- Hero -->
<section class="catalog-hero">
    <div class="catalog-hero__eyebrow">
        <span>⚡</span> Entregas más rápidas 
    </div>
    <h1 class="catalog-hero__title">Descubre lo mejor<br>en tecnología, deporte y moda</h1>
    <p class="catalog-hero__sub">Productos premium seleccionados de proveedores certificados. Entrega express por drones.</p>

    <!-- Category Tabs -->
    <div class="cat-tabs">
        <a href="{{ route('catalogo.index') }}"
           class="cat-tab {{ !request('categoria') ? 'active' : '' }}">
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M4 6h16M4 10h16M4 14h16M4 18h16"/>
            </svg>
            Todos
        </a>
        <a href="{{ route('catalogo.index', ['categoria' => 'electronica']) }}"
           class="cat-tab {{ request('categoria') === 'electronica' ? 'active' : '' }}">
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M9 3H5a2 2 0 00-2 2v4m6-6h10a2 2 0 012 2v4M9 3v18m0 0h10a2 2 0 002-2V9M9 21H5a2 2 0 01-2-2V9m0 0h18"/>
            </svg>
            Electrónica
        </a>
        <a href="{{ route('catalogo.index', ['categoria' => 'deporte']) }}"
           class="cat-tab {{ request('categoria') === 'deporte' ? 'active' : '' }}">
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M13 10V3L4 14h7v7l9-11h-7z"/>
            </svg>
            Deporte
        </a>
        <a href="{{ route('catalogo.index', ['categoria' => 'ropa']) }}"
           class="cat-tab {{ request('categoria') === 'ropa' ? 'active' : '' }}">
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/>
            </svg>
            Ropa
        </a>
    </div>
</section>

<!-- Layout principal -->
<div class="catalog-layout">

    <!-- Sidebar filtros -->
    <aside class="filter-sidebar">
        <div class="filter-sidebar__title">
            <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5"
                      d="M3 4h18M7 8h10M11 12h2M9 16h6"/>
            </svg>
            Filtros
        </div>

        <form method="GET" action="{{ route('catalogo.index') }}">
            <!-- Categoría -->
            <div class="filter-section">
                <div class="filter-section__label">Categoría</div>
                @foreach($categorias as $cat)
                    <label class="filter-check">
                        <input type="radio" name="categoria" value="{{ $cat->slug }}"
                               {{ request('categoria') === $cat->slug ? 'checked' : '' }}
                               onchange="this.form.submit()">
                        <span class="filter-check__box"></span>
                        {{ $cat->nombre }}
                    </label>
                @endforeach
                <label class="filter-check" style="margin-top:.5rem">
                    <input type="radio" name="categoria" value=""
                           {{ !request('categoria') ? 'checked' : '' }}
                           onchange="this.form.submit()">
                    <span class="filter-check__box"></span>
                    Todas las categorías
                </label>
            </div>

            <!-- Precio -->
            <div class="filter-section">
                <div class="filter-section__label">Rango de precio</div>
                <div class="filter-range">
                    <input type="number" name="precio_min" class="filter-input"
                           placeholder="Mín $" value="{{ request('precio_min') }}">
                    <input type="number" name="precio_max" class="filter-input"
                           placeholder="Máx $" value="{{ request('precio_max') }}">
                </div>
            </div>

            <!-- Disponibilidad -->
            <div class="filter-section">
                <div class="filter-section__label">Disponibilidad</div>
                <label class="filter-check">
                    <input type="checkbox" name="disponible" value="1"
                           {{ request('disponible') ? 'checked' : '' }}>
                    <span class="filter-check__box"></span>
                    Solo disponibles
                </label>
            </div>

            <!-- Búsqueda -->
            @if(request('buscar'))
                <input type="hidden" name="buscar" value="{{ request('buscar') }}">
            @endif

            <button type="submit" class="btn btn-gold btn-apply-filter">
                Aplicar filtros
            </button>

            @if(request()->hasAny(['categoria', 'precio_min', 'precio_max', 'disponible', 'buscar']))
                <a href="{{ route('catalogo.index') }}"
                   class="btn btn-ghost btn-apply-filter" style="margin-top:.5rem">
                    Limpiar
                </a>
            @endif
        </form>
    </aside>

    <!-- Productos -->
    <div>
        <div class="products-header">
            <div class="products-header__count">
                <strong>{{ $productos->total() }}</strong> productos encontrados
                @if(request('buscar'))
                    para "<em>{{ request('buscar') }}</em>"
                @endif
            </div>
        </div>

        <div class="products-grid">
            @forelse($productos as $producto)
                <a href="{{ route('catalogo.show', $producto->slug) }}" class="product-card">
                    <div class="product-card__img-wrap">
                        <img src="{{ $producto->imagen }}" alt="{{ $producto->nombre }}" loading="lazy">
                        <div class="product-card__badge">{{ $producto->categoria->nombre }}</div>
                        <div class="product-card__quick-view">
                            <svg width="12" height="12" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M15 12a3 3 0 11-6 0 3 3 0 016 0z M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                            </svg>
                            Ver detalle
                        </div>
                    </div>
                    <div class="product-card__body">
                        <div class="product-card__category">{{ $producto->categoria->nombre }}</div>
                        <div class="product-card__name">{{ $producto->nombre }}</div>
                        <div class="product-card__desc">{{ $producto->descripcion }}</div>
                    </div>
                    <div class="product-card__footer">
                        <div class="product-card__price">{{ $producto->precio_formateado }}</div>
                        <div class="product-card__stock {{ $producto->stock < 10 ? 'low' : '' }}">
                            {{ $producto->stock > 0 ? $producto->stock . ' disponibles' : 'Agotado' }}
                        </div>
                    </div>
                </a>
            @empty
                <div class="empty-state">
                    <div class="empty-state__icon">🔍</div>
                    <h3>No encontramos productos</h3>
                    <p>Intenta cambiar los filtros o el término de búsqueda</p>
                </div>
            @endforelse
        </div>

        <!-- Paginación -->
        @if($productos->hasPages())
            <div class="ds-pagination">
                {{ $productos->links('catalogo.pagination') }}
            </div>
        @endif
    </div>
</div>

@endsection
