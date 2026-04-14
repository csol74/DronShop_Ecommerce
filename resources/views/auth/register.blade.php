<x-guest-layout>
<div class="auth-wrap">
    <div class="auth-visual">
        <div class="auth-visual__content">
            <div class="auth-visual__logo">
                <img src="{{ asset('img/dronshop_logo.jpeg') }}" alt="DronShop Logo">
            </div>
            <h1 class="auth-visual__title">Únete a<br><span>DronShop</span></h1>
            <p class="auth-visual__sub">
                Crea tu cuenta y empieza a comprar con entrega express por drones en Bucaramanga y Colombia.
            </p>
        </div>
    </div>

    <div class="auth-form-side">
        <div class="auth-form-box">
            <h2>Crear cuenta</h2>
            <p>Completa el formulario para registrarte</p>

            <form method="POST" action="{{ route('register') }}">
                @csrf
                <div class="form-group">
                    <label class="form-label" for="name">Nombre completo</label>
                    <input id="name" type="text" name="name" class="form-control"
                           placeholder="Tu nombre" value="{{ old('name') }}" required autofocus>
                    @error('name')<div class="form-error">{{ $message }}</div>@enderror
                </div>

                <div class="form-group">
                    <label class="form-label" for="email">Correo electrónico</label>
                    <input id="email" type="email" name="email" class="form-control"
                           placeholder="tu@correo.com" value="{{ old('email') }}" required>
                    @error('email')<div class="form-error">{{ $message }}</div>@enderror
                </div>

                <div class="form-group">
                    <label class="form-label" for="password">Contraseña</label>
                    <input id="password" type="password" name="password" class="form-control"
                           placeholder="Mínimo 8 caracteres" required>
                    @error('password')<div class="form-error">{{ $message }}</div>@enderror
                </div>

                <div class="form-group">
                    <label class="form-label" for="password_confirmation">Confirmar contraseña</label>
                    <input id="password_confirmation" type="password" name="password_confirmation"
                           class="form-control" placeholder="Repite tu contraseña" required>
                </div>

                <button type="submit" class="btn btn-gold btn-submit" style="margin-top:.5rem">
                    Crear cuenta →
                </button>

                <p style="text-align:center;margin-top:1.5rem;font-size:.85rem;color:var(--text-muted)">
                    ¿Ya tienes cuenta?
                    <a href="{{ route('login') }}" style="color:var(--gold-400);font-weight:600">
                        Ingresar
                    </a>
                </p>
            </form>
        </div>
    </div>
</div>
</x-guest-layout>
