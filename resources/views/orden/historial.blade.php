@extends('layouts.app')
@section('title', 'Mis Pedidos')

@section('content')
<div class="order-page">
    <h1 style="font-size:1.8rem;margin-bottom:.4rem">Mis Pedidos</h1>
    <p style="color:var(--text-muted);margin-bottom:2rem;font-size:.9rem">
        Historial completo de tus órdenes
    </p>

    @if($ordenes->isEmpty())
        <div style="text-align:center;padding:4rem 2rem;background:var(--bg-card);
                    border:1px solid var(--border);border-radius:var(--radius-lg)">
            <div style="font-size:3rem;margin-bottom:1rem;opacity:.4">📋</div>
            <h3 style="font-size:1.2rem;margin-bottom:.5rem">Aún no tienes pedidos</h3>
            <p style="color:var(--text-muted);margin-bottom:1.5rem">Explora el catálogo y realiza tu primera compra</p>
            <a href="{{ route('catalogo.index') }}" class="btn btn-gold">Ver catálogo →</a>
        </div>
    @else
        <div class="order-section">
            <table class="orders-table">
                <thead>
                    <tr>
                        <th>Orden</th>
                        <th>Fecha</th>
                        <th>Productos</th>
                        <th>Transporte</th>
                        <th>Total</th>
                        <th>Estado</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($ordenes as $orden)
                        @php $badge = $orden->estado_badge; @endphp
                        <tr>
                            <td>
                                <span style="font-family:'Syne',sans-serif;font-weight:700;
                                             color:var(--text-primary)">{{ $orden->codigo }}</span>
                            </td>
                            <td>{{ $orden->created_at->format('d/m/Y') }}</td>
                            <td>{{ $orden->items->count() }} item(s)</td>
                            <td>{{ $orden->transporte_icon }} {{ ucfirst($orden->transporte) }}</td>
                            <td>
                                <span style="font-family:'Syne',sans-serif;font-weight:700;
                                             color:var(--gold-400)">
                                    $ {{ number_format($orden->total, 0, ',', '.') }}
                                </span>
                            </td>
                            <td>
                                <span class="order-badge" style="color:{{ $badge['color'] }};
                                     background:{{ $badge['bg'] }};border-color:{{ $badge['color'] }}40;
                                     font-size:.75rem;padding:.3rem .75rem">
                                    {{ $badge['label'] }}
                                </span>
                            </td>
                            <td>
                                <div style="display:flex;gap:.5rem">
                                    <a href="{{ route('orden.show', $orden) }}" class="btn btn-ghost"
                                       style="padding:.4rem .75rem;font-size:.8rem">Ver</a>
                                    @if($orden->estado === 'pendiente')
                                        <a href="{{ route('orden.pago', $orden) }}" class="btn btn-gold"
                                           style="padding:.4rem .75rem;font-size:.8rem">Pagar</a>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        @if($ordenes->hasPages())
            <div class="ds-pagination" style="margin-top:1.5rem">
                {{ $ordenes->links('catalogo.pagination') }}
            </div>
        @endif
    @endif
</div>
@endsection
