@extends('layouts.app')
@section('title', 'SkyPass — Membresía Premium')

@section('content')

<!-- Hero -->
<section class="skypass-hero">
    <div class="skypass-badge">✦ Programa de fidelización exclusivo</div>
    <h1 class="skypass-title">Sky<span>Pass</span></h1>
    <p class="skypass-sub">
        La membresía que transforma tu forma de comprar. Envío gratis ilimitado
        y 10% de descuento en cada pedido, todos los meses.
    </p>

    @if(session('success'))
        <div class="flash flash-success" style="max-width:500px;margin:0 auto 1.5rem">
            {{ session('success') }}
        </div>
    @endif
    @if(session('error'))
        <div class="flash flash-error" style="max-width:500px;margin:0 auto 1.5rem">
            {{ session('error') }}
        </div>
    @endif
</section>

<!-- Beneficios -->
<div class="benefits-strip">
    @foreach([
        ['icon'=>'🚀','text'=>'Envío gratis ilimitado'],
        ['icon'=>'💰','text'=>'10% de descuento en todo'],
        ['icon'=>'⚡','text'=>'Prioridad en entregas dron'],
        ['icon'=>'🎯','text'=>'Acceso anticipado a ofertas'],
        ['icon'=>'🔔','text'=>'Notificaciones exclusivas'],
    ] as $b)
        <div class="benefit-chip"><span>{{ $b['icon'] }}</span> {{ $b['text'] }}</div>
    @endforeach
</div>

<!-- Si tiene SkyPass activo -->
@auth
    @if($skypass && $skypass->estaVigente())
        @php
            $total   = $skypass->inicio->diffInDays($skypass->vencimiento);
            $usado   = $skypass->inicio->diffInDays(now());
            $pct     = $total > 0 ? min(round(($usado / $total) * 100), 100) : 0;
        @endphp
        <div class="skypass-active-card" style="margin:0 auto 4rem">
            <div class="skypass-active-card__logo">✦ SkyPass Activo</div>

            <div style="display:grid;grid-template-columns:1fr 1fr;gap:1rem;margin-bottom:1.25rem">
                <div>
                    <div style="font-size:.72rem;text-transform:uppercase;letter-spacing:.1em;
                                color:var(--text-muted);margin-bottom:.3rem">Plan</div>
                    <div style="font-family:'Syne',sans-serif;font-weight:700;
                                color:var(--text-primary)">
                        {{ ucfirst($skypass->plan) }}
                    </div>
                </div>
                <div>
                    <div style="font-size:.72rem;text-transform:uppercase;letter-spacing:.1em;
                                color:var(--text-muted);margin-bottom:.3rem">Días restantes</div>
                    <div style="font-family:'Syne',sans-serif;font-weight:700;
                                color:var(--gold-400);font-size:1.3rem">
                        {{ $skypass->diasRestantes() }} días
                    </div>
                </div>
                <div>
                    <div style="font-size:.72rem;text-transform:uppercase;letter-spacing:.1em;
                                color:var(--text-muted);margin-bottom:.3rem">Inicio</div>
                    <div style="font-size:.875rem;color:var(--text-secondary)">
                        {{ $skypass->inicio->format('d/m/Y') }}
                    </div>
                </div>
                <div>
                    <div style="font-size:.72rem;text-transform:uppercase;letter-spacing:.1em;
                                color:var(--text-muted);margin-bottom:.3rem">Vence</div>
                    <div style="font-size:.875rem;color:var(--text-secondary)">
                        {{ $skypass->vencimiento->format('d/m/Y') }}
                    </div>
                </div>
            </div>

            <!-- Barra de tiempo -->
            <div style="font-size:.78rem;color:var(--text-muted);margin-bottom:.4rem">
                Tiempo consumido: {{ $pct }}%
            </div>
            <div class="skypass-progress">
                <div class="skypass-progress__fill" style="width:{{ $pct }}%"></div>
            </div>

            <!-- Beneficios activos -->
            <div style="margin-top:1.25rem;background:var(--bg-surface);
                        border:1px solid var(--border);border-radius:var(--radius-md);
                        padding:1rem">
                <div style="font-size:.78rem;font-weight:600;text-transform:uppercase;
                            letter-spacing:.08em;color:var(--text-muted);margin-bottom:.75rem">
                    Beneficios activos
                </div>
                <div style="display:flex;flex-direction:column;gap:.5rem;font-size:.875rem">
                    <div style="color:#4ade80">✓ Envío gratis en todos tus pedidos</div>
                    <div style="color:#4ade80">✓ 10% de descuento automático</div>
                    <div style="color:#4ade80">✓ Prioridad en entregas por dron</div>
                </div>
            </div>

            <form method="POST" action="{{ route('skypass.cancelar') }}" style="margin-top:1rem">
                @csrf
                <button type="submit" class="btn btn-ghost"
                        style="width:100%;justify-content:center;font-size:.82rem;
                               color:var(--text-muted)"
                        onclick="return confirm('¿Cancelar tu SkyPass? Los beneficios siguen hasta el vencimiento.')">
                    Cancelar suscripción
                </button>
            </form>
        </div>
    @endif
