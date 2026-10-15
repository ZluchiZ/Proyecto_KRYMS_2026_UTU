<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="{{ asset('css/google-register.css') }}">
    <link rel="stylesheet" href="{{ asset('css/internal.css') }}">
    <title>Completar registro</title>
</head>
<body class="internal-page internal-auth">
@include('partials.internal-header')
    <div class="google-register-card">
        <h2>Completa tu registro</h2>
        <p>Ingresaste con Google con el correo <strong>{{ $email }}</strong>.</p>

        @if ($errors->any())
            <div class="google-register-errors">
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('google.complete.register') }}">
            @csrf

            <label for="cedula">Cédula</label>
            <input id="cedula" name="cedula" type="text" maxlength="8" required value="{{ old('cedula') }}">

            <label for="nombre">Nombre</label>
            <input id="nombre" name="nombre" type="text" required value="{{ old('nombre', $nombre) }}">

            <label for="apellido">Apellido</label>
            <input id="apellido" name="apellido" type="text" required value="{{ old('apellido') }}">

            <label for="telefono">Teléfono</label>
            <input id="telefono" name="telefono" type="text" maxlength="9" required value="{{ old('telefono') }}">

            <label for="nacimiento">Fecha de nacimiento</label>
            <input id="nacimiento" name="nacimiento" type="date" required value="{{ old('nacimiento') }}">

            <button type="submit" style="margin-top: 16px; width: 100%;">Crear cuenta</button>
        </form>
    </div>
</body>
</html>
