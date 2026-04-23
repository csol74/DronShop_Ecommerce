@extends('layouts.admin')
@section('title', isset($producto) ? 'Editar Producto' : 'Nuevo Producto')

@section('content')
<div class="admin-page-header">
    <div>
        <h1 class="admin-page-title">{{ isset($producto) ? 'Editar producto' : 'Nuevo producto' }}</h1>
        <div class="admin-page-sub">{{ isset($producto) ? $producto->nombre : 'Completa los campos del formulario' }}</div>
    </div>
    <a href="{{ route('admin.productos.index') }}" class="btn btn-ghost">← Volver</a>
</div>

@if($errors->any())
    <div class="flash flash-error" style="margin-bottom:1.5rem">{{ $errors->first() }}</div>
@endif

<form method="POST"
      action="{{ isset($producto) ? route('admin.productos.update', $producto) : route('admin.productos.store') }}">
    @csrf
    @if(isset($producto)) @method('PUT') @endif

    <div class="admin-form-section">
        <div class="admin-form-section__head">📝 Información básica</div>
        <div class="admin-form-section__body">
            <div class="admin-form-grid">
                <div class="form-group full">
                    <label class="form-label">Nombre del producto *</label>
                    <input type="text" name="nombre" class="form-control"
                           value="{{ old('nombre', $producto->nombre ?? '') }}" required>
                </div>
                <div class="form-group full">
                    <label class="form-label">Descripción *</label>
                    <textarea name="descripcion" class="form-control" rows="3"
                              required>{{ old('descripcion', $producto->descripcion ?? '') }}</textarea>
                </div>
                <div class="form-group">
                    <label class="form-label">Categoría *</label>
                    <select name="categoria_id" class="form-control admin-select" style="width:100%" required>
                        <option value="">Seleccionar...</option>
                        @foreach($categorias as $cat)
                            <option value="{{ $cat->id }}"
                                {{ old('categoria_id', $producto->categoria_id ?? '') == $cat->id ? 'selected' : '' }}>
                                {{ $cat->nombre }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Proveedor *</label>
                    <select name="proveedor_id" class="form-control admin-select" style="width:100%" required>
                        <option value="">Seleccionar...</option>
                        @foreach($proveedores as $prov)
                            <option value="{{ $prov->id }}"
                                {{ old('proveedor_id', $producto->proveedor_id ?? '') == $prov->id ? 'selected' : '' }}>
                                {{ $prov->empresa }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group full">
                    <label class="form-label">URL de imagen *</label>
                    <input type="url" name="imagen" class="form-control"
                           placeholder="https://..." id="imgUrl"
                           value="{{ old('imagen', $producto->imagen ?? '') }}" required
                           oninput="document.getElementById('imgPreview').src=this.value">
                    <div style="margin-top:.75rem">
                        <img id="imgPreview" src="{{ old('imagen', $producto->imagen ?? '') }}"
                             style="width:120px;height:120px;object-fit:cover;border-radius:10px;
                                    border:1px solid var(--border);display:{{ isset($producto) ? 'block' : 'none' }}"
                             onerror="this.style.display='none'" onload="this.style.display='block'">
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="admin-form-section">
        <div class="admin-form-section__head">💲 Precio e inventario</div>
        <div class="admin-form-section__body">
            <div class="admin-form-grid">
                <div class="form-group">
                    <label class="form-label">Precio (COP) *</label>
                    <input type="number" name="precio" class="form-control" step="100" min="0"
                           value="{{ old('precio', $producto->precio ?? '') }}" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Stock actual *</label>
                    <input type="number" name="stock" class="form-control" min="0"
                           value="{{ old('stock', $producto->stock ?? 0) }}" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Stock mínimo (alerta) *</label>
                    <input type="number" name="stock_minimo" class="form-control" min="1"
                           value="{{ old('stock_minimo', $producto->stock_minimo ?? 5) }}" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Peso (kg) *</label>
                    <input type="number" name="peso_kg" class="form-control" step="0.1" min="0"
                           value="{{ old('peso_kg', $producto->peso_kg ?? 0.5) }}" required>
                </div>
                <div class="form-group">
                    <label class="toggle-wrap">
                        <label class="toggle">
                            <input type="checkbox" name="activo" value="1"
                                   {{ old('activo', $producto->activo ?? true) ? 'checked' : '' }}>
                            <span class="toggle__slider"></span>
                        </label>
                        <span style="font-size:.875rem;color:var(--text-secondary)">Producto activo (visible en catálogo)</span>
                    </label>
                </div>
            </div>
        </div>
    </div>

    <div class="admin-form-section">
        <div class="admin-form-section__head">🔧 Especificaciones técnicas</div>
        <div class="admin-form-section__body">
            <label class="form-label">Una por línea, formato: Clave: Valor</label>
            <textarea name="caracteristicas" class="form-control" rows="8"
                      placeholder="Pantalla: AMOLED 6.8&quot; 120Hz&#10;RAM: 12 GB&#10;Batería: 5000mAh">{{ old('caracteristicas', $caracText ?? '') }}</textarea>
            <p style="font-size:.78rem;color:var(--text-muted);margin-top:.4rem">
                Ejemplo: <code style="color:var(--cyan-400)">Procesador: Snapdragon 8 Gen 3</code>
            </p>
        </div>
    </div>

    <div style="display:flex;gap:1rem;justify-content:flex-end">
        <a href="{{ route('admin.productos.index') }}" class="btn btn-ghost">Cancelar</a>
        <button type="submit" class="btn btn-gold" style="padding:.75rem 2rem">
            {{ isset($producto) ? '💾 Guardar cambios' : '✨ Crear producto' }}
        </button>
    </div>
</form>
@endsection
