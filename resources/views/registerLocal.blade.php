<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registro Local</title>
</head>
<body>
    
<form method="GET" action="{{ route('login') }}">
<input type="text" id="rut" name="rut" inputmode="numeric" pattern="[0-9]{12}" maxlength="12" placeholder="RUT (Opcional)" value="{{ old('rut') }}">
<input type="text" id="cilocal" name="cedulalocal" inputmode="numeric" pattern="[0-9]{8}" maxlength="8" placeholder="Cédula de Identidad" value="{{ old('cedulalocal') }}" required>
<input type="text" id="nombrelocal" name="nombrelocal" placeholder="NombreLocal" value="{{ old('nombrelocal') }}" required>
<input type="text" id="direccionlocal" name="direccion" placeholder="Dirección" value="{{ old('direccion') }}" required>
<input type="text" id="logo" name="logo" placeholder="URL del Logo" value="{{ old('logo') }}" required>    
<input type="text" id="numerocuenta" name="numerocuentalocal" placeholder="Número de cuenta" value="{{ old('numerocuenta') }}" required>
<input type="email" id="correolocal" name="correolocal" placeholder="Correo Electrónico" value="{{ old('correo') }}" required> 
<input type="password" id="password" name="passwordlocal" placeholder="Contraseña" required minlength="8">
<input type="password" id="password2" name="password2local" placeholder="Repetir Contraseña" required minlength="8">

<button type="submit">Entrar</button>
  </form>
















  <script src="{{ asset('js/ValidacionRegistro.js') }}"></script>

</body>
</html>