@extends('layouts.app')
@section('title', 'Orden ' . $orden->codigo)

@section('content')
<div class="order-page">

    @if(session('success'))
        <div class="flash flash-success">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="flash flash-error">{{ session('error') }}</div>
    @endif
    @if(session('info'))
        <div class="flash flash-info">{{ session('info') }}</div>
    @endif

    <!-- Header -->
    <div class="order-header">
        <div>
            <div class="order-code">{{ $orden->codigo }}</div>
            <div class="order-date">
                Creada el {{ $orden->created_at->format('d/m/Y H:i') }}
                · {{ $orden->transporte_icon }} {{ ucfirst($orden->transporte) }}
            </div>
        </div>
        <div style="display:flex;align-items:center;gap:1rem;flex-wrap:wrap">
            @php $badge = $orden->estado_badge; @endphp
            <span class="order-badge" style="color:{{ $badge['color'] }};
                         background:{{ $badge['bg'] }};border-color:{{ $badge['color'] }}40">
                ● {{ $badge['label'] }}
            </span>
            @if($orden->estado === 'pendiente')
                <a href="{{ route('orden.pago', $orden) }}" class="btn btn-gold">Pagar ahora →</a>
                <form method="POST" action="{{ route('orden.cancelar', $orden) }}">
                    @csrf
                    <button type="submit" class="btn btn-ghost"
                            onclick="return confirm('¿Cancelar esta orden?')"
                            style="color:#F87171;border-color:#7f1d1d">
                        Cancelar
                    </button>
                </form>
            @endif
            @if(in_array($orden->estado, ['pagado','en_despacho','entregado']) && $orden->seguimiento->count())
                <a href="{{ route('tracking.index', $orden) }}" class="btn btn-gold">
                    📍 Rastrear pedido en vivo
                </a>
@endif
        </div>
    </div>

    <!-- Productos -->
    <div class="order-section">
        <div class="order-section__head">Productos</div>
        @foreach($orden->items as $item)
            <div class="order-item">
                @if($item->producto)
                    <img src="{{ $item->producto->imagen }}" alt="{{ $item->nombre_producto }}">
                @else
                    <div style="width:56px;height:56px;background:var(--bg-surface);
                                border-radius:8px;display:grid;place-items:center;
                                border:1px solid var(--border);font-size:1.3rem">📦</div>
                @endif
                <div>
                    <div class="order-item__name">{{ $item->nombre_producto }}</div>
                    <div class="order-item__qty">
                        {{ $item->cantidad }} unidades × $ {{ number_format($item->precio_unitario, 0, ',', '.') }}
                    </div>
                </div>
                <div class="order-item__price">
                    $ {{ number_format($item->subtotal, 0, ',', '.') }}
                </div>
            </div>
        @endforeach
    </div>

    <!-- Info + Totales -->
    <div style="display:grid;grid-template-columns:1fr 1fr;gap:1.25rem">
        <div class="order-section">
            <div class="order-section__head">Información de entrega</div>
            <div style="padding:1.25rem;display:flex;flex-direction:column;gap:.6rem;
                        font-size:.875rem;color:var(--text-secondary)">
                <div>📍 <strong>Dirección:</strong> {{ $orden->direccion_entrega }}</div>
                <div>🌆 <strong>Ciudad:</strong> {{ $orden->ciudad }}</div>
                <div>{{ $orden->transporte_icon }} <strong>Transporte:</strong> {{ ucfirst($orden->transporte) }}</div>
                @if($orden->notas)
                    <div>📝 <strong>Notas:</strong> {{ $orden->notas }}</div>
                @endif
                @if($orden->mp_payment_id)
                    <div style="margin-top:.5rem;padding-top:.75rem;border-top:1px solid var(--border)">
                        <div>💳 <strong>Payment ID:</strong> {{ $orden->mp_payment_id }}</div>
                        <div>📊 <strong>MP Status:</strong> {{ $orden->mp_status }}</div>
                    </div>
                @endif
            </div>
        </div>

        <div class="order-section">
            <div class="order-section__head">Resumen de pago</div>
            <div style="padding:1.25rem;display:flex;flex-direction:column;gap:.6rem">
                <div class="summary-row">
                    <span>Subtotal</span>
                    <span class="val">$ {{ number_format($orden->subtotal, 0, ',', '.') }}</span>
                </div>
                <div class="summary-row">
                    <span>Envío</span>
                    <span class="val">$ {{ number_format($orden->costo_envio, 0, ',', '.') }}</span>
                </div>
                <div class="summary-row">
                    <span>IVA (19%)</span>
                    <span class="val">$ {{ number_format($orden->iva, 0, ',', '.') }}</span>
                </div>
                <div class="summary-row total">
                    <span>Total</span>
                    <span class="val">$ {{ number_format($orden->total, 0, ',', '.') }}</span>
                </div>
            </div>
        </div>
    </div>

    <div style="margin-top:1rem">
        <a href="{{ route('orden.historial') }}" style="color:var(--text-muted);font-size:.85rem">
            ← Volver a mis pedidos
        </a>
    </div>
</div>
@endsection
