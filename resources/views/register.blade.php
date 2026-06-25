<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="{{ asset('css/styles.css') }}">
    <title>Registrarse</title>
</head>
<body>

<div class="container">

<div class="formularioregistro">
    <h2>Registrarse</h2> 
  <form id="registerForm" method="POST">
    @csrf
    <input type="text" id="CI" name="CI" inputmode="numeric" pattern="[0-9]{8}" maxlength="8" placeholder="Cédula de Identidad" required>

    <input type="email" id="email" name="email" placeholder="Correo Electrónico">
     <p id="mensajeError" style="color: red; display: none;">Por favor, ingresa un correo válido.</p>
    <input type="text" name="nombre" placeholder="Nombre">
    <input type="text" name="apellido" placeholder="Apellido">
    <input type="date" name="nacimiento" placeholder="Fecha de Nacimiento">
    <input type="password" name="password" placeholder="Contraseña">
    <input type="password" name="password2" placeholder="Repetir Contraseña">
    
    <select name="opciones" id="opciones">
    <option value="opcion1">Usuario</option>
    <option value="opcion2">Local</option>
    <option value="opcion3">Repartidor</option>
</select>
    <button type="submit">Entrar</button>
  </form>
</div>
</div>

<script src="{{ asset('js/ValidacionMail.js') }}"></script>
</body>
</html>