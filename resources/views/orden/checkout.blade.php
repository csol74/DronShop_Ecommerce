    @extends('layouts.app')
@section('title', 'Checkout')

@section('content')
<div class="checkout-page">
    <h1 style="font-size:1.8rem;margin-bottom:.4rem">Confirmar pedido</h1>
    <p style="color:var(--text-muted);margin-bottom:2rem;font-size:.9rem">
        Paso final antes de proceder al pago
    </p>

    @if($errors->any())
        <div class="flash flash-error">{{ $errors->first() }}</div>
    @endif

    <form method="POST" action="{{ route('orden.store') }}">
        @csrf
        <div class="checkout-grid">
            <div>
                <!-- Dirección -->
                <div class="checkout-section">
                    <div class="checkout-section__title">Datos de entrega</div>

                    <div class="form-row" style="margin-bottom:1rem">
                        <div class="form-group">
                            <label class="form-label">Dirección completa *</label>
                            <input type="text" name="direccion_entrega" class="form-control"
                                   placeholder="Calle 45 # 12-34, Apto 502"
                                   value="{{ old('direccion_entrega') }}" required>
                            @error('direccion_entrega')<div class="form-error">{{ $message }}</div>@enderror
                        </div>
                        <div class="form-group">
                            <label class="form-label">Ciudad *</label>
                            <input type="text" name="ciudad" class="form-control"
                                   placeholder="Bucaramanga"
                                   value="{{ old('ciudad', 'Bucaramanga') }}" required>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Notas adicionales (opcional)</label>
                        <textarea name="notas" class="form-control" rows="2"
                                  placeholder="Instrucciones especiales para la entrega...">{{ old('notas') }}</textarea>
                    </div>
                </div>

                <!-- Transporte -->
                <div class="checkout-section">
                    <div class="checkout-section__title">Modalidad de transporte</div>
                    <div style="display:flex;flex-direction:column;gap:.5rem">
                        @foreach([
                            ['val'=>'dron',  'icon'=>'🚁', 'nombre'=>'Dron Express', 'desc'=>'Entrega en 2 horas', 'precio'=>15000],
                            ['val'=>'moto',  'icon'=>'🏍️', 'nombre'=>'Moto Rápido',  'desc'=>'Entrega en 4 horas', 'precio'=>8000],
                            ['val'=>'carro', 'icon'=>'🚗', 'nombre'=>'Carro Seguro', 'desc'=>'Entrega en 6 horas', 'precio'=>12000],
                        ] as $op)
                            <label class="transport-opt">
                                <input type="radio" name="transporte" value="{{ $op['val'] }}"
                                       {{ $transporte === $op['val'] ? 'checked' : '' }} required>
                                <div class="transport-opt__left">
                                    <span class="transport-opt__icon">{{ $op['icon'] }}</span>
                                    <div>
                                        <div class="transport-opt__name">{{ $op['nombre'] }}</div>
                                        <div class="transport-opt__desc">{{ $op['desc'] }}</div>
                                    </div>
                                </div>
                                <div class="transport-opt__price">$ {{ number_format($op['precio'], 0, ',', '.') }}</div>
                            </label>
                        @endforeach
                    </div>
                </div>

                <!-- Productos -->
                <div class="checkout-section">
                    <div class="checkout-section__title">Productos ({{ $items->count() }})</div>
                    <div class="checkout-items-mini">
                        @foreach($items as $item)
                            <div class="checkout-item-mini">
                                <img src="{{ $item->producto->imagen }}" alt="{{ $item->producto->nombre }}">
                                <div class="checkout-item-mini__name">
                                    {{ $item->producto->nombre }}
                                    <span style="color:var(--text-muted);font-size:.78rem;display:block">
                                        x{{ $item->cantidad }}
                                    </span>
                                </div>
                                <div class="checkout-item-mini__sub">
                                    $ {{ number_format($item->cantidad * $item->producto->precio, 0, ',', '.') }}
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>

            <!-- Resumen fijo -->
            <div>
                <div class="cart-summary" style="position:sticky;top:90px">
                    <div class="cart-summary__title">Resumen final</div>

                    <div style="display:flex;flex-direction:column;gap:.6rem">
                        <div class="summary-row">
                            <span>Subtotal</span>
                            <span class="val">$ {{ number_format($subtotal, 0, ',', '.') }}</span>
                        </div>
                        <div class="summary-row">
                            <span>Envío</span>
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

                    <button type="submit" class="btn btn-gold" style="justify-content:center;padding:.9rem;font-size:1rem">
                        Confirmar y pagar →
                    </button>

                    <a href="{{ route('carrito.index') }}"
                       style="display:block;text-align:center;font-size:.82rem;color:var(--text-muted);margin-top:.5rem">
                        ← Volver al carrito
                    </a>
                </div>
            </div>
        </div>
    </form>
</div>
@endsection
