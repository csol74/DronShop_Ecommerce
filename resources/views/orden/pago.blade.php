@extends('layouts.app')
@section('title', 'Pagar Orden')

@section('content')
<div class="payment-box">

    @if(session('error'))
        <div class="flash flash-error">{{ session('error') }}</div>
    @endif

    <div class="payment-logo">💳</div>
    <div class="payment-total">$ {{ number_format($orden->total, 0, ',', '.') }}</div>
    <div class="payment-order">Orden {{ $orden->codigo }}</div>

    <div class="payment-card">
        <div class="payment-card__row">
            <span class="lbl">Estado</span>
            <span class="val">
                @php $badge = $orden->estado_badge; @endphp
                <span style="color:{{ $badge['color'] }}">● {{ $badge['label'] }}</span>
            </span>
        </div>
        <div class="payment-card__row">
            <span class="lbl">Transporte</span>
            <span class="val">{{ $orden->transporte_icon }} {{ ucfirst($orden->transporte) }}</span>
        </div>
        <div class="payment-card__row">
            <span class="lbl">Entrega en</span>
            <span class="val">{{ $orden->direccion_entrega }}, {{ $orden->ciudad }}</span>
        </div>
        <div class="payment-card__row">
            <span class="lbl">Subtotal</span>
            <span class="val">$ {{ number_format($orden->subtotal, 0, ',', '.') }}</span>
        </div>
        <div class="payment-card__row">
            <span class="lbl">Envío</span>
            <span class="val">$ {{ number_format($orden->costo_envio, 0, ',', '.') }}</span>
        </div>
        <div class="payment-card__row">
            <span class="lbl">IVA</span>
            <span class="val">$ {{ number_format($orden->iva, 0, ',', '.') }}</span>
        </div>
    </div>

    @if($orden->estado === 'pendiente')
        <button id="btn-pagar" class="btn-mp" onclick="iniciarPago()">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor">
                <path d="M20 4H4c-1.11 0-2 .89-2 2v12c0 1.11.89 2 2 2h16c1.11 0 2-.89 2-2V6c0-1.11-.89-2-2-2zm0 14H4v-6h16v6zm0-10H4V6h16v2z"/>
            </svg>
            Pagar con MercadoPago
        </button>
        <p style="font-size:.78rem;color:var(--text-muted);margin-top:.75rem">
            🔒 Pago seguro — Sandbox de prueba
        </p>
    @else
        <div class="flash flash-success" style="margin-bottom:1rem">
            ✓ Esta orden ya fue {{ $orden->estado_badge['label'] }}
        </div>
        <a href="{{ route('orden.show', $orden) }}" class="btn btn-gold" style="justify-content:center;width:100%">
            Ver detalle de la orden →
        </a>
    @endif

    <a href="{{ route('orden.historial') }}"
       style="display:block;margin-top:1rem;color:var(--text-muted);font-size:.85rem">
        ← Ver mis pedidos
    </a>
</div>

@push('scripts')
<script>
async function iniciarPago() {
    const btn = document.getElementById('btn-pagar');
    btn.disabled = true;
    btn.innerHTML = '⏳ Generando enlace de pago...';

    try {
        const res = await fetch('{{ route("pago.preferencia", $orden) }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            }
        });

        const data = await res.json();

        if (data.sandbox_url) {
            // En sandbox usamos sandbox_init_point
            window.location.href = data.sandbox_url;
        } else {
            throw new Error(data.error || 'Error inesperado');
        }
    } catch (e) {
        btn.disabled = false;
        btn.innerHTML = '💳 Reintentar pago';
        alert('Error: ' + e.message);
    }
}
</script>
@endpush
@endsection
