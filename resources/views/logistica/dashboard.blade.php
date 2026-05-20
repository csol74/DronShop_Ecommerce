@extends('layouts.logistica')
@section('title', 'Mis Entregas')

@section('content')
<div class="admin-page-header">
    <div>
        <h1 class="admin-page-title">Panel de Logística</h1>
        <div class="admin-page-sub">Gestiona tus entregas en tiempo real</div>
    </div>
</div>

<div class="kpi-grid" style="margin-bottom:2rem">
    <div class="kpi-card" style="--c1:#22D3EE;--c2:#06B6D4">
        <div class="kpi-card__icon">🚚</div>
        <div class="kpi-card__value">{{ $enProceso }}</div>
        <div class="kpi-card__label">En proceso</div>
    </div>
    <div class="kpi-card" style="--c1:#4ade80;--c2:#34D399">
        <div class="kpi-card__icon">✅</div>
        <div class="kpi-card__value">{{ $entregadas }}</div>
        <div class="kpi-card__label">Entregadas hoy</div>
    </div>
    <div class="kpi-card" style="--c1:#F59E0B;--c2:#FCD34D">
        <div class="kpi-card__icon">📦</div>
        <div class="kpi-card__value">{{ $disponibles->count() }}</div>
        <div class="kpi-card__label">Disponibles para tomar</div>
    </div>
</div>

@if($misOrdenes->count())
    <div class="flash flash-info" style="margin-bottom:1.5rem;
         background:#0c1a3560;border-color:#2563eb60;color:#93C5FD">
        🔔 Tienes <strong>{{ $misOrdenes->count() }}</strong>
        {{ $misOrdenes->count() === 1 ? 'orden asignada' : 'órdenes asignadas' }}
        para gestionar.
    </div>
@endif

<div class="admin-table-wrap" style="margin-bottom:2rem">
    <div class="admin-table-toolbar">
        <span class="admin-table-toolbar__title">
            📋 Mis órdenes asignadas ({{ $misOrdenes->count() }})
        </span>
    </div>
    <table class="admin-table">
        <thead>
            <tr>
                <th>Orden</th>
                <th>Cliente</th>
                <th>Transporte</th>
                <th>Dirección</th>
                <th>Estado entrega</th>
                <th>Acción</th>
            </tr>
        </thead>
        <tbody>
            @forelse($misOrdenes as $orden)
                @php
                    $badge = $orden->estado_badge;
                @endphp
                <tr>
                    <td>
                        <span style="font-family:'Syne',sans-serif;font-weight:700;
                                     color:var(--gold-400)">
                            {{ $orden->codigo }}
                        </span>
                    </td>
                    <td>
                        <div style="font-size:.85rem;font-weight:500;
                                    color:var(--text-primary)">
                            {{ $orden->user->name ?? 'Cliente General' }}
                        </div>
                        <div style="font-size:.75rem;color:var(--text-muted)">
                            {{ $orden->user->email ?? '' }}
                        </div>
                    </td>
                    <td>
                        {{ $orden->transporte_icon }} {{ ucfirst($orden->transporte) }}
                    </td>
                    <td style="font-size:.82rem;max-width:180px">
                        {{ $orden->direccion_entrega }}, {{ $orden->ciudad }}
                    </td>
                    <td>
                        @php
                            // Buscamos dinámicamente cuál es el paso actual activo (no completado) de la ruta
                            $pasoActivo = $orden->seguimiento->sortBy('id')->firstWhere('completado', false);

                            // Si hay un paso pendiente en curso, usamos su título. Si ya se completó todo, usamos el estado maestro de la orden
                            $estadoMostrar = $pasoActivo
                                ? $pasoActivo->titulo
                                : str_replace('_', ' ', ucfirst($orden->estado_entrega));
                        @endphp
                        <span class="status-dot"
                            style="color:{{ $badge['color'] ?? 'var(--cyan-400)' }};
                                   background:{{ $badge['bg'] ?? '#06B6D415' }};
                                   border-color:{{ $badge['color'] ?? 'var(--cyan-400)' }}40;
                                   font-size:.75rem">
                            ● {{ $estadoMostrar }}
                        </span>
                    </td>
                    <td>
                        <a href="{{ route('logistica.entrega.show', $orden) }}"
                           class="btn btn-gold"
                           style="padding:.4rem .85rem;font-size:.82rem">
                            Gestionar →
                        </a>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="6"
                        style="text-align:center;padding:2.5rem;color:var(--text-muted)">
                        No tienes órdenes asignadas en este momento.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection
