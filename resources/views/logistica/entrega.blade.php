@extends('layouts.logistica')
@section('title', 'Entrega ' . $orden->codigo)

@section('content')
<div class="admin-page-header">
    <div>
        <h1 class="admin-page-title">{{ $orden->codigo }}</h1>
        <div class="admin-page-sub">
            {{ $orden->transporte_icon }} {{ ucfirst($orden->transporte) }} ·
            {{ $orden->direccion_entrega }}, {{ $orden->ciudad }}
        </div>
    </div>
    <a href="{{ route('logistica.dashboard') }}" class="btn btn-ghost">← Volver</a>
</div>

@if($errors->any())
    <div class="flash flash-error" style="margin-bottom:1.5rem">
        {{ $errors->first() }}
    </div>
@endif

<div style="display:grid;grid-template-columns:1fr 340px;gap:1.5rem;align-items:start">

    <div class="admin-form-section">
        <div class="admin-form-section__head">
            📋 Seguimiento del pedido
            <span style="margin-left:auto;font-size:.75rem;color:var(--text-muted);font-weight:400;text-transform:none;letter-spacing:0">
                Tú controlas cada paso
            </span>
        </div>
        <div class="admin-form-section__body" style="padding:0">

            @php
                // Ordenar el seguimiento una sola vez para asegurar consistencia
                $pasosOrdenados = $orden->seguimiento->sortBy('id');
                // El paso actual siempre será el primero que NO esté completado
                $pasoActual = $pasosOrdenados->firstWhere('completado', false);
            @endphp

            @foreach($pasosOrdenados as $paso)
                @php
                    $esCompletado         = (bool) $paso->completado;
                    $esActual             = $pasoActual && $paso->id === $pasoActual->id;
                    $esUltimoAntesEntrega = $esActual && $paso->estado === 'cerca';
                    $esEntregadoPendiente = !$esCompletado && $paso->estado === 'entregado' && !$esActual;
                @endphp

                <div style="display:flex;gap:1rem;padding:1.25rem 1.5rem;
                            border-bottom:{{ $loop->last ? 'none' : '1px solid var(--border)' }};
                            background:{{ $esActual ? '#06B6D410' : 'transparent' }}">

                    <div style="display:flex;flex-direction:column;align-items:center">
                        <div style="width:46px;height:46px;border-radius:50%;flex-shrink:0;
                                    display:grid;place-items:center;font-size:1.3rem;
                                    border:2px solid {{ $esCompletado ? 'var(--gold-500)' : ($esActual ? 'var(--cyan-400)' : 'var(--border)') }};
                                    background:{{ $esCompletado ? '#C9A84C20' : ($esActual ? '#06B6D415' : 'var(--bg-surface)') }};
                                    box-shadow:{{ $esActual ? '0 0 14px #22D3EE50' : 'none' }}">
                            {{ $esCompletado ? '✅' : $paso->icono }}
                        </div>
                        @if(!$loop->last)
                            <div style="width:2px;flex:1;min-height:20px;margin:4px 0;
                                        background:{{ $esCompletado ? 'var(--gold-500)' : 'var(--border)' }}">
                            </div>
                        @endif
                    </div>

                    <div style="flex:1;padding-top:.3rem">
                        <div style="display:flex;align-items:center;gap:.5rem;flex-wrap:wrap;margin-bottom:.25rem">
                            <span style="font-family:'Syne',sans-serif;font-weight:700;font-size:.95rem;
                                         color:{{ $esCompletado ? 'var(--text-primary)' : ($esActual ? 'var(--cyan-400)' : 'var(--text-muted)') }}">
                                {{ $paso->titulo }}
                            </span>
                            @if($esActual)
                                <span style="font-size:.65rem;background:var(--cyan-400);color:#000;
                                             padding:.15rem .5rem;border-radius:4px;font-weight:700">
                                    EN CURSO
                                </span>
                            @elseif($esCompletado)
                                <span style="font-size:.65rem;background:#052e16;color:#4ade80;
                                             border:1px solid #16a34a60;
                                             padding:.15rem .5rem;border-radius:4px;font-weight:600">
                                    ✓ LISTO
                                </span>
                            @else
                                <span style="font-size:.65rem;color:var(--text-muted);
                                             border:1px solid var(--border);
                                             padding:.15rem .5rem;border-radius:4px">
                                    PENDIENTE
                                </span>
                            @endif
                        </div>

                        <div style="font-size:.82rem;color:var(--text-muted);margin-bottom:.5rem">
                            {{ $paso->descripcion }}
                        </div>

                        @if($esCompletado)
                            <div style="font-size:.75rem;color:var(--text-muted)">
                                ✓ {{ $paso->updated_at->format('d/m/Y H:i') }}
                                @if($paso->logistica)
                                    · por <strong style="color:var(--text-secondary)">{{ $paso->logistica->name }}</strong>
                                @endif
                            </div>
                            @if($paso->foto_entrega)
                                <div style="margin-top:.75rem">
                                    <div style="font-size:.72rem;color:var(--text-muted);margin-bottom:.4rem">
                                        📷 Foto de entrega:
                                    </div>
                                    <img src="{{ asset($paso->foto_entrega) }}"
                                         style="width:200px;height:140px;object-fit:cover;
                                                border-radius:8px;border:1px solid var(--gold-600);
                                                cursor:pointer"
                                         onclick="abrirModal('{{ asset($paso->foto_entrega) }}')">
                                </div>
                            @endif
                        @elseif($esActual && !$esUltimoAntesEntrega)
                            <form method="POST" action="{{ route('logistica.entrega.avanzar', $orden) }}" style="margin-top:.75rem">
                                @csrf
                                <button type="submit" class="btn btn-gold" style="padding:.6rem 1.5rem;font-size:.875rem">
                                    ✓ Marcar como completado →
                                </button>
                            </form>
                        @elseif($esUltimoAntesEntrega)
                            <div style="margin-top:.85rem;background:var(--bg-surface);border:1px solid var(--gold-600);border-radius:var(--radius-md);padding:1.25rem">
                                <div style="font-family:'Syne',sans-serif;font-weight:700;color:var(--gold-400);margin-bottom:.4rem">
                                    📷 Subir foto para confirmar entrega
                                </div>
                                <p style="font-size:.82rem;color:var(--text-secondary);margin-bottom:1rem">
                                    Sin foto no puedes finalizar la entrega.
                                </p>
                                <form method="POST" action="{{ route('logistica.entrega.confirmar', $orden) }}" enctype="multipart/form-data">
                                    @csrf
                                    <input type="hidden" name="lat" id="input-lat">
                                    <input type="hidden" name="lng" id="input-lng">

                                    <div id="foto-preview-wrap" style="display:none;margin-bottom:.75rem">
                                        <img id="foto-preview" style="width:100%;max-height:200px;object-fit:cover;border-radius:var(--radius-md);border:2px solid var(--gold-500)">
                                        <div style="font-size:.75rem;color:#4ade80;margin-top:.35rem">
                                            ✓ Foto lista
                                        </div>
                                    </div>

                                    <div id="drop-zone" style="display:flex;flex-direction:column;align-items:center;gap:.5rem;padding:1.5rem;border:2px dashed var(--gold-600);border-radius:var(--radius-md);cursor:pointer;background:var(--bg-card);margin-bottom:.85rem;transition:var(--transition)">
                                        <span style="font-size:2rem">📷</span>
                                        <span style="font-size:.875rem;color:var(--text-secondary);font-weight:500">
                                            Toca para abrir cámara
                                        </span>
                                        <span style="font-size:.72rem;color:var(--text-muted)">
                                            JPG · PNG · WEBP — máx. 5MB
                                        </span>
                                        <input type="file" name="foto_entrega" id="foto-input" accept="image/*" capture="environment" style="display:none" required onchange="previewFoto(this)">
                                    </div>

                                    <button type="submit" class="btn btn-gold" style="width:100%;justify-content:center;padding:.85rem;font-size:1rem">
                                        🎯 Confirmar entrega
                                    </button>
                                </form>
                            </div>
                        @elseif($esEntregadoPendiente)
                            <div style="font-size:.78rem;color:var(--text-muted);margin-top:.3rem">
                                ⏳ Se marcará al confirmar con foto
                            </div>
                        @endif
                    </div>
                </div>
            @endforeach

        </div>
    </div>

    <div style="display:flex;flex-direction:column;gap:1.25rem">

        <div style="background:var(--bg-card);border:1px solid var(--border);border-radius:var(--radius-lg);padding:1.25rem;text-align:center">
            @php
                $badge = $orden->estado_badge;
                // Si hay un paso en curso, tomamos su título. Si no, usamos el estado de la orden.
                $textoEstadoActual = $pasoActual ? $pasoActual->titulo : str_replace('_',' ', ucfirst($orden->estado_entrega));
            @endphp
            <div style="font-size:.72rem;text-transform:uppercase;letter-spacing:.1em;color:var(--text-muted);margin-bottom:.5rem">Estado actual</div>
            <div class="status-dot" style="color:{{ $badge['color'] }};background:{{ $badge['bg'] }};border-color:{{ $badge['color'] }}40;font-size:.9rem;padding:.5rem 1.25rem;display:inline-flex">
                ● {{ $badge['label'] }}
            </div>
            <div style="font-size:.82rem;color:var(--cyan-400);margin-top:.6rem;font-weight:500">
                📍 {{ $textoEstadoActual }}
            </div>
        </div>

        <div class="admin-form-section">
            <div class="admin-form-section__head">👤 Datos de entrega</div>
            <div class="admin-form-section__body" style="font-size:.875rem;display:flex;flex-direction:column;gap:.6rem">
                <div>
                    <div style="font-size:.72rem;color:var(--text-muted);margin-bottom:.15rem">Cliente</div>
                    <div style="color:var(--text-primary);font-weight:600">{{ $orden->user->name ?? 'Cliente General' }}</div>
                </div>
                <div>
                    <div style="font-size:.72rem;color:var(--text-muted);margin-bottom:.15rem">Dirección</div>
                    <div style="color:var(--text-primary)">{{ $orden->direccion_entrega }}</div>
                </div>
                <div>
                    <div style="font-size:.72rem;color:var(--text-muted);margin-bottom:.15rem">Ciudad</div>
                    <div style="color:var(--text-primary)">{{ $orden->ciudad }}</div>
                </div>
                <div>
                    <div style="font-size:.72rem;color:var(--text-muted);margin-bottom:.15rem">Transporte</div>
                    <div style="color:var(--text-primary)">
                        {{ $orden->transporte_icon }} {{ ucfirst($orden->transporte) }}
                    </div>
                </div>
                @if($orden->notes ?? $orden->notas)
                    <div style="background:var(--bg-surface);border:1px solid var(--border);border-radius:var(--radius-sm);padding:.65rem;margin-top:.25rem">
                        <div style="font-size:.72rem;color:var(--text-muted);margin-bottom:.2rem">Notas</div>
                        <div style="font-size:.85rem">{{ $orden->notes ?? $orden->notas }}</div>
                    </div>
                @endif
            </div>
        </div>

        <div class="admin-form-section">
            <div class="admin-form-section__head">📦 Productos a entregar</div>
            <div class="admin-form-section__body" style="padding:0">
                @foreach($orden->items as $item)
                    <div style="display:flex;align-items:center;gap:.75rem;padding:.85rem 1.25rem;border-bottom:1px solid var(--border)">
                        @if($item->producto)
                            <img src="{{ $item->producto->imagen }}" style="width:48px;height:48px;border-radius:8px;object-fit:cover;border:1px solid var(--border);flex-shrink:0">
                        @endif
                        <div style="flex:1">
                            <div style="font-size:.875rem;font-weight:500;color:var(--text-primary)">
                                {{ $item->nombre_producto ?? ($item->producto->nombre ?? 'Producto') }}
                            </div>
                            <div style="font-size:.78rem;color:var(--text-muted)">
                                x{{ $item->cantidad }}
                                @if($item->producto && $item->producto->peso_kg)
                                    · {{ $item->producto->peso_kg }} kg
                                @endif
                            </div>
                        </div>
                        <div style="font-family:'Syne',sans-serif;font-weight:700;color:var(--gold-400);font-size:.875rem">
                            $ {{ number_format($item->subtotal ?? ($item->precio * $item->cantidad), 0, ',', '.') }}
                        </div>
                    </div>
                @endforeach

                <div style="padding:.85rem 1.25rem;display:flex;justify-content:space-between;font-size:.875rem">
                    <span style="color:var(--text-muted)">Total orden</span>
                    <span style="font-family:'Syne',sans-serif;font-weight:700;color:var(--gold-400)">
                        $ {{ number_format($orden->total, 0, ',', '.') }}
                    </span>
                </div>
            </div>
        </div>

    </div>
