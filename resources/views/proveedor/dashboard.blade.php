@extends('layouts.proveedor')
@section('title', 'Dashboard')

@section('content')
<div class="admin-page-header">
    <div>
        <h1 class="admin-page-title">Bienvenido, {{ $proveedor->nombre }}</h1>
        <div class="admin-page-sub">{{ $proveedor->empresa }} · Panel de proveedor</div>
    </div>
    <a href="{{ route('proveedor.productos.create') }}" class="btn btn-gold">+ Nuevo producto</a>
</div>

<!-- KPIs -->
<div class="kpi-grid" style="margin-bottom:2rem">
    <div class="kpi-card" style="--c1:#C9A84C;--c2:#F5C842">
        <div class="kpi-card__icon">💰</div>
        <div class="kpi-card__value">$ {{ number_format($totalVentas/1000000, 1) }}M</div>
        <div class="kpi-card__label">Mis ventas totales</div>
    </div>
    <div class="kpi-card" style="--c1:#22D3EE;--c2:#06B6D4">
        <div class="kpi-card__icon">🏷️</div>
        <div class="kpi-card__value">{{ $totalProductos }}</div>
        <div class="kpi-card__label">Productos publicados</div>
    </div>
    <div class="kpi-card" style="--c1:#4ade80;--c2:#34D399">
        <div class="kpi-card__icon">✅</div>
        <div class="kpi-card__value">{{ $productosActivos }}</div>
        <div class="kpi-card__label">Productos activos</div>
    </div>
    <div class="kpi-card" style="--c1:#F87171;--c2:#FCA5A5">
        <div class="kpi-card__icon">⚠️</div>
        <div class="kpi-card__value">{{ $stockBajo }}</div>
        <div class="kpi-card__label">Stock bajo alerta</div>
    </div>
</div>

<div style="display:grid;grid-template-columns:1fr 1fr;gap:1.5rem">

    <!-- Top productos -->
    <div class="admin-table-wrap">
        <div class="admin-table-toolbar">
            <span class="admin-table-toolbar__title">🏆 Mis productos más vendidos</span>
        </div>
        <table class="admin-table">
            <thead>
                <tr>
                    <th>Producto</th>
                    <th>Unidades</th>
                    <th>Total</th>
                </tr>
            </thead>
            <tbody>
                @forelse($topProductos as $prod)
                    <tr>
                        <td>
                            <div style="display:flex;align-items:center;gap:.65rem">
                                <img src="{{ $prod->imagen }}" class="tbl-img">
                                <span style="font-size:.85rem;font-weight:500;
                                             color:var(--text-primary)">{{ $prod->nombre }}</span>
                            </div>
                        </td>
                        <td style="font-weight:700;color:var(--text-primary)">
                            {{ $prod->unidades }}
                        </td>
                        <td style="color:var(--gold-400);font-family:'Syne',sans-serif;font-weight:700">
                            $ {{ number_format($prod->total, 0, ',', '.') }}
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="3" style="text-align:center;padding:2rem;color:var(--text-muted)">
                            Sin ventas aún
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Stock bajo -->
    <div class="admin-table-wrap">
        <div class="admin-table-toolbar">
            <span class="admin-table-toolbar__title">⚠️ Stock bajo alerta</span>
            <a href="{{ route('proveedor.productos.index') }}"
               class="btn btn-ghost" style="font-size:.8rem;padding:.4rem .8rem">
                Ver todos →
            </a>
        </div>
        <table class="admin-table">
            <thead>
                <tr><th>Producto</th><th>Stock</th><th>Mínimo</th><th></th></tr>
            </thead>
            <tbody>
                @forelse($productosStockBajo as $prod)
                    <tr>
                        <td style="font-size:.85rem;color:var(--text-primary);font-weight:500">
                            {{ $prod->nombre }}
                        </td>
                        <td style="color:#F87171;font-weight:700">{{ $prod->stock }}</td>
                        <td style="color:var(--text-muted)">{{ $prod->stock_minimo }}</td>
                        <td>
                            <a href="{{ route('proveedor.productos.edit', $prod) }}"
                               class="btn-action" title="Actualizar stock">
                                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                          d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                </svg>
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" style="text-align:center;padding:2rem;color:var(--text-muted)">
                            ✓ Todo el stock en orden
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
