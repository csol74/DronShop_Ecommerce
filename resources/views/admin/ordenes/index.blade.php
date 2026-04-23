@extends('layouts.admin')
@section('title', 'Órdenes')

@section('content')
<div class="admin-page-header">
    <div>
        <h1 class="admin-page-title">Órdenes</h1>
        <div class="admin-page-sub">Gestión y seguimiento de pedidos</div>
    </div>
</div>

<!-- Stat pills -->
<div class="stat-pills">
    @foreach([
        ['val'=>'',           'label'=>'Todas',       'count'=> array_sum($stats)],
        ['val'=>'pendiente',  'label'=>'Pendientes',  'count'=>$stats['pendiente']],
        ['val'=>'pagado',     'label'=>'Pagadas',     'count'=>$stats['pagado']],
        ['val'=>'en_despacho','label'=>'En despacho', 'count'=>$stats['en_despacho']],
        ['val'=>'entregado',  'label'=>'Entregadas',  'count'=>$stats['entregado']],
        ['val'=>'cancelado',  'label'=>'Canceladas',  'count'=>$stats['cancelado']],
    ] as $pill)
        <a href="{{ route('admin.ordenes.index', array_merge(request()->except('estado'), ['estado'=>$pill['val']])) }}"
           class="stat-pill {{ request('estado') === $pill['val'] ? 'active' : '' }}">
            {{ $pill['label'] }}
            <span class="stat-pill__count">{{ $pill['count'] }}</span>
        </a>
    @endforeach
</div>

<div class="admin-table-wrap">
    <div class="admin-table-toolbar">
        <span class="admin-table-toolbar__title">{{ $ordenes->total() }} resultados</span>
        <form method="GET" class="admin-filters">
            @if(request('estado'))
                <input type="hidden" name="estado" value="{{ request('estado') }}">
            @endif
            <div class="admin-search">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                </svg>
                <input type="text" name="buscar" placeholder="Código o cliente..." value="{{ request('buscar') }}">
            </div>
            <select name="transporte" class="admin-select" onchange="this.form.submit()">
                <option value="">Todos los transportes</option>
                <option value="dron"  {{ request('transporte')==='dron'  ? 'selected':'' }}>🚁 Dron</option>
                <option value="moto"  {{ request('transporte')==='moto'  ? 'selected':'' }}>🏍️ Moto</option>
                <option value="carro" {{ request('transporte')==='carro' ? 'selected':'' }}>🚗 Carro</option>
            </select>
            <button type="submit" class="btn btn-ghost" style="padding:.5rem .85rem;font-size:.85rem">Buscar</button>
        </form>
    </div>

    <table class="admin-table">
        <thead>
            <tr>
                <th>Código</th>
                <th>Cliente</th>
                <th>Transporte</th>
                <th>Total</th>
                <th>Estado</th>
                <th>Fecha</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            @forelse($ordenes as $orden)
                @php $badge = $orden->estado_badge; @endphp
                <tr>
                    <td>
                        <span style="font-family:'Syne',sans-serif;font-weight:700;
                                     color:var(--text-primary);font-size:.85rem">
                            {{ $orden->codigo }}
                        </span>
                    </td>
                    <td>
                        <div style="font-size:.85rem;font-weight:500;color:var(--text-primary)">
                            {{ $orden->user->name ?? '—' }}
                        </div>
                        <div style="font-size:.75rem;color:var(--text-muted)">
                            {{ $orden->user->email ?? '' }}
                        </div>
                    </td>
                    <td>{{ $orden->transporte_icon }} {{ ucfirst($orden->transporte) }}</td>
                    <td>
                        <span style="color:var(--gold-400);font-family:'Syne',sans-serif;font-weight:700">
                            $ {{ number_format($orden->total, 0, ',', '.') }}
                        </span>
                    </td>
                    <td>
                        <span class="status-dot"
                              style="color:{{ $badge['color'] }};background:{{ $badge['bg'] }};
                                     border-color:{{ $badge['color'] }}40">
                            {{ $badge['label'] }}
                        </span>
                    </td>
                    <td style="font-size:.82rem;color:var(--text-muted)">
                        {{ $orden->created_at->format('d/m/Y') }}
                    </td>
                    <td>
                        <div class="action-group">
                            <a href="{{ route('admin.ordenes.show', $orden) }}" class="btn-action" title="Ver detalle">
                                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                          d="M15 12a3 3 0 11-6 0 3 3 0 016 0z M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                </svg>
                            </a>
                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" style="text-align:center;padding:3rem;color:var(--text-muted)">
                        No hay órdenes con estos filtros.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>

    @if($ordenes->hasPages())
        <div style="padding:1rem 1.25rem;border-top:1px solid var(--border)">
            {{ $ordenes->links('catalogo.pagination') }}
        </div>
    @endif
</div>
@endsection
