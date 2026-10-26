<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="{{ asset('css/styles.css') }}">
    <link rel="stylesheet" href="{{ asset('css/login.css') }}">
    <link rel="stylesheet" href="{{ asset('css/internal.css') }}">
    <title>Verificar correo</title>
</head>
<body class="internal-page internal-auth login-page">
@include('partials.internal-header')
<main class="login-shell">
    <section class="login-intro">
        <span class="login-eyebrow">EL GAUCHO VA</span>
        <h1>Un paso más<br><span>para empezar.</span></h1>
        <p>Confirmá que este correo te pertenece para activar tu cuenta.</p>
    </section>

    <section class="container login-card">
        <div class="login-heading">
            <span class="login-kicker">Verificación de correo</span>
            <h2>Ingresá tu código</h2>
            <p>Enviamos un código a <strong>{{ $email }}</strong>. Es válido durante 5 minutos.</p>
        </div>

        @if (session('error'))
            <div class="alert alert-danger" role="alert">{{ session('error') }}</div>
        @endif
        @if (session('success'))
            <div class="alert alert-success" role="status">{{ session('success') }}</div>
        @endif
        @if ($errors->any())
            <div class="alert alert-danger" role="alert">
                @foreach ($errors->all() as $error)
                    <p>{{ $error }}</p>
                @endforeach
            </div>
        @endif

        <form method="POST" action="{{ route('email-verification.verify') }}">
            @csrf
            <label for="verification-code">Código de 6 dígitos</label>
            <input
                id="verification-code"
                type="text"
                name="code"
                inputmode="numeric"
                autocomplete="one-time-code"
                pattern="[0-9]{6}"
                maxlength="6"
                value="{{ old('code') }}"
                required
                autofocus
            >
            <button type="submit">Confirmar correo</button>
        </form>

        <form method="POST" action="{{ route('email-verification.resend') }}" class="verification-resend">
            @csrf
            <p id="resend-countdown" data-available-at="{{ $resendAvailableAt->timestamp }}">
                Podés solicitar otro código en <span id="resend-seconds"></span> segundos.
            </p>
            <button id="resend-button" type="submit" disabled>Reenviar código</button>
        </form>
    </section>
</main>
<script>
    const countdown = document.getElementById('resend-countdown');
    const secondsLabel = document.getElementById('resend-seconds');
    const resendButton = document.getElementById('resend-button');
    const availableAt = Number(countdown.dataset.availableAt);

    function updateResendCountdown() {
        const remaining = Math.max(0, availableAt - Math.floor(Date.now() / 1000));
        secondsLabel.textContent = remaining;
        resendButton.disabled = remaining > 0;
        countdown.hidden = remaining === 0;
    }

    updateResendCountdown();
    window.setInterval(updateResendCountdown, 1000);
</script>
</body>
</html>
