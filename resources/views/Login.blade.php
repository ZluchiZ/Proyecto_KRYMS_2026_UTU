<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="{{ asset('css/styles.css') }}">
    <link rel="stylesheet" href="{{ asset('css/login.css') }}">
    <title>Inicio de sesión</title>
</head>
<body>

<h2>Iniciar sesión</h2>

<div class="container">

@if (session('error'))
    <div class="alert alert-danger">
        {{ session('error') }}
    </div>
@endif

@if ($errors->any())
    <div class="alert alert-danger">
        <ul class="error-list">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<form method="POST" action="/login">
    @csrf
    <input type="email" name="email" placeholder="Correo Electrónico" value="{{ old('email') }}" required>
    <input type="password" name="password" placeholder="Contraseña" required>
    <button type="submit">Entrar</button>
</form>

   <h3>
    No tienes cuenta,
    <a href="{{ route('register') }}">¡Regístrate!</a>
</h3>

</div>

</body>
</html>