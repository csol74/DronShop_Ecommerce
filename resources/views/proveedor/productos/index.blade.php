@extends('layouts.proveedor')
@section('title', 'Mis Productos')

@section('content')
<div class="admin-page-header">
    <div>
        <h1 class="admin-page-title">Mis Productos</h1>
        <div class="admin-page-sub">{{ $proveedor->empresa }}</div>
    </div>
    <a href="{{ route('proveedor.productos.create') }}" class="btn btn-gold">+ Nuevo producto</a>
</div>

<div class="admin-table-wrap">
    <div class="admin-table-toolbar">
        <span class="admin-table-toolbar__title">{{ $productos->total() }} productos</span>
        <form method="GET" class="admin-filters">
            <div class="admin-search">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                </svg>
                <input type="text" name="buscar" placeholder="Buscar..." value="{{ request('buscar') }}">
            </div>
            <select name="categoria" class="admin-select" onchange="this.form.submit()">
                <option value="">Todas las categorías</option>
                @foreach($categorias as $cat)
                    <option value="{{ $cat->id }}"
                        {{ request('categoria') == $cat->id ? 'selected' : '' }}>
                        {{ $cat->nombre }}
                    </option>
                @endforeach
            </select>
            <select name="estado" class="admin-select" onchange="this.form.submit()">
                <option value="">Todos</option>
                <option value="activo"   {{ request('estado')==='activo'   ? 'selected':'' }}>Activos</option>
                <option value="inactivo" {{ request('estado')==='inactivo' ? 'selected':'' }}>Inactivos</option>
            </select>
            <button type="submit" class="btn btn-ghost"
                    style="padding:.5rem .85rem;font-size:.85rem">Filtrar</button>
        </form>
    </div>

    <table class="admin-table">
        <thead>
            <tr>
                <th>Producto</th>
                <th>Categoría</th>
                <th>Precio</th>
                <th>Stock</th>
                <th>Estado</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            @forelse($productos as $prod)
                <tr>
                    <td>
                        <div style="display:flex;align-items:center;gap:.75rem">
                            <img src="{{ $prod->imagen }}" alt="{{ $prod->nombre }}" class="tbl-img">
                            <div>
                                <div style="font-weight:600;color:var(--text-primary);font-size:.875rem">
                                    {{ Str::limit($prod->nombre, 35) }}
                                </div>
                                <div style="font-size:.75rem;color:var(--text-muted)">
                                    {{ $prod->peso_kg }} kg
                                </div>
                            </div>
                        </div>
                    </td>
                    <td>
                        <span style="color:var(--cyan-400);font-size:.8rem;font-weight:500">
                            {{ $prod->categoria->nombre }}
                        </span>
                    </td>
                    <td>
                        <span style="color:var(--gold-400);font-family:'Syne',sans-serif;font-weight:700">
                            $ {{ number_format($prod->precio, 0, ',', '.') }}
                        </span>
                    </td>
                    <td>
                        @if($prod->stock <= $prod->stock_minimo)
                            <span style="color:#F87171;font-weight:700">{{ $prod->stock }} ⚠️</span>
                        @else
                            <span style="color:#4ade80;font-weight:600">{{ $prod->stock }}</span>
                        @endif
                    </td>
                    <td>
                        @if($prod->activo)
                            <span class="status-dot"
                                  style="color:#4ade80;background:#05261640;border-color:#16a34a60">
                                ● Activo
                            </span>
                        @else
                            <span class="status-dot"
                                  style="color:#94A3B8;background:#1E2D4540;border-color:#1E2D45">
                                ● Inactivo
                            </span>
                        @endif
                    </td>
                    <td>
                        <div class="action-group">
                            <a href="{{ route('catalogo.show', $prod->slug) }}"
                               target="_blank" class="btn-action" title="Ver en catálogo">
                                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                          d="M15 12a3 3 0 11-6 0 3 3 0 016 0zM2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                </svg>
                            </a>
                            <a href="{{ route('proveedor.productos.edit', $prod) }}"
                               class="btn-action" title="Editar">
                                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                          d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                </svg>
                            </a>
                            <form method="POST"
                                  action="{{ route('proveedor.productos.toggle', $prod) }}">
                                @csrf @method('PATCH')
                                <button type="submit"
                                        class="btn-action {{ $prod->activo ? 'danger' : 'success' }}"
                                        title="{{ $prod->activo ? 'Desactivar' : 'Activar' }}">
                                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                              stroke-width="2"
                                              d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/>
                                    </svg>
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="6"
                        style="text-align:center;padding:3rem;color:var(--text-muted)">
                        No tienes productos publicados aún.
                        <a href="{{ route('proveedor.productos.create') }}"
                           style="color:var(--gold-400)">Crear el primero →</a>
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>

    @if($productos->hasPages())
        <div style="padding:1rem 1.25rem;border-top:1px solid var(--border)">
            {{ $productos->links('catalogo.pagination') }}
        </div>
    @endif
</div>
@endsection
