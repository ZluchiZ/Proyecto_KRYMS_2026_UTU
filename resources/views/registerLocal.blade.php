<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registro Local</title>
</head>
<body>
    
<form method="POST" action="{{ route('local.store') }}">
    @csrf
    <input type="text" id="rut" name="rut" inputmode="numeric" pattern="[0-9]{12}" maxlength="12" placeholder="RUT (Opcional)" value="{{ old('rut') }}">
    <input type="text" id="cedula" name="cedula" inputmode="numeric" pattern="[0-9]{8}" maxlength="8" placeholder="Cédula de Identidad" value="{{ old('cedula') }}" required>
    <input type="text" id="nombre" name="nombre" placeholder="Nombre del local" value="{{ old('nombre') }}" required>
    <input type="text" id="direccion" name="direccion" placeholder="Dirección" value="{{ old('direccion') }}" required>
    <input type="text" id="logo" name="logo" placeholder="URL del Logo" value="{{ old('logo') }}" required>
    <input type="text" id="numero_cuenta" name="numero_cuenta" placeholder="Número de cuenta" value="{{ old('numero_cuenta') }}" required>
    <input type="email" id="correo" name="correo" placeholder="Correo Electrónico" value="{{ old('correo') }}" required>
    <input type="password" id="contrasena" name="contrasena" placeholder="Contraseña" required minlength="8">
    <input type="password" id="contrasena_confirmation" name="contrasena_confirmation" placeholder="Repetir Contraseña" required minlength="8">

    <button type="submit">Registrar</button>
</form>
















  <script src="{{ asset('js/ValidacionRegistro.js') }}"></script>
















  <script src="{{ asset('js/ValidacionRegistro.js') }}"></script>

</body>
</html>