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

    <style>
    /* Barra de progreso del paso actual */
    .paso-progress-wrap {
        margin-top: .5rem;
        height: 4px;
        background: var(--border);
        border-radius: 2px;
        overflow: hidden;
    }
    .paso-progress-bar {
        height: 100%;
        border-radius: 2px;
        background: linear-gradient(90deg, var(--cyan-400), var(--gold-400));
        transition: width 1s linear;
        width: 0%;
    }
    .paso-timer {
        font-size: .72rem;
        color: var(--text-muted);
        margin-top: .3rem;
    }
    </style>

    <script>
    // ══════════════════════════════════════════════════════
    // MAPA LEAFLET
    // ══════════════════════════════════════════════════════
    const map = L.map('tracking-map').setView([7.1254, -73.1198], 14);

    L.tileLayer('https://{s}.basemaps.cartocdn.com/dark_all/{z}/{x}/{y}{r}.png', {
        attribution: '©OpenStreetMap ©CartoDB',
        subdomains: 'abcd', maxZoom: 19
    }).addTo(map);

    // Marcador origen (bodega)
    L.marker([7.1254, -73.1198], {
        icon: L.divIcon({
            html: `<div style="background:#C9A84C;padding:3px 8px;border-radius:6px;
                            color:#000;font-size:11px;font-weight:700;white-space:nowrap;
                            box-shadow:0 2px 6px #0008">🏭 DronShop</div>`,
            className: '', iconAnchor: [42, 20]
        })
    }).addTo(map);

    // Marcador destino
    let markerDestino = L.marker([7.1254, -73.1198], { //origen temporal
        icon: L.divIcon({
            html: `<div style="background:#4ade80;padding:3px 8px;border-radius:6px;
                            color:#000;font-size:11px;font-weight:700;white-space:nowrap;
                            box-shadow:0 2px 6px #0008">📍 {{ addslashes(Str::limit($orden->direccion_entrega, 25)) }}</div>`,
            className: '', iconAnchor: [50, 20]
        })
    }).addTo(map);

    // Marcador vehículo
    const emoji = '{{ $orden->transporte === "dron" ? "🚁" : ($orden->transporte === "moto" ? "🏍️" : "🚗") }}';
    let markerVehiculo = L.marker([7.1254, -73.1198], {
        icon: L.divIcon({
            html: `<div style="font-size:28px;filter:drop-shadow(0 3px 8px #000);transition:all .5s ease">${emoji}</div>`,
            iconSize: [32, 32], className: '', iconAnchor: [16, 16]
        })
    }).addTo(map);

    // Línea de ruta punteada
    let rutaLine = L.polyline([[7.1254, -73.1198], [7.1198, -73.1227]], {
        color: '#C9A84C40', weight: 3, dashArray: '10,8'
    }).addTo(map);

    // Línea de progreso (rellena según avance)
    let progresLine = L.polyline([[7.1254, -73.1198], [7.1254, -73.1198]], {
        color: '#C9A84C', weight: 4, opacity: .85
    }).addTo(map);

    let rutaActualizada = false;
    let latO = 7.1254, lngO = -73.1198;
    let latD = null, lngD = null;

    // ══════════════════════════════════════════════════════
    // MOVER VEHÍCULO SUAVEMENTE (interpolación cliente)
    // El servidor da progreso 0.0→1.0, el cliente anima frame a frame
    // ══════════════════════════════════════════════════════
    let progresoActual  = 0;
    let progresoObjetivo = 0;
    let animFrameId     = null;

    function animarVehiculo() {
        if (Math.abs(progresoActual - progresoObjetivo) < 0.0001) {
            progresoActual = progresoObjetivo;
            animFrameId = null;
            return;
        }

        // Lerp suave: acercarse 8% cada frame (~60fps)
        progresoActual += (progresoObjetivo - progresoActual) * 0.02;

        const lat = latO + (latD - latO) * progresoActual;
        const lng = lngO + (lngD - lngO) * progresoActual;

        markerVehiculo.setLatLng([lat, lng]);

        // Dibujar línea de progreso recorrido
        progresLine.setLatLngs([[latO, lngO], [lat, lng]]);

        animFrameId = requestAnimationFrame(animarVehiculo);
    }

    function setProgresoObjetivo(p) {
        progresoObjetivo = p;
        if (!animFrameId) {
            animFrameId = requestAnimationFrame(animarVehiculo);
        }
    }

    // ══════════════════════════════════════════════════════
    // TIMELINE
    // ══════════════════════════════════════════════════════
    function renderTimeline(seguimiento, estadoActual, progresoPaso, segundosRestantes) {
        seguimiento.forEach((paso, idx) => {
            const el = document.querySelector(`[data-estado="${paso.estado}"]`);
            if (!el) return;

            const dot     = el.querySelector('.timeline-step__dot');
            const content = el.querySelector('.timeline-step__content');
            const titulo  = el.querySelector('.timeline-step__titulo');

            el.classList.remove('done', 'active');

            if (paso.completado) {
                // ✅ Completado
                el.classList.add('done');
                if (dot) dot.innerHTML = '✅';

                // Agregar timestamp
                if (content && paso.tiempo && !el.querySelector('.timeline-step__time')) {
                    const t = document.createElement('div');
                    t.className = 'timeline-step__time';
                    t.textContent = '✓ ' + paso.tiempo;
                    content.appendChild(t);
                }

                // Quitar barra de progreso si existe
                const bar = el.querySelector('.paso-progress-wrap');
                if (bar) bar.remove();

            } else if (paso.estado === estadoActual) {
                // 🔄 Activo — mostrar barra de progreso y timer
                el.classList.add('active');

                // Barra de progreso
                let wrap = el.querySelector('.paso-progress-wrap');
                if (!wrap) {
                    wrap = document.createElement('div');
                    wrap.className = 'paso-progress-wrap';
                    wrap.innerHTML = `<div class="paso-progress-bar" id="progress-bar-${paso.estado}"></div>`;
                    content.appendChild(wrap);

                    const timer = document.createElement('div');
                    timer.className = 'paso-timer';
                    timer.id = `timer-${paso.estado}`;
                    content.appendChild(timer);
                }

                const bar = document.getElementById(`progress-bar-${paso.estado}`);
                if (bar) bar.style.width = progresoPaso + '%';

                const timer = document.getElementById(`timer-${paso.estado}`);
                if (timer) {
                    const min = Math.floor(segundosRestantes / 60);
                    const seg = segundosRestantes % 60;
                    timer.textContent = min > 0
                        ? `⏱ ${min}m ${seg}s para siguiente paso`
                        : `⏱ ${seg}s para siguiente paso`;
                }

            } else {
                // ⏳ Pendiente
                if (dot) dot.innerHTML = paso.icono;
                const bar = el.querySelector('.paso-progress-wrap');
                if (bar) bar.remove();
            }
        });
    }

    // ══════════════════════════════════════════════════════
    // BANNER ENTREGADO
    // ══════════════════════════════════════════════════════
    function mostrarBannerEntregado() {
        if (document.getElementById('banner-entregado')) return;
        const banner = document.createElement('div');
        banner.id = 'banner-entregado';
        banner.style.cssText = `
            position:fixed;bottom:2rem;left:50%;transform:translateX(-50%);
            background:linear-gradient(135deg,#052e16,#16a34a);
            border:1px solid #4ade8060;border-radius:16px;
            padding:1.25rem 2rem;color:#fff;
            font-family:'Syne',sans-serif;font-size:1rem;font-weight:700;
            z-index:9999;box-shadow:0 8px 32px #0008;
            display:flex;align-items:center;gap:.75rem;
            animation:slideUp .4s ease;
        `;
        banner.innerHTML = `
            <span style="font-size:2rem">🎉</span>
            <div>
                <div>¡Tu pedido fue entregado!</div>
                <div style="font-weight:400;font-size:.82rem;color:#86efac;margin-top:.2rem">
                    Redirigiendo en 5 segundos...
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
        setTimeout(() => { window.location.href = '{{ route("orden.show", $orden) }}'; }, 5000);
    }

    // ══════════════════════════════════════════════════════
    // POLLING PRINCIPAL
    // ══════════════════════════════════════════════════════
    let ultimoEstado  = '{{ $orden->estado_entrega }}';
    let pollingActivo = true;
    let enVuelo       = false;

    async function actualizarTracking() {
        if (!pollingActivo) return;

        try {
            const res = await fetch('{{ route("tracking.estado", $orden) }}', {
                headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
            });
            if (!res.ok) throw new Error('HTTP ' + res.status);
            const data = await res.json();

            // -- Texto estado actual --
            const txt = document.getElementById('estado-entrega-txt');
            if (txt) txt.textContent = data.estado_entrega
                .replace(/_/g, ' ')
                .replace(/\b\w/g, l => l.toUpperCase());

            // -- Timeline con barra y timer --
            renderTimeline(
                data.seguimiento,
                data.estado_entrega,
                data.progreso_paso      ?? 0,
                data.segundos_restantes ?? 0
            );

            // -- Notificación si cambió estado --
            if (data.estado_entrega !== ultimoEstado) {
                ultimoEstado = data.estado_entrega;
                if ('Notification' in window && Notification.permission === 'granted') {
                    const paso = data.seguimiento.find(p => p.estado === data.estado_entrega);
                    if (paso) new Notification('DronShop — ' + paso.titulo, {
                        body: paso.descripcion, icon: '/favicon.ico'
                    });
                }
            }

            // -- Actualizar ruta real del vuelo (solo primera vez) --
            if (data.vuelo && !rutaActualizada) {
                latO = data.vuelo.lat_origen;
                lngO = data.vuelo.lng_origen;
                latD = data.vuelo.lat_destino;
                lngD = data.vuelo.lng_destino;

                markerDestino.setLatLng([latD, lngD]);
                rutaLine.setLatLngs([[latO, lngO], [latD, lngD]]);
                progresLine.setLatLngs([[latO, lngO], [latO, lngO]]);

                map.fitBounds([[latO, lngO], [latD, lngD]], { padding: [60, 60] });
                rutaActualizada = true;
            }

            // -- Mover vehículo suavemente con progreso del servidor --
            if (data.progreso_movimiento !== null && data.progreso_movimiento !== undefined) {
                setProgresoObjetivo(parseFloat(data.progreso_movimiento));
                enVuelo = data.vuelo?.estado_mision === 'en_vuelo';
            }

            // -- Timestamp --
            const ms = document.getElementById('map-status');
            if (ms) ms.textContent = 'Actualizado ' + new Date().toLocaleTimeString('es-CO');

            // -- Entregado --
            if (data.entregado) {
                pollingActivo = false;
                setProgresoObjetivo(1.0); // llegar al destino
                mostrarBannerEntregado();
            }

        } catch(err) {
            console.warn('Polling error:', err);
        }
    }

    // Permisos notificaciones
    if ('Notification' in window && Notification.permission === 'default') {
        Notification.requestPermission();
    }

    // Iniciar
    actualizarTracking();
    const pollingId = setInterval(() => {
        if (!pollingActivo) { clearInterval(pollingId); return; }
        actualizarTracking();
    }, 4000);

    // CSS animación banner
    const s = document.createElement('style');
    s.textContent = `
        @keyframes slideUp {
            from { transform:translateX(-50%) translateY(20px); opacity:0; }
            to   { transform:translateX(-50%) translateY(0); opacity:1; }
        }
    `;
    document.head.appendChild(s);
    </script>
@endpush
@endsection
