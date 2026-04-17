@extends('layouts.app')
@section('title', 'Mi Carrito')

@section('content')
<div class="cart-page">
    <h1 style="font-size:1.8rem;margin-bottom:.4rem">Mi Carrito</h1>
    <p style="color:var(--text-muted);margin-bottom:2rem;font-size:.9rem">
        {{ $items->count() }} {{ $items->count() === 1 ? 'producto' : 'productos' }}
    </p>

    @if(session('success'))
        <div class="flash flash-success">✓ {{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="flash flash-error">✕ {{ session('error') }}</div>
    @endif

    @if($items->isEmpty())
        <div style="text-align:center;padding:4rem 2rem;background:var(--bg-card);
                    border:1px solid var(--border);border-radius:var(--radius-lg)">
            <div style="font-size:3rem;margin-bottom:1rem;opacity:.4">🛒</div>
            <h3 style="font-size:1.2rem;margin-bottom:.5rem">Tu carrito está vacío</h3>
            <p style="color:var(--text-muted);margin-bottom:1.5rem">Agrega productos desde el catálogo</p>
            <a href="{{ route('catalogo.index') }}" class="btn btn-gold">Ir al catálogo →</a>
        </div>
    @else
        <div class="cart-grid">
            <!-- Tabla productos -->
            <div>
                <div class="cart-table">
                    <div class="cart-table__head">
                        <span>Producto</span>
                        <span>Precio</span>
                        <span>Cantidad</span>
                        <span>Subtotal</span>
                        <span></span>
                    </div>

                    @foreach($items as $item)
                        <div class="cart-item">
                            <!-- Producto -->
                            <div class="cart-item__product">
                                <img src="{{ $item->producto->imagen }}"
                                     alt="{{ $item->producto->nombre }}" class="cart-item__img">
                                <div>
                                    <div class="cart-item__name">{{ $item->producto->nombre }}</div>
                                    <div class="cart-item__cat">{{ $item->producto->categoria->nombre }}</div>
                                </div>
                            </div>

                            <!-- Precio -->
                            <div class="cart-item__price">
                                $ {{ number_format($item->producto->precio, 0, ',', '.') }}
                            </div>

                            <!-- Cantidad -->
                            <div>
                                <form method="POST" action="{{ route('carrito.actualizar', $item) }}">
                                    @csrf @method('PATCH')
                                    <div class="qty-control">
                                        <button type="button" class="qty-btn"
                                                onclick="this.nextElementSibling.stepDown();this.closest('form').submit()">−</button>
                                        <input type="number" name="cantidad" class="qty-input"
                                               value="{{ $item->cantidad }}" min="1"
                                               max="{{ $item->producto->stock }}"
                                               onchange="this.closest('form').submit()">
                                        <button type="button" class="qty-btn"
                                                onclick="this.previousElementSibling.stepUp();this.closest('form').submit()">+</button>
                                    </div>
                                </form>
                            </div>

                            <!-- Subtotal -->
                            <div class="cart-item__subtotal">
                                $ {{ number_format($item->cantidad * $item->producto->precio, 0, ',', '.') }}
                            </div>

                            <!-- Eliminar -->
                            <div>
                                <form method="POST" action="{{ route('carrito.eliminar', $item) }}">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="btn-remove" title="Eliminar">
                                        <svg width="15" height="15" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                  d="M6 18L18 6M6 6l12 12"/>
                                        </svg>
                                    </button>
                                </form>
                            </div>
                        </div>
                    @endforeach
                </div>

                <!-- Vaciar -->
                <div style="margin-top:.75rem;display:flex;justify-content:space-between;align-items:center">
                    <a href="{{ route('catalogo.index') }}"
                       style="font-size:.85rem;color:var(--text-muted)">← Seguir comprando</a>
                    <form method="POST" action="{{ route('carrito.vaciar') }}">
                        @csrf @method('DELETE')
                        <button type="submit" class="btn btn-ghost"
                                onclick="return confirm('¿Vaciar carrito?')"
                                style="font-size:.82rem;color:var(--text-muted)">
                            Vaciar carrito
                        </button>
                    </form>
                </div>
            </div>

            <!-- Panel resumen -->
            <div class="cart-summary">
                <div class="cart-summary__title">Resumen del pedido</div>

                <!-- Selector de transporte -->
                <div>
                    <div style="font-size:.78rem;font-weight:600;text-transform:uppercase;
                                letter-spacing:.1em;color:var(--text-muted);margin-bottom:.75rem">
                        Modalidad de entrega
                    </div>
                    <form method="POST" action="{{ route('carrito.transporte') }}" id="transporteForm">
                        @csrf
                        <div class="transport-selector">
                            @foreach([
                                ['val'=>'dron',  'icon'=>'🚁', 'nombre'=>'Dron Express', 'desc'=>'Entrega en 2h', 'precio'=>'$ 15.000'],
                                ['val'=>'moto',  'icon'=>'🏍️', 'nombre'=>'Moto Rápido',  'desc'=>'Entrega en 4h', 'precio'=>'$ 8.000'],
                                ['val'=>'carro', 'icon'=>'🚗', 'nombre'=>'Carro Seguro', 'desc'=>'Entrega en 6h', 'precio'=>'$ 12.000'],
                            ] as $op)
                                <label class="transport-opt">
                                    <input type="radio" name="transporte" value="{{ $op['val'] }}"
                                           {{ session('transporte', 'moto') === $op['val'] ? 'checked' : '' }}
                                           onchange="document.getElementById('transporteForm').submit()">
                                    <div class="transport-opt__left">
                                        <span class="transport-opt__icon">{{ $op['icon'] }}</span>
                                        <div>
                                            <div class="transport-opt__name">{{ $op['nombre'] }}</div>
                                            <div class="transport-opt__desc">{{ $op['desc'] }}</div>
                                        </div>
                                    </div>
                                    <div class="transport-opt__price">{{ $op['precio'] }}</div>
                                </label>
                            @endforeach
                        </div>
                    </form>
                </div>

                <!-- Totales -->
                <div style="display:flex;flex-direction:column;gap:.6rem">
                    <div class="summary-row">
                        <span>Subtotal</span>
                        <span class="val">$ {{ number_format($subtotal, 0, ',', '.') }}</span>
                    </div>
                    <div class="summary-row">
                        <span>Envío ({{ ucfirst(session('transporte', 'moto')) }})</span>
                        <span class="val">$ {{ number_format($costoEnvio, 0, ',', '.') }}</span>
                    </div>
                    <div class="summary-row">
                        <span>IVA (19%)</span>
                        <span class="val">$ {{ number_format($iva, 0, ',', '.') }}</span>
                    </div>
                    <div class="summary-row total">
                        <span>Total</span>
                        <span class="val">$ {{ number_format($total, 0, ',', '.') }}</span>
                    </div>
                </div>

                <a href="{{ route('orden.checkout') }}" class="btn btn-gold" style="justify-content:center;padding:.9rem">
                    Proceder al checkout →
                </a>
            </div>
        </div>
    @endif
</div>
@endsection
