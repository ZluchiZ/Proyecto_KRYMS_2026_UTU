<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registrarse</title>
</head>
<body>
    <h2>Registrarse</h2> 
<form method="POST" action="{{ route('register.submit') }}">
    @csrf
    <input type="text" name="user" placeholder="Usuario">
    <input type="password" name="password" placeholder="Contraseña">
    <button type="submit">Entrar</button>
</form>
</body>
</html>