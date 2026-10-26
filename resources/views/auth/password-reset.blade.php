<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="{{ asset('css/styles.css') }}">
    <link rel="stylesheet" href="{{ asset('css/login.css') }}">
    <link rel="stylesheet" href="{{ asset('css/internal.css') }}">
    <title>Crear nueva contraseña</title>
</head>
<body class="internal-page internal-auth login-page">
    @include('partials.internal-header')
    <main class="login-shell">
        <section class="login-intro">
            <span class="login-eyebrow">EL GAUCHO VA</span>
            <h1>Una contraseña nueva,<br><span>un acceso seguro.</span></h1>
            <p>Elegí una contraseña de al menos ocho caracteres.</p>
        </section>

        <section class="container login-card">
            <div class="login-heading">
                <span class="login-kicker">Solicitud aprobada</span>
                <h2>Nueva contraseña</h2>
                <p>Este enlace se puede usar una sola vez.</p>
            </div>

            @if ($errors->any())
                <div class="alert alert-danger" role="alert">
                    <ul class="error-list">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form method="POST" action="{{ route('password-recovery.reset', [$recovery, $token]) }}">
                @csrf
                <label for="new-password">Nueva contraseña</label>
                <input id="new-password" type="password" name="password" autocomplete="new-password" minlength="8" required>
                <label for="new-password-confirmation">Confirmar contraseña</label>
                <input id="new-password-confirmation" type="password" name="password_confirmation" autocomplete="new-password" minlength="8" required>
                <button type="submit">Guardar nueva contraseña</button>
            </form>
        </section>
    </main>
</body>
</html>