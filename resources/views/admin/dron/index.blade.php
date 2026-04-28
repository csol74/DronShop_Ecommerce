@extends('layouts.admin')
@section('title', 'Gestión del Dron')

@section('content')
<div class="admin-page-header">
    <div>
        <h1 class="admin-page-title">🚁 Módulo de Dron</h1>
        <div class="admin-page-sub">Ficha técnica, vuelos y operaciones</div>
    </div>
    <div style="display:flex;gap:.75rem">
        <a href="{{ route('admin.dron.monitoreo') }}" class="btn btn-gold">📡 Monitoreo en vivo</a>
        <a href="{{ route('admin.mantenimiento.index') }}" class="btn btn-ghost">🔧 Mantenimiento</a>
        <a href="{{ route('admin.dron.editar') }}" class="btn btn-ghost">✏️ Editar ficha</a>
    </div>
</div>

@if($dron)
<!-- Estado en tiempo real -->
<div style="background:var(--bg-card);border:1px solid var(--border);border-radius:var(--radius-lg);
            padding:1.5rem;margin-bottom:1.5rem;display:flex;align-items:center;gap:2rem;flex-wrap:wrap">
    <div style="font-size:3.5rem">🚁</div>
    <div style="flex:1">
        <div style="font-family:'Syne',sans-serif;font-size:1.3rem;font-weight:800;margin-bottom:.25rem">
            {{ $dron->nombre }}
        </div>
        <div style="font-size:.85rem;color:var(--text-muted)">
            {{ $dron->modelo }} · {{ $dron->numero_serie }}
        </div>
    </div>
    @php $badge = $dron->estado_badge; @endphp
    <div style="text-align:center">
        <div class="status-dot" style="color:{{ $badge['color'] }};background:{{ $badge['bg'] }};
             border-color:{{ $badge['color'] }}40;font-size:.9rem;padding:.5rem 1.25rem"
             id="dron-estado">
            ● {{ $badge['label'] }}
        </div>
    </div>
    <div style="text-align:center;min-width:120px">
        <div style="font-size:.72rem;text-transform:uppercase;letter-spacing:.1em;
                    color:var(--text-muted);margin-bottom:.4rem">Batería</div>
        <div style="font-family:'Syne',sans-serif;font-size:1.6rem;font-weight:800;
                    color:{{ $dron->bateria_actual_pct > 40 ? '#4ade80' : '#F87171' }}"
             id="dron-bateria">
            {{ $dron->bateria_actual_pct }}%
        </div>
        <div class="battery-bar" style="margin-top:.4rem">
            <div class="battery-fill" id="battery-fill"
                 style="width:{{ $dron->bateria_actual_pct }}%;
                        background:{{ $dron->bateria_actual_pct > 40 ? 'linear-gradient(90deg,#16a34a,#4ade80)' : 'linear-gradient(90deg,#dc2626,#F87171)' }}">
            </div>
        </div>
    </div>
</div>

<!-- Ficha técnica -->
<div class="dron-ficha">
    @foreach([
        ['label'=>'Fabricante',         'val'=> $dron->fabricante],
        ['label'=>'Fecha adquisición',  'val'=> $dron->fecha_adquisicion->format('d/m/Y')],
        ['label'=>'Autonomía batería',  'val'=> $dron->autonomia_min . ' minutos'],
        ['label'=>'Velocidad máxima',   'val'=> $dron->velocidad_max_kmh . ' km/h'],
        ['label'=>'Alcance máximo',     'val'=> $dron->alcance_max_km . ' km'],
        ['label'=>'Carga máxima',       'val'=> $dron->carga_max_kg . ' kg'],
        ['label'=>'Batería mínima op.', 'val'=> $dron->bateria_minima_pct . '%'],
        ['label'=>'Total vuelos',        'val'=> $dron->vuelos->count() . ' misiones'],
    ] as $spec)
        <div class="dron-stat">
            <div class="dron-stat__label">{{ $spec['label'] }}</div>
            <div class="dron-stat__value">{{ $spec['val'] }}</div>
        </div>
    @endforeach
</div>

