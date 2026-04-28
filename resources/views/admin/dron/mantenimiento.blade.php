@extends('layouts.admin')
@section('title', 'Mantenimiento del Dron')

@section('content')
<div class="admin-page-header">
    <div>
        <h1 class="admin-page-title">🔧 Plan de Mantenimiento</h1>
        <div class="admin-page-sub">Control preventivo y correctivo del dron</div>
    </div>
    <a href="{{ route('admin.dron.index') }}" class="btn btn-ghost">← Volver al dron</a>
</div>

@if($proximos->count())
    <div class="flash flash-info" style="margin-bottom:1.5rem">
        ⚠️ Tienes {{ $proximos->count() }} mantenimiento(s) próximos pendientes.
    </div>
@endif

<div style="display:grid;grid-template-columns:1fr 380px;gap:1.5rem;align-items:start">

    <!-- Historial -->
    <div class="admin-table-wrap">
        <div class="admin-table-toolbar">
            <span class="admin-table-toolbar__title">Historial de mantenimientos</span>
        </div>
        <table class="admin-table">
            <thead>
                <tr>
                    <th>Tipo</th>
                    <th>Descripción</th>
                    <th>Programado</th>
                    <th>Técnico</th>
                    <th>Costo</th>
                    <th>Estado</th>
                    <th>Acción</th>
                </tr>
            </thead>
            <tbody>
                @forelse($mantenimientos as $mant)
                    @php
                        $mc = match($mant->estado) {
                            'completado' => ['color'=>'#4ade80','bg'=>'#05261640'],
                            'en_proceso' => ['color'=>'#F59E0B','bg'=>'#451a0350'],
                            'pendiente'  => ['color'=>'#60A5FA','bg'=>'#0c1a3550'],
                            'cancelado'  => ['color'=>'#94A3B8','bg'=>'#1E2D45'],
                            default      => ['color'=>'#94A3B8','bg'=>'#1E2D45'],
                        };
                    @endphp
                    <tr>
                        <td>
                            <span style="font-size:.78rem;font-weight:600;
                                         color:{{ $mant->tipo === 'preventivo' ? '#A78BFA' : '#F87171' }}">
                                {{ ucfirst($mant->tipo) }}
                            </span>
                        </td>
                        <td style="max-width:200px;font-size:.82rem">{{ $mant->descripcion }}</td>
                        <td style="font-size:.82rem">{{ $mant->fecha_programada->format('d/m/Y') }}</td>
                        <td style="font-size:.82rem">{{ $mant->tecnico ?? '—' }}</td>
                        <td style="font-size:.82rem;color:var(--gold-400)">
                            {{ $mant->costo ? '$ ' . number_format($mant->costo, 0, ',', '.') : '—' }}
                        </td>
                        <td>
                            <span class="status-dot"
                                  style="color:{{ $mc['color'] }};background:{{ $mc['bg'] }};
                                         border-color:{{ $mc['color'] }}40;font-size:.72rem">
                                {{ ucfirst($mant->estado) }}
                            </span>
                        </td>
                        <td>
                            @if($mant->estado !== 'completado' && $mant->estado !== 'cancelado')
                                <form method="POST" action="{{ route('admin.mantenimiento.actualizar', $mant) }}">
                                    @csrf @method('PATCH')
                                    <div style="display:flex;gap:.4rem">
                                        <select name="estado" class="admin-select" style="font-size:.75rem;padding:.3rem .5rem">
                                            <option value="pendiente"  {{ $mant->estado==='pendiente'  ?'selected':'' }}>Pendiente</option>
                                            <option value="en_proceso" {{ $mant->estado==='en_proceso' ?'selected':'' }}>En proceso</option>
                                            <option value="completado">Completado</option>
                                            <option value="cancelado">Cancelado</option>
                                        </select>
                                        <button type="submit" class="btn-action success">
                                            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                            </svg>
                                        </button>
                                    </div>
                                </form>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" style="text-align:center;padding:2rem;color:var(--text-muted)">
                            Sin mantenimientos registrados.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
        @if($mantenimientos->hasPages())
            <div style="padding:1rem 1.25rem;border-top:1px solid var(--border)">
                {{ $mantenimientos->links('catalogo.pagination') }}
            </div>
        @endif
    </div>

    <!-- Formulario nuevo -->
    <div class="admin-form-section">
        <div class="admin-form-section__head">➕ Programar mantenimiento</div>
        <div class="admin-form-section__body">
            <form method="POST" action="{{ route('admin.mantenimiento.store') }}">
                @csrf
                <div class="form-group">
                    <label class="form-label">Tipo *</label>
                    <select name="tipo" class="form-control admin-select" style="width:100%" required>
                        <option value="preventivo">Preventivo</option>
                        <option value="correctivo">Correctivo</option>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Descripción *</label>
                    <textarea name="descripcion" class="form-control" rows="2" required
                              placeholder="Ej: Revisión de motores y hélices"></textarea>
                </div>
                <div class="form-group">
                    <label class="form-label">Fecha programada *</label>
                    <input type="date" name="fecha_programada" class="form-control"
                           value="{{ now()->addDays(3)->format('Y-m-d') }}" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Técnico responsable</label>
                    <input type="text" name="tecnico" class="form-control" placeholder="Nombre del técnico">
                </div>
                <div class="form-group">
                    <label class="form-label">Costo estimado (COP)</label>
                    <input type="number" name="costo" class="form-control" step="1000" min="0">
                </div>
                <div class="form-group">
                    <label class="form-label">Observaciones</label>
                    <textarea name="observaciones" class="form-control" rows="2"></textarea>
                </div>
                <button type="submit" class="btn btn-gold" style="width:100%;justify-content:center">
                    📅 Programar
                </button>
            </form>
        </div>
    </div>
</div>
@endsection
