<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="{{ asset('css/styles.css') }}">
    <link rel="stylesheet" href="{{ asset('css/register-repartidor.css') }}">
    <link rel="stylesheet" href="{{ asset('css/internal.css') }}">
    <title>Registro Repartidor</title>
</head>
<body class="internal-page internal-auth register-page rider-register-page">
@include('partials.internal-header')
    <main class="register-shell">
        <section class="register-intro">
            <span class="register-eyebrow">EL GAUCHO VA PARA REPARTIDORES</span>
            <h1>Mové la ciudad<br><span>con nosotros.</span></h1>
            <p>Sumate a El Gaucho Va, organizá tus entregas y generá ingresos con cada pedido.</p>
        </section>

        <div class="container register-content">
            <section class="rider-register-card">
                <div class="register-form-heading">
                    <span class="register-option-kicker">Cuenta de repartidor</span>
                    <h2>Registrate como repartidor</h2>
                    <p>Completá tus datos para empezar a repartir.</p>
                </div>

    <form method="POST" action="{{ route('repartidor.store') }}">
        @csrf
        @if ($errors->any())
            <div class="form-errors">
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif
        @if (session('error'))
            <div class="session-error">
                {{ session('error') }}
            </div>
        @endif
        <input type="text" id="cedula" name="cedula" inputmode="numeric" pattern="[0-9]{8}" maxlength="8" placeholder="Cédula de Identidad" value="{{ old('cedula') }}" required>
        <input type="email" id="correo" name="correo" placeholder="Correo Electrónico" value="{{ old('correo') }}" required>
        <input type="text" id="nombre" name="nombre" placeholder="Nombre" value="{{ old('nombre') }}" required>
        <input type="text" id="apellido" name="apellido" placeholder="Apellido" value="{{ old('apellido') }}" required>
        <input type="text" id="telefono" name="telefono" inputmode="numeric" pattern="[0-9]{9}" maxlength="9" placeholder="Teléfono" value="{{ old('telefono') }}" required>
        <input type="date" id="fecha_nacimiento" name="fecha_nacimiento" placeholder="Fecha de Nacimiento" value="{{ old('fecha_nacimiento') }}" required>
        <input type="password" id="contrasena" name="contrasena" placeholder="Contraseña" required minlength="8">
        <input type="password" id="contrasena_confirmation" name="contrasena_confirmation" placeholder="Repetir Contraseña" required minlength="8">
        <button type="submit">Registrar repartidor</button>
    












       
        <script src="{{ asset('js/ValidacionRegistro.js') }}"></script>
    </form>
            </section>
        </div>
    </main>
</body>
</html>