<!-- Restricciones operativas -->
<div style="display:grid;grid-template-columns:1fr 1fr;gap:1.5rem;margin-bottom:1.5rem">
    <div class="admin-form-section">
        <div class="admin-form-section__head">🚫 Condiciones limitantes</div>
        <div class="admin-form-section__body">
            @if($dron->condiciones_climaticas)
                @foreach($dron->condiciones_climaticas as $key => $val)
                    <div style="display:flex;justify-content:space-between;padding:.4rem 0;
                                border-bottom:1px solid var(--border);font-size:.875rem">
                        <span style="color:var(--text-muted)">{{ str_replace('_', ' ', ucfirst($key)) }}</span>
                        <span style="color:var(--text-primary);font-weight:500">
                            {{ is_bool($val) ? ($val ? '✓ Sí' : '✗ No') : $val }}
                        </span>
                    </div>
                @endforeach
            @endif
        </div>
    </div>
    <div class="admin-form-section">
        <div class="admin-form-section__head">📍 Zonas de vuelo permitidas</div>
        <div class="admin-form-section__body">
            @if($dron->zonas_permitidas)
                @foreach($dron->zonas_permitidas as $zona)
                    <div style="background:var(--bg-surface);border:1px solid var(--border);
                                border-radius:var(--radius-sm);padding:.75rem;margin-bottom:.5rem">
                        <div style="font-weight:600;font-size:.875rem;margin-bottom:.2rem">
                            📍 {{ $zona['nombre'] }}
                        </div>
                        <div style="font-size:.78rem;color:var(--text-muted)">
                            Radio: {{ $zona['radio_km'] }}km ·
                            Lat: {{ $zona['lat'] }} / Lng: {{ $zona['lng'] }}
                        </div>
                    </div>
                @endforeach
            @endif
        </div>
    </div>
</div>

<!-- Historial de vuelos -->
<div class="admin-table-wrap">
    <div class="admin-table-toolbar">
        <span class="admin-table-toolbar__title">📋 Historial de vuelos</span>
    </div>
    <table class="admin-table">
        <thead>
            <tr>
                <th>Misión</th>
                <th>Orden</th>
                <th>Despegue</th>
                <th>Aterrizaje</th>
                <th>Carga</th>
                <th>Estado</th>
            </tr>
        </thead>
        <tbody>
            @forelse($historialVuelos as $vuelo)
                <tr>
                    <td style="font-family:'Syne',sans-serif;font-weight:700;color:var(--text-primary)">
                        #{{ $vuelo->id }}
                    </td>
                    <td>
                        <a href="{{ route('admin.ordenes.show', $vuelo->orden) }}"
                           style="color:var(--gold-400)">{{ $vuelo->orden->codigo }}</a>
                    </td>
                    <td style="font-size:.82rem">
                        {{ $vuelo->hora_despegue?->format('d/m/Y H:i') ?? '—' }}
                    </td>
                    <td style="font-size:.82rem">
                        {{ $vuelo->hora_aterrizaje?->format('d/m/Y H:i') ?? 'En vuelo...' }}
                    </td>
                    <td>{{ $vuelo->carga_kg }} kg</td>
                    <td>
                        @php
                            $vc = match($vuelo->estado_mision) {
                                'en_vuelo'   => ['color'=>'#60A5FA','bg'=>'#0c1a3550'],
                                'completado' => ['color'=>'#4ade80','bg'=>'#05261640'],
                                'fallido'    => ['color'=>'#F87171','bg'=>'#2d0a0a50'],
                                default      => ['color'=>'#94A3B8','bg'=>'#1E2D45'],
                            };
                        @endphp
                        <span class="status-dot"
                              style="color:{{ $vc['color'] }};background:{{ $vc['bg'] }};
                                     border-color:{{ $vc['color'] }}40">
                            {{ ucfirst($vuelo->estado_mision) }}
                        </span>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" style="text-align:center;padding:2rem;color:var(--text-muted)">
                        Sin vuelos registrados aún.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>
@else
    <div class="flash flash-error">No hay dron registrado. Ejecuta el seeder.</div>
@endif

@push('scripts')
<script>
// Polling estado dron
setInterval(async () => {
    const res  = await fetch('{{ route("admin.dron.api.estado") }}');
    const data = await res.json();
    document.getElementById('dron-estado').innerHTML = '● ' + data.estado_badge.label;
    document.getElementById('dron-bateria').textContent = data.bateria_actual_pct + '%';
    document.getElementById('battery-fill').style.width = data.bateria_actual_pct + '%';
}, 8000);
</script>
@endpush
@endsection