</div>

<div id="modal-foto" style="display:none;position:fixed;inset:0;background:#000d;z-index:9999;align-items:center;justify-content:center" onclick="this.style.display='none'">
    <img id="modal-img" style="max-width:92vw;max-height:90vh;border-radius:var(--radius-lg);box-shadow:0 8px 48px #000">
</div>

@push('scripts')
<script>
function abrirModal(src) {
    document.getElementById('modal-img').src = src;
    document.getElementById('modal-foto').style.display = 'flex';
}

function previewFoto(input) {
    if (!input.files[0]) return;
    const reader = new FileReader();
    reader.onload = e => {
        const preview = document.getElementById('foto-preview');
        const wrap    = document.getElementById('foto-preview-wrap');
        const zone    = document.getElementById('drop-zone');
        if (preview) preview.src = e.target.result;
        if (wrap)    wrap.style.display = 'block';
        if (zone)    zone.style.display = 'none';
    };
    reader.readAsDataURL(input.files[0]);
}

document.getElementById('drop-zone')?.addEventListener('click', function(e) {
    if (e.target.id === 'foto-input') return;
    document.getElementById('foto-input').click();
});

if ('geolocation' in navigator) {
    navigator.geolocation.getCurrentPosition(
        pos => {
            document.getElementById('input-lat').value = pos.coords.latitude;
            document.getElementById('input-lng').value = pos.coords.longitude;
        },
        () => {}
    );
}
</script>
@endpush
@endsection
