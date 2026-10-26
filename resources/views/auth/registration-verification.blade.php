<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="{{ asset('css/internal.css') }}">
    <title>Verificar correo | El Gaucho Va</title>
</head>
<body class="internal-page internal-auth">
    @include('partials.internal-header')
    <main class="registration-verification-card">
        <span class="profile-kicker">Confirmación de correo</span>
        <h1>Revisá tu email</h1>
        <p>Enviamos un código de 6 dígitos a <strong>{{ $email }}</strong>. El código vence en 15 minutos.</p>

        @if (session('success'))
            <p class="success" role="status">{{ session('success') }}</p>
        @endif

        @if ($errors->any())
            <div class="error" role="alert">
                @foreach ($errors->all() as $error)
                    <p>{{ $error }}</p>
                @endforeach
            </div>
        @endif

        @if ($expired)
            <p class="error">El código venció. Solicitá uno nuevo para continuar.</p>
        @endif

        <form method="POST" action="{{ route('registration.verify') }}">
            @csrf
            <input type="hidden" name="email" value="{{ $email }}">
            <label for="code">Código de verificación</label>
            <input id="code" name="code" type="text" inputmode="numeric" autocomplete="one-time-code" pattern="[0-9]{6}" maxlength="6" required autofocus>
            <button type="submit">Verificar correo</button>
        </form>

        <form class="registration-resend-form" method="POST" action="{{ route('registration.verify.resend') }}">
            @csrf
            <input type="hidden" name="email" value="{{ $email }}">
            <button type="submit">Reenviar código</button>
        </form>
    </main>
</body>
</html>
