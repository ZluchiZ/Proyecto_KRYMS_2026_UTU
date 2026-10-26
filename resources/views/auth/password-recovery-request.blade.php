<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="{{ asset('css/styles.css') }}">
    <link rel="stylesheet" href="{{ asset('css/login.css') }}">
    <link rel="stylesheet" href="{{ asset('css/internal.css') }}">
    <title>Recuperar contraseña</title>
</head>
<body class="internal-page internal-auth login-page">
    @include('partials.internal-header')
    <main class="login-shell">
        <section class="login-intro">
            <span class="login-eyebrow">EL GAUCHO VA</span>
            <h1>Recuperá el acceso<br><span>a tu cuenta.</span></h1>
            <p>La solicitud debe ser aprobada por un administrador antes de cambiar la contraseña.</p>
        </section>

        <section class="container login-card">
            <div class="login-heading">
                <span class="login-kicker">Seguridad de la cuenta</span>
                <h2>Recuperar contraseña</h2>
                <p>Ingresá el correo y la cédula asociados a tu cuenta.</p>
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

            <form method="POST" action="{{ route('password-recovery.submit') }}">
                @csrf
                <label for="recovery-email">Correo electrónico</label>
                <input id="recovery-email" type="email" name="email" value="{{ old('email') }}" autocomplete="email" required>
                <label for="recovery-cedula">Cédula</label>
                <input id="recovery-cedula" type="text" name="cedula" value="{{ old('cedula') }}" inputmode="numeric" maxlength="30" autocomplete="off" required>
                <button type="submit">Solicitar recuperación</button>
            </form>

            <p class="login-register"><a href="{{ route('login') }}">Volver a iniciar sesión</a></p>
        </section>
    </main>
    @if (session('status'))
        <dialog class="recovery-success-dialog" id="recovery-success-dialog" aria-labelledby="recovery-success-title">
            <div class="recovery-success-content">
                <span class="recovery-success-icon" aria-hidden="true">✓</span>
                <h2 id="recovery-success-title">Solicitud enviada</h2>
                <p role="status">{{ session('status') }}</p>
                <button type="button" data-close-recovery-dialog>Entendido</button>
            </div>
        </dialog>
        <script>
            const recoverySuccessDialog = document.getElementById('recovery-success-dialog');
            recoverySuccessDialog?.showModal();
            recoverySuccessDialog?.querySelector('[data-close-recovery-dialog]')?.addEventListener('click', () => recoverySuccessDialog.close());
            recoverySuccessDialog?.addEventListener('click', (event) => {
                if (event.target === recoverySuccessDialog) recoverySuccessDialog.close();
            });
        </script>
    @endif
</body>
</html>