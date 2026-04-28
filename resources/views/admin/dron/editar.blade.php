@extends('layouts.admin')
@section('title', 'Editar Dron')

@section('content')
<div class="admin-page-header">
    <div><h1 class="admin-page-title">✏️ Editar ficha técnica del dron</h1></div>
    <a href="{{ route('admin.dron.index') }}" class="btn btn-ghost">← Volver</a>
</div>

@if($errors->any())
    <div class="flash flash-error">{{ $errors->first() }}</div>
@endif

<form method="POST" action="{{ route('admin.dron.actualizar') }}" style="max-width:800px">
    @csrf @method('PUT')

    <div class="admin-form-section">
        <div class="admin-form-section__head">🚁 Identificación</div>
        <div class="admin-form-section__body">
            <div class="admin-form-grid">
                <div class="form-group">
                    <label class="form-label">Nombre *</label>
                    <input type="text" name="nombre" class="form-control"
                           value="{{ old('nombre', $dron->nombre) }}" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Modelo *</label>
                    <input type="text" name="modelo" class="form-control"
                           value="{{ old('modelo', $dron->modelo) }}" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Fabricante *</label>
                    <input type="text" name="fabricante" class="form-control"
                           value="{{ old('fabricante', $dron->fabricante) }}" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Fecha de adquisición *</label>
                    <input type="date" name="fecha_adquisicion" class="form-control"
                           value="{{ old('fecha_adquisicion', $dron->fecha_adquisicion->format('Y-m-d')) }}" required>
                </div>
            </div>
        </div>
    </div>

    <div class="admin-form-section">
        <div class="admin-form-section__head">⚙️ Especificaciones técnicas</div>
        <div class="admin-form-section__body">
            <div class="admin-form-grid">
                <div class="form-group">
                    <label class="form-label">Autonomía (minutos) *</label>
                    <input type="number" name="autonomia_min" class="form-control"
                           value="{{ old('autonomia_min', $dron->autonomia_min) }}" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Velocidad máx. (km/h) *</label>
                    <input type="number" name="velocidad_max_kmh" class="form-control" step="0.1"
                           value="{{ old('velocidad_max_kmh', $dron->velocidad_max_kmh) }}" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Alcance máx. (km) *</label>
                    <input type="number" name="alcance_max_km" class="form-control" step="0.1"
                           value="{{ old('alcance_max_km', $dron->alcance_max_km) }}" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Carga máxima (kg) *</label>
                    <input type="number" name="carga_max_kg" class="form-control" step="0.1"
                           value="{{ old('carga_max_kg', $dron->carga_max_kg) }}" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Batería mínima operación (%) *</label>
                    <input type="number" name="bateria_minima_pct" class="form-control" min="1" max="50"
                           value="{{ old('bateria_minima_pct', $dron->bateria_minima_pct) }}" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Batería actual (%) *</label>
                    <input type="number" name="bateria_actual_pct" class="form-control" min="0" max="100"
                           value="{{ old('bateria_actual_pct', $dron->bateria_actual_pct) }}" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Estado *</label>
                    <select name="estado" class="form-control admin-select" style="width:100%">
                        @foreach(['disponible','en_vuelo','mantenimiento','fuera_servicio'] as $est)
                            <option value="{{ $est }}" {{ $dron->estado === $est ? 'selected' : '' }}>
                                {{ ucfirst(str_replace('_', ' ', $est)) }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>
        </div>
    </div>

    <div style="display:flex;gap:1rem;justify-content:flex-end">
        <a href="{{ route('admin.dron.index') }}" class="btn btn-ghost">Cancelar</a>
        <button type="submit" class="btn btn-gold" style="padding:.75rem 2rem">💾 Guardar cambios</button>
    </div>
</form>
@endsection
