@extends('layouts.admin')
@section('title', 'Proveedores')

@section('content')
<div class="admin-page-header">
    <div>
        <h1 class="admin-page-title">Proveedores</h1>
        <div class="admin-page-sub">Empresas y contactos del catálogo</div>
    </div>
    <a href="{{ route('admin.proveedores.create') }}" class="btn btn-gold">+ Nuevo proveedor</a>
</div>

<div class="admin-table-wrap">
    <table class="admin-table">
        <thead>
            <tr>
                <th>Proveedor</th>
                <th>Empresa</th>
                <th>Contacto</th>
                <th>País</th>
                <th>Productos</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            @forelse($proveedores as $prov)
                <tr>
                    <td>
                        <div style="display:flex;align-items:center;gap:.75rem">
                            @if($prov->logo)
                                <img src="{{ $prov->logo }}" class="tbl-img">
                            @else
                                <div style="width:44px;height:44px;border-radius:8px;
                                            background:var(--bg-surface);border:1px solid var(--border);
                                            display:grid;place-items:center;font-size:1.1rem">🤝</div>
                            @endif
                            <span style="font-weight:600;color:var(--text-primary)">{{ $prov->nombre }}</span>
                        </div>
                    </td>
                    <td style="color:var(--gold-400);font-weight:500">{{ $prov->empresa }}</td>
                    <td>
                        <div style="font-size:.82rem">{{ $prov->email }}</div>
                        <div style="font-size:.78rem;color:var(--text-muted)">{{ $prov->telefono }}</div>
                    </td>
                    <td>{{ $prov->pais }}</td>
                    <td>
                        <span style="font-family:'Syne',sans-serif;font-weight:700;color:var(--text-primary)">
                            {{ $prov->productos_count }}
                        </span>
                    </td>
                    <td>
                        <div class="action-group">
                            <a href="{{ route('admin.proveedores.edit', $prov) }}" class="btn-action" title="Editar">
                                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                          d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                </svg>
                            </a>
                            @if($prov->productos_count === 0)
                                <form method="POST" action="{{ route('admin.proveedores.destroy', $prov) }}">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="btn-action danger"
                                            onclick="return confirm('¿Eliminar proveedor?')">
                                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                  d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                        </svg>
                                    </button>
                                </form>
                            @endif
                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" style="text-align:center;padding:3rem;color:var(--text-muted)">
                        No hay proveedores registrados.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>

    @if($proveedores->hasPages())
        <div style="padding:1rem 1.25rem;border-top:1px solid var(--border)">
            {{ $proveedores->links('catalogo.pagination') }}
        </div>
    @endif
</div>
@endsection
