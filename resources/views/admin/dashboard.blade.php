@extends('layouts.admin')
@section('title', 'Dashboard')

@section('content')
<div class="admin-page-header">
    <div>
        <h1 class="admin-page-title">Dashboard</h1>
        <div class="admin-page-sub">Resumen operativo de DronShop</div>
    </div>
    <div style="font-size:.82rem;color:var(--text-muted)">
        📅 {{ now()->format('d/m/Y H:i') }}
    </div>
</div>

<!-- KPIs -->
<div class="kpi-grid">
    <div class="kpi-card" style="--c1:#C9A84C;--c2:#F5C842">
        <div class="kpi-card__icon"></div>
        <div class="kpi-card__value">$ {{ number_format($totalVentas/1000000, 1) }}M</div>
        <div class="kpi-card__label">Ventas totales</div>
    </div>
    <div class="kpi-card" style="--c1:#22D3EE;--c2:#06B6D4">
        <div class="kpi-card__icon"></div>
        <div class="kpi-card__value">{{ $totalOrdenes }}</div>
        <div class="kpi-card__label">Órdenes totales</div>
    </div>
    <div class="kpi-card" style="--c1:#F59E0B;--c2:#FCD34D">
        <div class="kpi-card__icon"></div>
        <div class="kpi-card__value">{{ $ordenesPendientes }}</div>
        <div class="kpi-card__label">Pendientes de pago</div>
    </div>
    <div class="kpi-card" style="--c1:#818CF8;--c2:#A5B4FC">
        <div class="kpi-card__icon"></div>
        <div class="kpi-card__value">{{ $totalUsuarios }}</div>
        <div class="kpi-card__label">Clientes registrados</div>
    </div>
    <div class="kpi-card" style="--c1:#34D399;--c2:#6EE7B7">
        <div class="kpi-card__icon"></div>
        <div class="kpi-card__value">{{ $totalProductos }}</div>
        <div class="kpi-card__label">Productos en catálogo</div>
    </div>
    <div class="kpi-card" style="--c1:#F87171;--c2:#FCA5A5">
        <div class="kpi-card__icon"></div>
        <div class="kpi-card__value">{{ $stockBajo }}</div>
        <div class="kpi-card__label">Stock bajo alerta</div>
    </div>
</div>

<!-- Charts -->
<div class="charts-grid">
    <!-- Ventas por categoría -->
    <div class="chart-card">
        <div class="chart-card__title">Ventas por línea de producto</div>
        @php $maxVenta = $ventasPorCategoria->max('total') ?: 1; @endphp
        <div class="bar-chart">
            @forelse($ventasPorCategoria as $vc)
                <div class="bar-item">
                    <div class="bar-item__header">
                        <span class="bar-item__label">{{ $vc->nombre }}</span>
                        <span class="bar-item__val">$ {{ number_format($vc->total/1000000, 1) }}M</span>
                    </div>
                    <div class="bar-track">
                        <div class="bar-fill" style="width:{{ ($vc->total / $maxVenta) * 100 }}%"></div>
                    </div>
                </div>
            @empty
                <p style="color:var(--text-muted);font-size:.85rem">Sin ventas registradas aún.</p>
            @endforelse
        </div>
    </div>

    <!-- Ventas últimos 7 días -->
    <div class="chart-card">
        <div class="chart-card__title">Actividad últimos 7 días</div>
        @php $maxDia = $ventasSemana->max('total') ?: 1; @endphp
        <div class="bar-chart">
            @forelse($ventasSemana as $dia)
                <div class="bar-item">
                    <div class="bar-item__header">
                        <span class="bar-item__label">
                            {{ \Carbon\Carbon::parse($dia->fecha)->format('d/m') }}
                            <span style="color:var(--text-muted);font-size:.75rem">({{ $dia->cantidad }} órdenes)</span>
                        </span>
                        <span class="bar-item__val">$ {{ number_format($dia->total/1000, 0) }}K</span>
                    </div>
                    <div class="bar-track">
                        <div class="bar-fill" style="width:{{ ($dia->total / $maxDia) * 100 }}%;
                             background:linear-gradient(90deg,#06B6D4,#22D3EE)"></div>
                    </div>
                </div>
            @empty
                <p style="color:var(--text-muted);font-size:.85rem">Sin actividad en los últimos 7 días.</p>
            @endforelse
        </div>
    </div>
</div>

<!-- Bottom grid -->
<div style="display:grid;grid-template-columns:1fr 1fr;gap:1.5rem">

    <!-- Últimas órdenes -->
    <div class="admin-table-wrap">
        <div class="admin-table-toolbar">
            <span class="admin-table-toolbar__title">Últimas órdenes</span>
            <a href="{{ route('admin.ordenes.index') }}" class="btn btn-ghost" style="font-size:.8rem;padding:.4rem .8rem">
                Ver todas →
            </a>
        </div>
        <table class="admin-table">
            <thead>
                <tr>
                    <th>Código</th>
                    <th>Cliente</th>
                    <th>Total</th>
                    <th>Estado</th>
                </tr>
            </thead>
            <tbody>
                @foreach($ultimasOrdenes as $orden)
                    @php $badge = $orden->estado_badge; @endphp
                    <tr>
                        <td>
                            <a href="{{ route('admin.ordenes.show', $orden) }}"
                               style="font-family:'Syne',sans-serif;font-weight:700;
                                      color:var(--text-primary);font-size:.82rem">
                                {{ $orden->codigo }}
                            </a>
                        </td>
                        <td>{{ $orden->user->name ?? '—' }}</td>
                        <td style="color:var(--gold-400);font-family:'Syne',sans-serif;font-weight:700">
                            $ {{ number_format($orden->total/1000, 0) }}K
                        </td>
                        <td>
                            <span class="status-dot"
                                  style="color:{{ $badge['color'] }};background:{{ $badge['bg'] }};
                                         border-color:{{ $badge['color'] }}40">
                                {{ $badge['label'] }}
                            </span>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <!-- Stock bajo -->
    <div class="admin-table-wrap">
        <div class="admin-table-toolbar">
            <span class="admin-table-toolbar__title">⚠️ Alertas de stock</span>
            <a href="{{ route('admin.productos.index') }}" class="btn btn-ghost" style="font-size:.8rem;padding:.4rem .8rem">
                Gestionar →
            </a>
        </div>
        <table class="admin-table">
            <thead>
                <tr>
                    <th>Producto</th>
                    <th>Stock</th>
                    <th>Mínimo</th>
                </tr>
            </thead>
            <tbody>
                @forelse($productosBajoStock as $prod)
                    <tr>
                        <td>
                            <a href="{{ route('admin.productos.edit', $prod) }}"
                               style="color:var(--text-primary);font-weight:500;font-size:.85rem">
                                {{ $prod->nombre }}
                            </a>
                        </td>
                        <td>
                            <span style="color:#F87171;font-family:'Syne',sans-serif;font-weight:700">
                                {{ $prod->stock }}
                            </span>
                        </td>
                        <td style="color:var(--text-muted)">{{ $prod->stock_minimo }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="3" style="text-align:center;color:var(--text-muted);padding:2rem">
                            ✓ Todo el stock está en orden
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

@endsection