@endauth

<!-- Planes -->
<div style="text-align:center;margin-bottom:2rem">
    <h2 style="font-size:1.6rem">
        @auth
            @if($skypass && $skypass->estaVigente()) Renovar o cambiar plan
            @else Elige tu plan
            @endif
        @else
            Elige tu plan
        @endauth
    </h2>
    <p style="color:var(--text-muted);font-size:.9rem;margin-top:.4rem">
        Cancela cuando quieras. Sin permanencia.
    </p>
</div>

<div class="planes-grid">
    @foreach($planes as $key => $plan)
        <div class="plan-card {{ $key === 'trimestral' ? 'popular' : '' }}">
            @if($key === 'trimestral')
                <div class="plan-card__popular-badge">⭐ MÁS POPULAR</div>
            @endif

            <div class="plan-card__name">{{ $plan['label'] }}</div>

            <div>
                <div class="plan-card__price">
                    $ {{ number_format($plan['precio'], 0, ',', '.') }}
                </div>
                <div class="plan-card__price-sub">
                    COP / {{ $plan['meses'] === 1 ? 'mes' : ($plan['meses'] === 3 ? '3 meses' : 'año') }}
                </div>
                @if($plan['ahorro'])
                    <div class="plan-card__ahorro" style="margin-top:.5rem">
                        Ahorras {{ $plan['ahorro'] }} vs mensual
                    </div>
                @endif
            </div>

            <ul class="plan-features">
                <li>Envío gratis ilimitado</li>
                <li>10% descuento en toda la tienda</li>
                <li>Prioridad en entregas dron</li>
                <li>Acceso anticipado a ofertas</li>
                @if($key !== 'mensual')
                    <li>Badge exclusivo en tu perfil</li>
                @endif
                @if($key === 'anual')
                    <li>Soporte prioritario 24/7</li>
                @endif
            </ul>

            @auth
                <form method="POST" action="{{ route('skypass.suscribir') }}">
                    @csrf
                    <input type="hidden" name="plan" value="{{ $key }}">
                    <button type="submit" class="btn {{ $key === 'trimestral' ? 'btn-gold' : 'btn-outline-gold' }}"
                            style="width:100%;justify-content:center;padding:.85rem;font-size:.95rem">
                        @if($skypass && $skypass->estaVigente() && $skypass->plan === $key)
                            ✓ Plan actual — Renovar
                        @else
                            Suscribirme →
                        @endif
                    </button>
                </form>
            @else
                <a href="{{ route('login') }}" class="btn btn-outline-gold"
                   style="display:flex;justify-content:center;padding:.85rem;font-size:.95rem">
                    Iniciar sesión para suscribirse →
                </a>
            @endauth
        </div>
    @endforeach
</div>

<!-- FAQ -->
<div style="max-width:700px;margin:0 auto 5rem;padding:0 2rem">
    <h2 style="text-align:center;font-size:1.5rem;margin-bottom:2rem">Preguntas frecuentes</h2>
    @foreach([
        ['q'=>'¿Cuándo se aplican los descuentos?',
         'a'=>'Los descuentos y el envío gratis se aplican automáticamente en cada pedido mientras tu SkyPass esté activo. No necesitas ningún código.'],
        ['q'=>'¿Qué pasa cuando vence mi SkyPass?',
         'a'=>'Una vez vencido el período contratado, los beneficios se desactivan automáticamente. Podrás renovar en cualquier momento desde esta página.'],
        ['q'=>'¿Puedo cancelar antes del vencimiento?',
         'a'=>'Sí. Si cancelas, tus beneficios se mantienen activos hasta la fecha de vencimiento original. No hacemos reembolsos parciales.'],
        ['q'=>'¿El descuento aplica sobre productos ya en oferta?',
         'a'=>'El 10% se aplica sobre el precio base de cada producto antes del IVA. Se acumula con cualquier descuento de temporada.'],
    ] as $faq)
        <div style="border-bottom:1px solid var(--border);padding:1.25rem 0">
            <div style="font-family:'Syne',sans-serif;font-weight:700;
                        margin-bottom:.6rem;color:var(--text-primary)">
                {{ $faq['q'] }}
            </div>
            <div style="font-size:.9rem;color:var(--text-secondary);line-height:1.6">
                {{ $faq['a'] }}
            </div>
        </div>
    @endforeach
</div>

@endsection
