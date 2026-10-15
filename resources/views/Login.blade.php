<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="{{ asset('css/styles.css') }}">
    <link rel="stylesheet" href="{{ asset('css/login.css') }}">
    <link rel="stylesheet" href="{{ asset('css/internal.css') }}">
    <title>Inicio de sesión</title>
</head>
<body class="internal-page internal-auth login-page">
@include('partials.internal-header')
<main class="login-shell">
    <section class="login-intro">
        <span class="login-eyebrow">EL GAUCHO VA</span>
        <h1>Pedí cerca,<br><span>recibí fácil.</span></h1>
        <p>Entrá para seguir tus pedidos y descubrir productos de tus locales favoritos.</p>
    </section>

    <section class="container login-card">
        <div class="login-heading">
            <span class="login-kicker">Tu cuenta</span>
            <h2>Iniciar sesión</h2>
            <p>Usá tus datos para continuar.</p>
        </div>

@if (session('error'))
    <div class="alert alert-danger">
        {{ session('error') }}
    </div>
@endif

@if ($errors->any())
    <div class="alert alert-danger">
        <ul class="error-list">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

        <form method="POST" action="/login">
            @csrf
            <label for="login-email">Correo electrónico</label>
            <input id="login-email" type="email" name="email" placeholder="tu@correo.com" value="{{ old('email') }}" required>
            <label for="login-password">Contraseña</label>
            <input id="login-password" type="password" name="password" placeholder="Tu contraseña" required>
            <button type="submit">Entrar a mi cuenta</button>
        </form>

        <div class="login-divider"><span>o continuá con</span></div>
        <a href="{{ route('google.login') }}" class="google-btn">Continuar con Google</a>

        <p class="login-register">
            ¿Todavía no tenés cuenta?
            <a href="{{ route('register') }}">Crear cuenta</a>
        </p>
    </section>
</main>

</body>
</html>