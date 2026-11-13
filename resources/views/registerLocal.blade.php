<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link rel="stylesheet" href="{{ asset('css/styles.css') }}">
  <link rel="stylesheet" href="{{ asset('css/register-local.css') }}">
  <link rel="stylesheet" href="{{ asset('css/internal.css') }}">
    <title>Registro Local</title>
  <link rel="stylesheet" href="{{ asset('css/visual-refresh.css') }}">
</head>
<body class="internal-page internal-auth register-page local-register-page">
@include('partials.internal-header')
<main class="register-shell">
  <section class="register-intro">
    <span class="register-eyebrow">EL GAUCHO VA PARA COMERCIOS</span>
    <h1>Hacé crecer<br><span>tu local.</span></h1>
    <p>Sumá tu comercio a El Gaucho Va y acercá tus productos a más personas.</p>
  </section>

  <div class="container register-content">
    <section class="local-register-card">
      <div class="register-form-heading">
        <span class="register-option-kicker">Cuenta de comercio</span>
        <h2>Registrá tu local</h2>
        <p>Completá tus datos para empezar a recibir pedidos.</p>
      </div>

<form method="POST" action="{{ route('local.store') }}" enctype="multipart/form-data" data-native-submit="true">
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
    <input type="text" id="rut" name="rut" inputmode="numeric" pattern="[0-9]{12}" maxlength="12" placeholder="RUT (Opcional)" value="{{ old('rut') }}">
    <input type="text" id="nombre" name="nombre" placeholder="Nombre del local" value="{{ old('nombre') }}" required>
    <input type="text" id="nombre_dueno" name="nombre_dueno" placeholder="Nombre del dueño" value="{{ old('nombre_dueno') }}" required>
    <input type="text" id="cedula_dueno" name="cedula_dueno" inputmode="numeric" maxlength="20" placeholder="Cédula de Identidad" value="{{ old('cedula_dueno') }}" required>
    <div class="field-group horario-fields">
      <label for="horario_inicio">Horario de atención</label>
      <div class="dual-time-inputs">
        <input type="time" id="horario_inicio" name="horario_inicio" value="{{ old('horario_inicio') }}" required>
        <span class="time-separator">-</span>
        <input type="time" id="horario_fin" name="horario_fin" value="{{ old('horario_fin') }}" required>
      </div>
    </div>
    <input type="text" id="direccion" name="direccion" placeholder="Dirección" value="{{ old('direccion') }}" required>
    <input type="url" id="logo_url" name="logo_url" placeholder="URL del logo (opcional)" value="{{ old('logo_url') }}">
    <input type="file" id="logo" name="logo" accept="image/png,image/jpeg,image/webp" aria-label="Logo del local como archivo">
    <input type="text" id="numero_cuenta" name="numero_cuenta" placeholder="Número de cuenta" value="{{ old('numero_cuenta') }}" required>
    <input type="email" id="correo" name="correo" pattern="[^@\s]+@[^@\s]+\.[^@\s]+" title="Ingresá un correo válido, por ejemplo nombre@gmail.com." placeholder="Correo Electrónico" value="{{ old('correo') }}" required>
    <input type="password" id="contrasena" name="contrasena" pattern="(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[^A-Za-z0-9\s]).{8,}" title="Usá al menos 8 caracteres, una mayúscula, una minúscula, un número y un símbolo." placeholder="Contraseña" required minlength="8">
    <input type="password" id="contrasena_confirmation" name="contrasena_confirmation" pattern="(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[^A-Za-z0-9\s]).{8,}" title="Usá al menos 8 caracteres, una mayúscula, una minúscula, un número y un símbolo." placeholder="Repetir Contraseña" required minlength="8">

    <button type="submit">Registrar local</button>
</form>

      </section>
    </div>
  </main>
















  <script src="{{ asset('js/ValidacionRegistro.js') }}"></script>
















  <script src="{{ asset('js/ValidacionRegistro.js') }}"></script>

</body>
</html>