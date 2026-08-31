<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registro Repartidor</title>
</head>
<body>
    <form method="GET" action="{{ route('login') }}">
        <input type="text" id="cirepartidor" name="cedularepartidor" inputmode="numeric" pattern="[0-9]{8}" maxlength="8" placeholder="Cédula de Identidad" value="{{ old('cedularepartidor') }}" required>
        <input type="email" id="emailrepartidor" name="email" placeholder="Correo Electrónico" value="{{ old('email') }}" required>
        <input type="text" id="nombrerepartidor" name="nombre" placeholder="Nombre" value="{{ old('nombre') }}" required>
        <input type="text" id="apellidorepartidor" name="apellido" placeholder="Apellido" value="{{ old('apellido') }}" required>
        <input type="text" id="numrepartidor" name="Numero" inputmode="numeric" pattern="[0-9]{9}" maxlength="9" placeholder="Telefono" value="{{ old('Numero') }}" required>
        <input type="date" id="nacimientorepartidor" name="nacimiento" placeholder="Fecha de Nacimiento" value="{{ old('nacimiento') }}" required>
        <input type="password" id="passwordrepartidor" name="password" placeholder="Contraseña" required minlength="8">
        <input type="password" id="passwordrepartidor2" name="password2" placeholder="Repetir Contraseña" required minlength="8">
        <button type="submit">Entrar</button>
    












       
        
    </form>
</body>
</html>