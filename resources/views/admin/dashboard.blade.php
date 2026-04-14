@extends('layouts.app')
@section('title', 'Admin Panel')

@section('content')
<div style="max-width:1200px;margin:0 auto;padding:2.5rem 2rem">
    <div style="margin-bottom:2rem">
        <div style="font-size:.78rem;text-transform:uppercase;letter-spacing:.1em;
                    color:var(--gold-400);margin-bottom:.5rem">Panel de control</div>
        <h1 style="font-size:2rem">Administración DronShop</h1>
    </div>

    <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(220px,1fr));gap:1.25rem;margin-bottom:2.5rem">
        @foreach([
            ['icon'=>'📦','label'=>'Productos','val'=>\App\Models\Producto::count(),'color'=>'#C9A84C'],
            ['icon'=>'🏷️','label'=>'Categorías','val'=>\App\Models\Categoria::count(),'color'=>'#22D3EE'],
            ['icon'=>'🤝','label'=>'Proveedores','val'=>\App\Models\Proveedor::count(),'color'=>'#818CF8'],
            ['icon'=>'👥','label'=>'Usuarios','val'=>\App\Models\User::count(),'color'=>'#34D399'],
        ] as $stat)
            <div style="background:var(--bg-card);border:1px solid var(--border);
                        border-radius:var(--radius-lg);padding:1.5rem">
                <div style="font-size:1.75rem;margin-bottom:.5rem">{{ $stat['icon'] }}</div>
                <div style="font-family:'Syne',sans-serif;font-size:2rem;font-weight:800;
                            color:{{ $stat['color'] }}">{{ $stat['val'] }}</div>
                <div style="font-size:.85rem;color:var(--text-muted)">{{ $stat['label'] }}</div>
            </div>
        @endforeach
    </div>

    <div style="display:grid;grid-template-columns:1fr 1fr;gap:1.5rem">
        <div style="background:var(--bg-card);border:1px solid var(--border);
                    border-radius:var(--radius-lg);padding:1.5rem">
            <h3 style="font-size:1rem;margin-bottom:1rem">Acciones rápidas</h3>
            <div style="display:flex;flex-direction:column;gap:.6rem">
                <a href="{{ route('catalogo.index') }}" class="btn btn-ghost">Ver catálogo público →</a>
            </div>
        </div>
        <div style="background:var(--bg-card);border:1px solid var(--border);
                    border-radius:var(--radius-lg);padding:1.5rem">
            <h3 style="font-size:1rem;margin-bottom:1rem">Info de sesión</h3>
            <div style="font-size:.875rem;color:var(--text-secondary);display:flex;flex-direction:column;gap:.4rem">
                <span>👤 {{ auth()->user()->name }}</span>
                <span>📧 {{ auth()->user()->email }}</span>
                <span style="color:var(--gold-400);font-weight:600">🔑 Rol: {{ ucfirst(auth()->user()->role) }}</span>
            </div>
        </div>
    </div>
</div>
@endsection
