<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registro Local</title>
</head>
<body>
    
<form>
<input type="text" id="RUT" name="rut" inputmode="numeric" pattern="[0-9]{12}" maxlength="12" placeholder="RUT" value="{{ old('rut') }}" required>
<input type="text" id="NombreLocal" name="nombrelocal" placeholder="NombreLocal" value="{{ old('nombrelocal') }}" required>
<input type="text" id="Direccion" name="direccion" placeholder="Dirección" value="{{ old('direccion') }}" required>
<input type="text" id="Logo" name="logo" placeholder="URL del Logo" value="{{ old('logo') }}" required>    
<input type="text" id="NumeroCuenta" name="numerocuenta" placeholder="Número de cuenta" value="{{ old('numerocuenta') }}" required>
<input type="email" id="Correo" name="correo" placeholder="Correo Electrónico" value="{{ old('correo') }}" required> 
    <button type="submit">Entrar</button>
  </form>

</body>
</html>