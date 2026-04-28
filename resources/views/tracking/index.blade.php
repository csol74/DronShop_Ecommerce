@extends('layouts.app')
@section('title', 'Seguimiento — ' . $orden->codigo)

@section('content')
<div class="tracking-page">

    <!-- Header -->
    <div class="tracking-header">
        <div style="display:flex;align-items:center;gap:1.25rem">
            <div class="tracking-header__icon">{{ $orden->transporte_icon }}</div>
            <div>
                <div class="tracking-header__transport">
                    Entrega por {{ ucfirst($orden->transporte) }}
                </div>
                <div class="tracking-header__code">{{ $orden->codigo }}</div>
                <div style="font-size:.82rem;color:var(--text-muted)">
                    {{ $orden->direccion_entrega }}, {{ $orden->ciudad }}
                </div>
            </div>
        </div>
        <div class="tracking-header__eta">
            <div class="tracking-header__eta-label">Tiempo estimado</div>
            <div class="tracking-header__eta-val">
                @switch($orden->transporte)
                    @case('dron')  ~2 horas @break
                    @case('moto')  ~4 horas @break
                    @case('carro') ~6 horas @break
                @endswitch
            </div>
            @php $badge = $orden->estado_badge; @endphp
            <div style="margin-top:.5rem">
                <span class="status-dot"
                      style="color:{{ $badge['color'] }};background:{{ $badge['bg'] }};
                             border-color:{{ $badge['color'] }}40;font-size:.75rem">
                    {{ $badge['label'] }}
                </span>
            </div>
        </div>
    </div>

    <div class="tracking-grid">

        <!-- Timeline -->
        <div class="timeline" id="timeline-wrap">
            <div class="timeline__title">
                Estado del pedido
                <span class="timeline__live">EN VIVO</span>
            </div>

            <div id="timeline-steps">
                @foreach($orden->seguimiento as $paso)
                    <div class="timeline-step {{ $paso->completado ? 'done' : '' }}
                                {{ $paso->estado === $orden->estado_entrega && !$paso->completado ? 'active' : '' }}"
                         data-estado="{{ $paso->estado }}">
                        <div class="timeline-step__dot">{{ $paso->icono }}</div>
                        <div class="timeline-step__content">
                            <div class="timeline-step__titulo">{{ $paso->titulo }}</div>
                            <div class="timeline-step__desc">{{ $paso->descripcion }}</div>
                            @if($paso->completado)
                                <div class="timeline-step__time">
                                    {{ $paso->updated_at->format('d/m/Y H:i') }}
                                </div>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        <!-- Mapa + info -->
        <div>
            <div class="tracking-map-wrap">
                <div class="tracking-map-head">
                    <span>📍 Ubicación en tiempo real</span>
                    <span id="map-status" style="font-size:.75rem;color:var(--text-muted)">
                        Actualizando...
                    </span>
                </div>
                <div id="tracking-map" class="tracking-map"></div>

                <div class="tracking-info-card">
                    <div class="tracking-info-row">
                        <span class="lbl">Transportista</span>
                        <span class="val">
                            @if($orden->transporte === 'dron')
                                🚁 DronShop Alpha-1
                            @elseif($orden->transporte === 'moto')
                                🏍️ Mensajero DronShop
                            @else
                                🚗 Vehículo DronShop
                            @endif
                        </span>
                    </div>
                    <div class="tracking-info-row">
                        <span class="lbl">Modalidad</span>
                        <span class="val">{{ ucfirst($orden->transporte) }}</span>
                    </div>
                    <div class="tracking-info-row">
                        <span class="lbl">Destino</span>
                        <span class="val">{{ $orden->ciudad }}</span>
                    </div>
                    <div class="tracking-info-row" id="row-estado-entrega">
                        <span class="lbl">Estado actual</span>
                        <span class="val" id="estado-entrega-txt">
                            {{ str_replace('_', ' ', ucfirst($orden->estado_entrega)) }}
                        </span>
                    </div>
                </div>
            </div>

            <!-- Productos del pedido -->
            <div style="background:var(--bg-card);border:1px solid var(--border);
                        border-radius:var(--radius-lg);padding:1.25rem;margin-top:1.25rem">
                <div style="font-family:'Syne',sans-serif;font-size:.85rem;font-weight:700;
                            margin-bottom:1rem;color:var(--text-muted);text-transform:uppercase;
                            letter-spacing:.08em">Tu pedido</div>
                @foreach($orden->items as $item)
                    <div style="display:flex;align-items:center;gap:.75rem;margin-bottom:.75rem">
                        @if($item->producto)
                            <img src="{{ $item->producto->imagen }}"
                                 style="width:44px;height:44px;border-radius:8px;
                                        object-fit:cover;border:1px solid var(--border)">
                        @endif
                        <div style="flex:1;font-size:.85rem">
                            <div style="font-weight:500;color:var(--text-primary)">{{ $item->nombre_producto }}</div>
                            <div style="color:var(--text-muted);font-size:.78rem">x{{ $item->cantidad }}</div>
                        </div>
                        <div style="font-family:'Syne',sans-serif;font-weight:700;color:var(--gold-400);font-size:.9rem">
                            $ {{ number_format($item->subtotal, 0, ',', '.') }}
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
</div>
@push('scripts')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/leaflet.min.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/leaflet.min.js"></script>

    <script>
    // ══════════════════════════════════════════════
    // MAPA
    // ══════════════════════════════════════════════
    const map = L.map('tracking-map').setView([7.1254, -73.1198], 14);

    L.tileLayer(
        `https://{s}-tiles.locationiq.com/v3/darkmatter/r/{z}/{x}/{y}.png?key={{ config('services.locationiq.key') }}`,
        { attribution: '©LocationIQ ©OpenStreetMap', subdomains: 'a', maxZoom: 19 }
    ).addTo(map);

    // Marcador bodega
    L.marker([7.1254, -73.1198], {
        icon: L.divIcon({
            html: `<div style="background:#C9A84C;padding:3px 8px;border-radius:6px;
                            color:#000;font-size:11px;font-weight:700;white-space:nowrap;
                            box-shadow:0 2px 6px #0008">🏭 DronShop</div>`,
            className: '', iconAnchor: [42, 20]
        })
    }).addTo(map);

    // Marcador destino
    let markerDestino = L.marker([7.1198, -73.1227], {
        icon: L.divIcon({
            html: `<div style="background:#4ade80;padding:3px 8px;border-radius:6px;
                            color:#000;font-size:11px;font-weight:700;white-space:nowrap;
                            box-shadow:0 2px 6px #0008">📍 {{ addslashes($orden->direccion_entrega) }}</div>`,
            className: '', iconAnchor: [60, 20]
        })
    }).addTo(map);

    // Marcador vehículo
    const vehiculoEmoji = '{{ $orden->transporte === "dron" ? "https://img.icons8.com/?size=100&id=HEGf1eAQiXfe&format=png&color=000000" : ($orden->transporte === "moto" ? "🏍️" : "🚗") }}';

    let markerVehiculo = L.marker([7.1254, -73.1198], {
        icon: L.divIcon({
            html: `<div style="font-size:28px;filter:drop-shadow(0 2px 6px #000)">${vehiculoEmoji}</div>`,
            iconSize: [32,32], className: '', iconAnchor: [16,16]
        })
    }).addTo(map);

    // Línea ruta
    let rutaLine = L.polyline(
        [[7.1254, -73.1198], [7.1198, -73.1227]],
        { color: '#C9A84C', weight: 3, opacity: .5, dashArray: '10,8' }
    ).addTo(map);

    let rutaActualizada = false;

    // ══════════════════════════════════════════════
    // ACTUALIZAR TIMELINE
    // ══════════════════════════════════════════════
    function renderTimeline(seguimiento, estadoActual) {
        seguimiento.forEach(paso => {
            const el = document.querySelector(`[data-estado="${paso.estado}"]`);
            if (!el) return;

            const dot     = el.querySelector('.timeline-step__dot');
            const content = el.querySelector('.timeline-step__content');
            const titulo  = el.querySelector('.timeline-step__titulo');

            // Quitar clases previas
            el.classList.remove('done', 'active');

            if (paso.completado) {
                // ✅ Paso completado
                el.classList.add('done');
                if (dot) dot.innerHTML = '✅';
                if (titulo) titulo.style.color = 'var(--text-primary)';

                // Agregar timestamp si no existe
                if (content && paso.tiempo && !el.querySelector('.timeline-step__time')) {
                    const t = document.createElement('div');
                    t.className = 'timeline-step__time';
                    t.textContent = paso.tiempo;
                    content.appendChild(t);
                }

            } else if (paso.estado === estadoActual) {
                // 🔄 Paso activo (en progreso)
                el.classList.add('active');
                if (titulo) titulo.style.color = 'var(--cyan-400)';

            } else {
                // ⏳ Paso pendiente
                if (dot) dot.innerHTML = paso.icono;
                if (titulo) titulo.style.color = 'var(--text-muted)';
            }
        });
    }

    // ══════════════════════════════════════════════
    // BANNER ENTREGADO
    // ══════════════════════════════════════════════
    function mostrarBannerEntregado() {
        if (document.getElementById('banner-entregado')) return;
        const banner = document.createElement('div');
        banner.id = 'banner-entregado';
        banner.style.cssText = `
            position:fixed;bottom:2rem;left:50%;transform:translateX(-50%);
            background:linear-gradient(135deg,#052e16,#16a34a);
            border:1px solid #4ade8060;border-radius:16px;
            padding:1.25rem 2rem;color:#fff;font-family:'Syne',sans-serif;
            font-size:1rem;font-weight:700;z-index:9999;
            box-shadow:0 8px 32px #0008;
            display:flex;align-items:center;gap:.75rem;
            animation:slideUp .4s ease;
        `;
        banner.innerHTML = `
            <span style="font-size:2rem">🎉</span>
            <div>
                <div>¡Tu pedido fue entregado!</div>
                <div style="font-weight:400;font-size:.82rem;color:#86efac;margin-top:.2rem">
                    Gracias por comprar en DronShop
                </div>
            </div>
            <a href="{{ route('catalogo.index') }}"
            style="margin-left:1rem;background:#4ade80;color:#000;
                    padding:.5rem 1rem;border-radius:8px;
                    font-size:.82rem;text-decoration:none;font-weight:700">
                Seguir comprando →
            </a>
        `;
        document.body.appendChild(banner);

        // Redirigir al detalle tras 8 segundos
        setTimeout(() => {
            window.location.href = '{{ route("orden.show", $orden) }}';
        }, 8000);
    }

    // ══════════════════════════════════════════════
    // POLLING PRINCIPAL
    // ══════════════════════════════════════════════
    let ultimoEstado   = '{{ $orden->estado_entrega }}';
    let pollingActivo  = true;

    async function actualizarTracking() {
        if (!pollingActivo) return;

        try {
            const res = await fetch('{{ route("tracking.estado", $orden) }}', {
                headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
            });

            if (!res.ok) throw new Error('HTTP ' + res.status);
            const data = await res.json();

            // -- Actualizar texto estado --
            const estadoTxt = document.getElementById('estado-entrega-txt');
            if (estadoTxt) {
                estadoTxt.textContent = data.estado_entrega
                    .replace(/_/g, ' ')
                    .replace(/\b\w/g, l => l.toUpperCase());
            }

            // -- Renderizar timeline --
            renderTimeline(data.seguimiento, data.estado_entrega);

            // -- Notificación si cambió --
            if (data.estado_entrega !== ultimoEstado) {
                ultimoEstado = data.estado_entrega;
                const paso = data.seguimiento.find(p => p.estado === data.estado_entrega);
                if (paso && Notification.permission === 'granted') {
                    new Notification('DronShop — ' + paso.titulo, {
                        body: paso.descripcion, icon: '/favicon.ico'
                    });
                }
            }

            // -- Actualizar mapa con coordenadas REALES del vuelo --
            if (data.vuelo && !rutaActualizada) {
                const latO = data.vuelo.lat_origen;
                const lngO = data.vuelo.lng_origen;
                const latD = data.vuelo.lat_destino;
                const lngD = data.vuelo.lng_destino;

                // Actualizar destino con coordenada geocodificada real
                markerDestino.setLatLng([latD, lngD]);
                rutaLine.setLatLngs([[latO, lngO], [latD, lngD]]);

                // Ajustar zoom para ver todo el trayecto
                map.fitBounds([[latO, lngO], [latD, lngD]], { padding: [60, 60] });

                rutaActualizada = true;
            }

            // -- Mover vehículo --
            if (data.posicion) {
                markerVehiculo.setLatLng([data.posicion.lat, data.posicion.lng]);
            }

            // -- Timestamp --
            const ms = document.getElementById('map-status');
            if (ms) ms.textContent = 'Actualizado ' + new Date().toLocaleTimeString('es-CO');

            // -- Si entregado: detener todo --
            if (data.entregado) {
                pollingActivo = false;
                if (data.vuelo) {
                    markerVehiculo.setLatLng([data.vuelo.lat_destino, data.vuelo.lng_destino]);
                }
                mostrarBannerEntregado();
            }

        } catch(err) {
            console.warn('Polling error:', err);
        }
    }

    // Pedir permiso notificaciones
    if ('Notification' in window && Notification.permission === 'default') {
        Notification.requestPermission();
    }

    // Ejecutar inmediatamente y luego cada 4s
    actualizarTracking();
    const pollingInterval = setInterval(() => {
        if (!pollingActivo) { clearInterval(pollingInterval); return; }
        actualizarTracking();
    }, 4000);

    // CSS animación
    const s = document.createElement('style');
    s.textContent = `
        @keyframes slideUp {
            from { transform:translateX(-50%) translateY(20px); opacity:0; }
            to   { transform:translateX(-50%) translateY(0);    opacity:1; }
        }
    `;
    document.head.appendChild(s);
    </script>
@endpush
@endsection
