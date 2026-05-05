@extends('layouts.proveedor')
@section('title', 'Sin asignar')
@section('content')
<div style="text-align:center;padding:5rem 2rem">
    <div style="font-size:3rem;margin-bottom:1rem">⚠️</div>
    <h2 style="margin-bottom:.75rem">Tu cuenta no está vinculada a un proveedor</h2>
    <p style="color:var(--text-muted);margin-bottom:1.5rem">
        Contacta al administrador para que vincule tu usuario a un proveedor registrado.
    </p>
    <a href="{{ route('catalogo.index') }}" class="btn btn-ghost">← Ver catálogo</a>
</div>
@endsection
