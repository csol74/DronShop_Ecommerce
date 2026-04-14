<x-guest-layout>
<div class="auth-wrap">
    <!-- Visual lado izquierdo -->
    <div class="auth-visual">
        <div class="auth-visual__content">
            <div class="auth-visual__logo">
                <img src="{{ asset('img/dronshop_logo.jpeg') }}" alt="DronShop Logo">
            </div>
            <h1 class="auth-visual__title">Dron<span>Shop</span></h1>
            <p class="auth-visual__sub">
                La plataforma de comercio premium con entrega por drones en Colombia.
                Electrónica, deporte y moda al siguiente nivel.
            </p>
        </div>
    </div>

    <!-- Formulario lado derecho -->
    <div class="auth-form-side">
        <div class="auth-form-box">
            <h2>Bienvenido de nuevo</h2>
            <p>Ingresa tus credenciales para acceder</p>

            @if($errors->any())
                <div style="background:#1c0a0a;border:1px solid #7f1d1d;border-radius:10px;padding:.85rem 1rem;margin-bottom:1rem;font-size:.85rem;color:#fca5a5;">
                    {{ $errors->first() }}
                </div>
            @endif

            <form method="POST" action="{{ route('login') }}">
                @csrf
                <div class="form-group">
                    <label class="form-label" for="email">Correo electrónico</label>
                    <input id="email" type="email" name="email" class="form-control"
                           placeholder="tu@correo.com" value="{{ old('email') }}" required autofocus>
                    @error('email')<div class="form-error">{{ $message }}</div>@enderror
                </div>

                <div class="form-group">
                    <label class="form-label" for="password">Contraseña</label>
                    <input id="password" type="password" name="password" class="form-control"
                           placeholder="••••••••" required>
                    @error('password')<div class="form-error">{{ $message }}</div>@enderror
                </div>

                <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:1.5rem;font-size:.85rem;">
                    <label class="filter-check" style="color:var(--text-secondary)">
                        <input type="checkbox" name="remember">
                        <span class="filter-check__box"></span>
                        Recordarme
                    </label>
                    @if(Route::has('password.request'))
                        <a href="{{ route('password.request') }}"
                           style="color:var(--gold-400);font-size:.82rem;">¿Olvidaste tu contraseña?</a>
                    @endif
                </div>

                <button type="submit" class="btn btn-gold btn-submit">
                    Ingresar →
                </button>

                <p style="text-align:center;margin-top:1.5rem;font-size:.85rem;color:var(--text-muted)">
                    ¿No tienes cuenta?
                    <a href="{{ route('register') }}" style="color:var(--gold-400);font-weight:600">
                        Regístrate gratis
                    </a>
                </p>
            </form>
        </div>
    </div>
</div>
</x-guest-layout>
