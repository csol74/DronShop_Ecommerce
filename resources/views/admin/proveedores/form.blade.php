@extends('layouts.admin')
@section('title', isset($proveedor) ? 'Editar Proveedor' : 'Nuevo Proveedor')

@section('content')
<div class="admin-page-header">
    <div>
        <h1 class="admin-page-title">{{ isset($proveedor) ? 'Editar proveedor' : 'Nuevo proveedor' }}</h1>
    </div>
    <a href="{{ route('admin.proveedores.index') }}" class="btn btn-ghost">← Volver</a>
</div>

@if($errors->any())
    <div class="flash flash-error" style="margin-bottom:1.5rem">{{ $errors->first() }}</div>
@endif

<form method="POST"
      action="{{ isset($proveedor) ? route('admin.proveedores.update', $proveedor) : route('admin.proveedores.store') }}"
      style="max-width:700px">
    @csrf
    @if(isset($proveedor)) @method('PUT') @endif

    <div class="admin-form-section">
        <div class="admin-form-section__head">🤝 Datos del proveedor</div>
        <div class="admin-form-section__body">
            <div class="admin-form-grid">
                <div class="form-group">
                    <label class="form-label">Nombre contacto *</label>
                    <input type="text" name="nombre" class="form-control"
                           value="{{ old('nombre', $proveedor->nombre ?? '') }}" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Empresa *</label>
                    <input type="text" name="empresa" class="form-control"
                           value="{{ old('empresa', $proveedor->empresa ?? '') }}" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Email *</label>
                    <input type="email" name="email" class="form-control"
                           value="{{ old('email', $proveedor->email ?? '') }}" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Teléfono *</label>
                    <input type="text" name="telefono" class="form-control"
                           placeholder="+57 300 000 0000"
                           value="{{ old('telefono', $proveedor->telefono ?? '') }}" required>
                </div>
                <div class="form-group">
                    <label class="form-label">País *</label>
                    <input type="text" name="pais" class="form-control"
                           value="{{ old('pais', $proveedor->pais ?? 'Colombia') }}" required>
                </div>
                <div class="form-group">
                    <label class="form-label">URL Logo</label>
                    <input type="url" name="logo" class="form-control"
                           placeholder="https://..."
                           value="{{ old('logo', $proveedor->logo ?? '') }}">
                </div>
                <div class="form-group full">
                    <label class="form-label">Descripción</label>
                    <textarea name="descripcion" class="form-control" rows="3">{{ old('descripcion', $proveedor->descripcion ?? '') }}</textarea>
                </div>
            </div>
        </div>
    </div>

    <div style="display:flex;gap:1rem;justify-content:flex-end">
        <a href="{{ route('admin.proveedores.index') }}" class="btn btn-ghost">Cancelar</a>
        <button type="submit" class="btn btn-gold" style="padding:.75rem 2rem">
            {{ isset($proveedor) ? '💾 Guardar cambios' : '✨ Registrar proveedor' }}
        </button>
    </div>
</form>
@endsection
