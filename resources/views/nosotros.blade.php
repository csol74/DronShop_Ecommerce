@extends('layouts.app')
@section('title', 'Nosotros')

@section('content')

<!-- Hero -->
<section class="nosotros-hero">
    <div style="display:inline-flex;align-items:center;gap:.5rem;
                background:var(--bg-surface);border:1px solid var(--border);
                border-radius:100px;padding:.35rem 1rem;font-size:.8rem;
                font-weight:600;color:var(--cyan-400);margin-bottom:1.25rem;
                text-transform:uppercase;letter-spacing:.08em">
        🚀 Desde Bucaramanga para Colombia
    </div>
    <h1 style="font-size:clamp(2rem,5vw,3.5rem);margin-bottom:1rem;
               background:linear-gradient(135deg,#F0F4FF 30%,var(--gold-400) 100%);
               -webkit-background-clip:text;-webkit-text-fill-color:transparent;
               background-clip:text">
        Redefiniendo el comercio<br>con tecnología de vanguardia
    </h1>
    <p style="font-size:1.05rem;color:var(--text-secondary);max-width:580px;
              margin:0 auto 2rem;line-height:1.7">
        DronShop nació con una visión simple pero poderosa: llevar lo que necesitas
        de la manera más rápida, segura e innovadora posible. Somos el primer
        marketplace colombiano con entrega por drones.
    </p>
</section>

<!-- Visión y Misión -->
<div class="nosotros-grid">
    <div class="nosotros-card">
        <div class="nosotros-card__icon">🎯</div>
        <div class="nosotros-card__title">Nuestra Misión</div>
        <p class="nosotros-card__text">
            Transformar la experiencia de compra en Colombia conectando a proveedores
            de calidad con clientes exigentes, a través de una plataforma tecnológica
            que integra inteligencia logística, drones autónomos y un servicio al cliente
            excepcional. Creemos que cada compra debe ser una experiencia memorable,
            desde el clic hasta la entrega en tu puerta.
        </p>
    </div>
    <div class="nosotros-card">
        <div class="nosotros-card__icon">🌟</div>
        <div class="nosotros-card__title">Nuestra Visión</div>
        <p class="nosotros-card__text">
            Ser la plataforma de comercio electrónico líder en Latinoamérica para 2030,
            reconocida por revolucionar la logística urbana mediante flotas de drones
            autónomos, reducir las emisiones de carbono en un 40% frente al reparto
            tradicional y empoderar a miles de proveedores locales para que lleguen
            a clientes en toda la región sin barreras.
        </p>
    </div>
</div>

<!-- Valores -->
<div style="text-align:center;margin-bottom:2rem;padding:0 2rem">
    <h2 style="font-size:1.8rem;margin-bottom:.5rem">Nuestros valores</h2>
    <p style="color:var(--text-muted);font-size:.95rem">Los principios que guían cada decisión</p>
</div>

<div class="values-grid">
    @foreach([
        ['icon'=>'⚡','title'=>'Velocidad',       'desc'=>'Cada segundo importa. Optimizamos cada proceso para que tu pedido llegue antes de lo esperado.'],
        ['icon'=>'🔒','title'=>'Confianza',        'desc'=>'Verificamos cada proveedor y aseguramos cada transacción. Tu seguridad es nuestra prioridad.'],
        ['icon'=>'🌱','title'=>'Sostenibilidad',   'desc'=>'Nuestros drones consumen 80% menos energía que los vehículos de reparto convencionales.'],
        ['icon'=>'🤝','title'=>'Comunidad',        'desc'=>'Apoyamos a proveedores locales colombianos para que crezcan junto a nosotros.'],
        ['icon'=>'💡','title'=>'Innovación',       'desc'=>'Invertimos en tecnología de punta: IA, drones y logística predictiva para el futuro.'],
        ['icon'=>'🎯','title'=>'Precisión',        'desc'=>'GPS de alta precisión y sistemas de seguimiento en tiempo real para cada entrega.'],
    ] as $val)
        <div class="value-item">
            <div class="value-item__icon">{{ $val['icon'] }}</div>
            <div class="value-item__title">{{ $val['title'] }}</div>
            <div class="value-item__desc">{{ $val['desc'] }}</div>
        </div>
    @endforeach
</div>

<!-- Stats -->
<div style="background:var(--bg-surface);border-top:1px solid var(--border);
            border-bottom:1px solid var(--border);padding:3rem 2rem;
            margin:2rem 0;text-align:center">
    <div style="max-width:900px;margin:0 auto;
                display:grid;grid-template-columns:repeat(4,1fr);gap:2rem">
        @foreach([
            ['num'=>'3',    'label'=>'Líneas de producto'],
            ['num'=>'+50',  'label'=>'Proveedores verificados'],
            ['num'=>'1',    'label'=>'Dron en operación'],
            ['num'=>'BGA',  'label'=>'Ciudad de origen'],
        ] as $stat)
            <div>
                <div style="font-family:'Syne',sans-serif;font-size:2.5rem;
                            font-weight:800;color:var(--gold-400)">{{ $stat['num'] }}</div>
                <div style="font-size:.875rem;color:var(--text-muted);margin-top:.25rem">
                    {{ $stat['label'] }}
                </div>
            </div>
        @endforeach
    </div>
</div>

<!-- Equipo -->
<div style="text-align:center;margin:3rem 0 2rem;padding:0 2rem">
    <h2 style="font-size:1.8rem;margin-bottom:.5rem">El equipo</h2>
    <p style="color:var(--text-muted);font-size:.95rem">Las personas detrás de DronShop</p>
</div>

<div class="team-grid">
    @foreach([
        ['name'=>'Cesar Solano',  'role'=>'CEO & Co-fundador'],
        ['name'=>'Isabel Vargas',  'role'=>'CTO & Dron Ops'],
        ['name'=>'Oscar Vera',   'role'=>'Head of Logistics'],
        ['name'=>'Felipe Afanador',   'role'=>'Head of Logistics'],
    ] as $member)
        <div class="team-card">
            <div class="team-card__name">{{ $member['name'] }}</div>
            <div class="team-card__role">{{ $member['role'] }}</div>
        </div>
    @endforeach
</div>

<!-- CTA -->
<div style="text-align:center;padding:3rem 2rem 5rem">
    <div style="background:var(--bg-card);border:1px solid var(--border);
                border-radius:var(--radius-xl);padding:3rem;max-width:600px;
                margin:0 auto">
        <div style="font-size:2rem;margin-bottom:1rem">🚁</div>
        <h2 style="font-size:1.5rem;margin-bottom:.75rem">
            ¿Listo para experimentar el futuro?
        </h2>
        <p style="color:var(--text-muted);margin-bottom:1.5rem;font-size:.95rem">
            Únete a los clientes que ya disfrutan de entregas express por dron en Bucaramanga.
        </p>
        <div style="display:flex;gap:1rem;justify-content:center;flex-wrap:wrap">
            <a href="{{ route('catalogo.index') }}" class="btn btn-gold"
               style="padding:.85rem 2rem;font-size:1rem">
                Ver catálogo →
            </a>
            <a href="{{ route('skypass.index') }}" class="btn btn-ghost"
               style="padding:.85rem 2rem;font-size:1rem">
                ✦ Conocer SkyPass
            </a>
        </div>
    </div>
</div>

@endsection
