<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="{{ asset('css/styles.css') }}">
    <link rel="stylesheet" href="{{ asset('css/register.css') }}">
    <link rel="stylesheet" href="{{ asset('css/internal.css') }}">
    <title>Registro</title>
</head>
<body class="internal-page internal-auth register-page">
@include('partials.internal-header')
<main class="register-shell">
    <section class="register-intro">
        <span class="register-eyebrow">EL GAUCHO VA</span>
        <h1>Tu próxima<br><span>entrega empieza acá.</span></h1>
        <p>Creá tu cuenta y pedí lo que necesitás en tus locales favoritos.</p>
    </section>

    <div class="container register-content">

<div class="formularioregistro">
    <h2>Registro Cliente</h2> 
  <form id="registroFormulario" method="POST" action="{{ route('cliente.store') }}">
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

    <input type="text" id="CI" name="cedula" inputmode="numeric" pattern="[0-9]{8}" maxlength="8" placeholder="Cédula de Identidad" value="{{ old('cedula') }}" required>
    <input type="email" id="email" name="email" placeholder="Correo Electrónico" value="{{ old('email') }}" required>
    <input type="text" id="Nombre" name="nombre" placeholder="Nombre" value="{{ old('nombre') }}" required>
    <input type="text" id="Apellido" name="apellido" placeholder="Apellido" value="{{ old('apellido') }}" required>
    <input type="text" id="Num" name="Numero" inputmode="numeric" pattern="[0-9]{9}" maxlength="9" placeholder="Telefono" value="{{ old('Numero') }}" required>
    <input type="date" id="fechaNacimiento" name="nacimiento" placeholder="Fecha de Nacimiento" value="{{ old('nacimiento') }}" required>
    <input type="password" id="password" name="password" placeholder="Contraseña" required minlength="8">
    <input type="password" id="password2" name="password2" placeholder="Repetir Contraseña" required minlength="8">
    <button type="submit">Registrar</button>
  </form>
    <p id="mensajeError" role="alert">Por favor, ingresa un correo válido.</p>
    <p id="errorCampo" role="alert"></p>
    <p id="errorApellido" role="alert"></p>
    <p id="errorContrasena" role="alert"></p>
    <p id="errorFecha" role="alert"></p>
  </div>
    <div class="register-options">
        <section class="FormarParteLocal">
            <span class="register-option-kicker">Para comercios</span>
            <h2>¿Eres un local?</h2>
            <p>Mostrá tus productos y recibí pedidos.</p>
            <a href="{{ route('registerLocal') }}" class="btn-registrar-local">Registrarme como local</a>
        </section>

        <section class="FormarParteRepartidor">
            <span class="register-option-kicker">Para repartidores</span>
            <h2>¿Eres repartidor?</h2>
            <p>Sumate y llevá pedidos a toda la ciudad.</p>
            <a href="{{ route('registerRepartidor') }}" class="btn-registrar-repartidor">Registrarme como repartidor</a>
        </section>
    </div>
    </div>
</main>

<script src="{{ asset('js/ValidacionRegistro.js') }}"></script>


</body>
</html>