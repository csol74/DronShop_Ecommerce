@extends('layouts.admin')
@section('title', 'Usuarios')

@section('content')
<div class="admin-page-header">
    <div>
        <h1 class="admin-page-title">Usuarios</h1>
        <div class="admin-page-sub">Gestión de roles y accesos</div>
    </div>
</div>

<div class="admin-table-wrap">
    <div class="admin-table-toolbar">
        <span class="admin-table-toolbar__title">{{ $usuarios->total() }} usuarios</span>
        <form method="GET" class="admin-filters">
            <div class="admin-search">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                </svg>
                <input type="text" name="buscar" placeholder="Nombre o email..." value="{{ request('buscar') }}">
            </div>
            <select name="rol" class="admin-select" onchange="this.form.submit()">
                <option value="">Todos los roles</option>
                @foreach(['cliente','admin','operario','proveedor'] as $r)
                    <option value="{{ $r }}" {{ request('rol') === $r ? 'selected' : '' }}>
                        {{ ucfirst($r) }}
                    </option>
                @endforeach
            </select>
            <button type="submit" class="btn btn-ghost" style="padding:.5rem .85rem;font-size:.85rem">Buscar</button>
        </form>
    </div>

    <table class="admin-table">
        <thead>
            <tr>
                <th>Usuario</th>
                <th>Rol actual</th>
                <th>Pedidos</th>
                <th>Registrado</th>
                <th>Cambiar rol</th>
            </tr>
        </thead>
        <tbody>
            @forelse($usuarios as $user)
                <tr>
                    <td>
                        <div style="font-weight:600;color:var(--text-primary)">{{ $user->name }}</div>
                        <div style="font-size:.78rem;color:var(--text-muted)">{{ $user->email }}</div>
                    </td>
                    <td>
                        @php
                            $roleColor = match($user->role) {
                                'admin'     => ['color'=>'#C9A84C','bg'=>'#451a0350'],
                                'operario'  => ['color'=>'#60A5FA','bg'=>'#0c1a3550'],
                                'proveedor' => ['color'=>'#A78BFA','bg'=>'#1e123350'],
                                default     => ['color'=>'#94A3B8','bg'=>'#1E2D4540'],
                            };
                        @endphp
                        <span class="status-dot"
                              style="color:{{ $roleColor['color'] }};background:{{ $roleColor['bg'] }};
                                     border-color:{{ $roleColor['color'] }}40">
                            {{ ucfirst($user->role) }}
                        </span>
                    </td>
                    <td>
                        <span style="font-family:'Syne',sans-serif;font-weight:700;color:var(--text-primary)">
                            {{ $user->ordenes_count }}
                        </span>
                    </td>
                    <td style="font-size:.82rem;color:var(--text-muted)">
                        {{ $user->created_at->format('d/m/Y') }}
                    </td>
                    <td>
                        @if($user->id !== auth()->id())
                            <form method="POST" action="{{ route('admin.usuarios.rol', $user) }}"
                                  style="display:flex;gap:.5rem;align-items:center">
                                @csrf @method('PATCH')
                                <select name="role" class="admin-select" style="font-size:.8rem">
                                    @foreach(['cliente','admin','operario','proveedor'] as $r)
                                        <option value="{{ $r }}" {{ $user->role === $r ? 'selected' : '' }}>
                                            {{ ucfirst($r) }}
                                        </option>
                                    @endforeach
                                </select>
                                <button type="submit" class="btn-action success" title="Guardar rol">
                                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                              d="M5 13l4 4L19 7"/>
                                    </svg>
                                </button>
                            </form>
                        @else
                            <span style="font-size:.8rem;color:var(--text-muted)">Tu cuenta</span>
                        @endif
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="5" style="text-align:center;padding:3rem;color:var(--text-muted)">
                        No se encontraron usuarios.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>

    @if($usuarios->hasPages())
        <div style="padding:1rem 1.25rem;border-top:1px solid var(--border)">
            {{ $usuarios->links('catalogo.pagination') }}
        </div>
    @endif
</div>
@endsection
