@extends('layouts.admin')
@section('title', 'Orden ' . $orden->codigo)

@section('content')
<div class="admin-page-header">
    <div>
        <h1 class="admin-page-title">{{ $orden->codigo }}</h1>
        <div class="admin-page-sub">{{ $orden->created_at->format('d/m/Y H:i') }}</div>
    </div>
    <a href="{{ route('admin.ordenes.index') }}" class="btn btn-ghost">← Volver</a>
</div>

@php $badge = $orden->estado_badge; @endphp

<div style="display:grid;grid-template-columns:1fr 340px;gap:1.5rem;align-items:start">
    <div>
        <!-- Productos -->
        <div class="admin-form-section">
            <div class="admin-form-section__head">📦 Productos ({{ $orden->items->count() }})</div>
            <div class="admin-form-section__body" style="padding:0">
                @foreach($orden->items as $item)
                    <div style="display:flex;align-items:center;gap:1rem;padding:1rem 1.5rem;
                                border-bottom:1px solid var(--border)">
                        @if($item->producto)
                            <img src="{{ $item->producto->imagen }}" style="width:52px;height:52px;
                                 border-radius:8px;object-fit:cover;border:1px solid var(--border)">
                        @endif
                        <div style="flex:1">
                            <div style="font-weight:600;color:var(--text-primary)">{{ $item->nombre_producto }}</div>
                            <div style="font-size:.8rem;color:var(--text-muted)">
                                x{{ $item->cantidad }} × $ {{ number_format($item->precio_unitario, 0, ',', '.') }}
                            </div>
                        </div>
                        <div style="font-family:'Syne',sans-serif;font-weight:700;color:var(--gold-400)">
                            $ {{ number_format($item->subtotal, 0, ',', '.') }}
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        <!-- Info cliente -->
        <div class="admin-form-section">
            <div class="admin-form-section__head">👤 Cliente</div>
            <div class="admin-form-section__body" style="display:grid;grid-template-columns:1fr 1fr;gap:1rem;font-size:.875rem">
                <div>
                    <div style="color:var(--text-muted);font-size:.75rem;margin-bottom:.2rem">Nombre</div>
                    <div style="color:var(--text-primary);font-weight:500">{{ $orden->user->name }}</div>
                </div>
                <div>
                    <div style="color:var(--text-muted);font-size:.75rem;margin-bottom:.2rem">Email</div>
                    <div style="color:var(--text-primary)">{{ $orden->user->email }}</div>
                </div>
                <div>
                    <div style="color:var(--text-muted);font-size:.75rem;margin-bottom:.2rem">Dirección</div>
                    <div style="color:var(--text-primary)">{{ $orden->direccion_entrega }}</div>
                </div>
                <div>
                    <div style="color:var(--text-muted);font-size:.75rem;margin-bottom:.2rem">Ciudad</div>
                    <div style="color:var(--text-primary)">{{ $orden->ciudad }}</div>
                </div>
                @if($orden->notas)
                    <div style="grid-column:1/-1">
                        <div style="color:var(--text-muted);font-size:.75rem;margin-bottom:.2rem">Notas</div>
                        <div style="color:var(--text-primary)">{{ $orden->notas }}</div>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Panel lateral -->
    <div>
        <!-- Resumen -->
        <div class="admin-form-section" style="margin-bottom:1.25rem">
            <div class="admin-form-section__head">💰 Resumen de pago</div>
            <div class="admin-form-section__body">
                <div class="summary-row" style="margin-bottom:.4rem">
                    <span>Subtotal</span>
                    <span class="val">$ {{ number_format($orden->subtotal, 0, ',', '.') }}</span>
                </div>
                <div class="summary-row" style="margin-bottom:.4rem">
                    <span>Envío ({{ ucfirst($orden->transporte) }})</span>
                    <span class="val">$ {{ number_format($orden->costo_envio, 0, ',', '.') }}</span>
                </div>
                <div class="summary-row" style="margin-bottom:.75rem">
                    <span>IVA 19%</span>
                    <span class="val">$ {{ number_format($orden->iva, 0, ',', '.') }}</span>
                </div>
                <div class="summary-row total">
                    <span>Total</span>
                    <span class="val">$ {{ number_format($orden->total, 0, ',', '.') }}</span>
                </div>

                @if($orden->mp_payment_id)
                    <div style="margin-top:1rem;padding-top:1rem;border-top:1px solid var(--border);
                                font-size:.8rem;color:var(--text-muted)">
                        <div>💳 Payment ID: {{ $orden->mp_payment_id }}</div>
                        <div>📊 MP Status: {{ $orden->mp_status }}</div>
                    </div>
                @endif
            </div>
        </div>

        <!-- Cambiar estado -->
        <div class="admin-form-section">
            <div class="admin-form-section__head">🔄 Gestión de estado</div>
            <div class="admin-form-section__body">
                <div style="margin-bottom:1rem">
                    <div style="font-size:.78rem;color:var(--text-muted);margin-bottom:.5rem">Estado actual</div>
                    <span class="status-dot"
                          style="color:{{ $badge['color'] }};background:{{ $badge['bg'] }};
                                 border-color:{{ $badge['color'] }}40">
                        ● {{ $badge['label'] }}
                    </span>
                </div>

                <form method="POST" action="{{ route('admin.ordenes.estado', $orden) }}">
                    @csrf @method('PATCH')
                    <div class="form-group" style="margin-bottom:.75rem">
                        <label class="form-label">Cambiar a</label>
                        <select name="estado" class="form-control admin-select" style="width:100%">
                            @foreach(['pendiente','pagado','en_despacho','entregado','cancelado'] as $est)
                                <option value="{{ $est }}" {{ $orden->estado === $est ? 'selected' : '' }}>
                                    {{ ucfirst(str_replace('_', ' ', $est)) }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <button type="submit" class="btn btn-gold" style="width:100%;justify-content:center">
                        Actualizar estado
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
