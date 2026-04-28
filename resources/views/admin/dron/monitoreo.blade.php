@extends('layouts.admin')
@section('title', 'Monitoreo en Vivo')

@section('content')
<div class="admin-page-header">
    <div>
        <h1 class="admin-page-title">📡 Monitoreo en Tiempo Real</h1>
        <div class="admin-page-sub">Posición y estado del dron — simulado</div>
    </div>
    <div style="display:flex;align-items:center;gap:.5rem;font-size:.82rem;color:#4ade80">
        <span style="width:8px;height:8px;background:#4ade80;border-radius:50%;
                     display:inline-block;animation:pulse-green 1.5s infinite"></span>
        EN VIVO
    </div>
</div>

<div style="display:grid;grid-template-columns:1fr 340px;gap:1.5rem;align-items:start">

    <!-- Mapa grande -->
    <div>
        <div id="monitor-map" class="monitor-map"></div>
    </div>

    <!-- Panel lateral -->
    <div style="display:flex;flex-direction:column;gap:1.25rem">

        <!-- Estado dron -->
        <div class="admin-form-section">
            <div class="admin-form-section__head">🚁 DronShop Alpha-1</div>
            <div class="admin-form-section__body">
                @if($dron)
                    @php $badge = $dron->estado_badge; @endphp
                    <div class="tracking-info-row" style="margin-bottom:.6rem">
                        <span class="lbl">Estado</span>
                        <span class="status-dot" id="panel-estado"
                              style="color:{{ $badge['color'] }};background:{{ $badge['bg'] }};
                                     border-color:{{ $badge['color'] }}40;font-size:.75rem">
                            {{ $badge['label'] }}
                        </span>
                    </div>
                    <div class="tracking-info-row" style="margin-bottom:.6rem">
                        <span class="lbl">Batería</span>
                        <span class="val" id="panel-bateria">{{ $dron->bateria_actual_pct }}%</span>
                    </div>
                    <div class="battery-bar">
                        <div class="battery-fill" id="panel-battery-fill"
                             style="width:{{ $dron->bateria_actual_pct }}%;
                                    background:linear-gradient(90deg,#16a34a,#4ade80)"></div>
                    </div>
                    <div class="tracking-info-row" style="margin-top:.75rem">
                        <span class="lbl">Autonomía</span>
                        <span class="val">{{ $dron->autonomia_min }} min</span>
                    </div>
                    <div class="tracking-info-row">
                        <span class="lbl">Carga máx.</span>
                        <span class="val">{{ $dron->carga_max_kg }} kg</span>
                    </div>
                    <div class="tracking-info-row">
                        <span class="lbl">Vel. máx.</span>
                        <span class="val">{{ $dron->velocidad_max_kmh }} km/h</span>
                    </div>
                @endif
            </div>
        </div>

        <!-- Órdenes activas -->
        <div class="admin-form-section">
            <div class="admin-form-section__head">📦 Entregas activas (dron)</div>
            <div class="admin-form-section__body" style="padding:0">
                @forelse($ordenesActivas as $orden)
                    <div style="padding:1rem 1.25rem;border-bottom:1px solid var(--border)">
                        <div style="display:flex;justify-content:space-between;margin-bottom:.4rem">
                            <span style="font-family:'Syne',sans-serif;font-weight:700;
                                         color:var(--gold-400);font-size:.85rem">
                                {{ $orden->codigo }}
                            </span>
                            <span style="font-size:.75rem;color:var(--cyan-400)">En vuelo 🚁</span>
                        </div>
                        <div style="font-size:.78rem;color:var(--text-muted)">
                            {{ $orden->user->name }} · {{ $orden->ciudad }}
                        </div>
                        <!-- Avanzar tracking -->
                        <button onclick="avanzarTracking({{ $orden->id }})"
                                class="btn btn-ghost" style="width:100%;justify-content:center;
                                margin-top:.6rem;font-size:.8rem;padding:.4rem">
                            Avanzar siguiente paso →
                        </button>
                    </div>
                @empty
                    <div style="padding:1.5rem;text-align:center;color:var(--text-muted);font-size:.85rem">
                        Sin entregas activas por dron
                    </div>
                @endforelse
            </div>
        </div>

        <!-- Todas las órdenes en_despacho -->
        <div class="admin-form-section">
            <div class="admin-form-section__head">🚚 Gestionar entregas</div>
            <div class="admin-form-section__body">
                <p style="font-size:.82rem;color:var(--text-muted);margin-bottom:.75rem">
                    Avanza el estado de cualquier orden pagada para simular el flujo de entrega.
                </p>
                <a href="{{ route('admin.ordenes.index', ['estado'=>'pagado']) }}"
                   class="btn btn-ghost" style="width:100%;justify-content:center;font-size:.85rem">
                    Ver órdenes pagadas →
                </a>
            </div>
        </div>
    </div>
</div>

@push('scripts')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/leaflet.min.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/leaflet.min.js"></script>
    <script>
    const map = L.map('monitor-map').setView([7.1254, -73.1198], 13);

   L.tileLayer(
        `https://{s}-tiles.locationiq.com/v3/darkmatter/r/{z}/{x}/{y}.png?key={{ config('services.locationiq.key') }}`,
        { attribution: '©LocationIQ ©OpenStreetMap', subdomains: 'a', maxZoom: 19 }
    ).addTo(map);

    // Bodega
    L.marker([7.1254, -73.1198], {
        icon: L.divIcon({
            html: '<div style="background:#C9A84C;padding:4px 8px;border-radius:6px;color:#000;font-size:11px;font-weight:700;white-space:nowrap">🏭 DronShop HQ</div>',
            className: '', iconAnchor: [50, 20]
        })
    }).addTo(map);

    // Dron actual
    const dronIcon = L.divIcon({
        html: '<div style="font-size:28px;filter:drop-shadow(0 2px 6px #000)">🚁</div>',
        iconSize: [30, 30], className: '', iconAnchor: [15, 15]
    });

    let dronMarker = L.marker([{{ $dron?->lat_actual ?? 7.1254 }}, {{ $dron?->lng_actual ?? -73.1198 }}],
        {icon: dronIcon}).addTo(map).bindPopup('<b>DronShop Alpha-1</b>');

    // Polling posición
    setInterval(async () => {
        const res  = await fetch('{{ route("admin.dron.api.estado") }}');
        const data = await res.json();
        if (data.lat_actual && data.lng_actual) {
            dronMarker.setLatLng([data.lat_actual, data.lng_actual]);
        }
        document.getElementById('panel-bateria').textContent = data.bateria_actual_pct + '%';
        document.getElementById('panel-battery-fill').style.width = data.bateria_actual_pct + '%';
    }, 5000);

    // Avanzar tracking desde monitoreo
    async function avanzarTracking(ordenId) {
        const res = await fetch(`/admin/dron/tracking/${ordenId}/avanzar`, {
            method: 'POST',
            headers: {'X-CSRF-TOKEN': '{{ csrf_token() }}','Content-Type':'application/json'}
        });
        const data = await res.json();
        if (data.ok) {
            alert('✓ Paso avanzado: ' + data.nuevo_estado.replace(/_/g,' '));
            location.reload();
        } else {
            alert('Error: ' + (data.error || 'No se pudo avanzar'));
        }
    }
    // Polling órdenes activas cada 6s
    async function actualizarOrdenesActivas() {
        const res  = await fetch('{{ route("admin.dron.api.estado") }}');
        const data = await res.json();

        // Batería
        document.getElementById('panel-bateria').textContent    = data.bateria_actual_pct + '%';
        document.getElementById('panel-battery-fill').style.width = data.bateria_actual_pct + '%';

        // Estado dron
        document.getElementById('panel-estado').innerHTML =
            `<span style="color:${data.estado_badge.color}">● ${data.estado_badge.label}</span>`;

        // Mover dron en mapa si tiene posición actualizada
        if (data.lat_actual && data.lng_actual) {
            dronMarker.setLatLng([data.lat_actual, data.lng_actual]);
        }
    }

    setInterval(actualizarOrdenesActivas, 6000);
    </script>
@endpush
@endsection
