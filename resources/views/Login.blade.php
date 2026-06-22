<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inicio de sesión</title>
</head>
<body>
<h2>Iniciar sesión</h2>
<form method="POST" action="{{ route('login.submit') }}">
    @csrf
    <input type="text" name="user" placeholder="Usuario">
    <input type="password" name="password" placeholder="Contraseña">
    <button type="submit">Entrar</button>
</form>

   <h3>
    No tienes cuenta,
    <a href="{{ route('register') }}">¡Regístrate!</a>
</h3>
</body>
</html>