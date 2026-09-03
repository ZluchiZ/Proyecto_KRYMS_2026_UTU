<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="{{ asset('css/styles.css') }}">
    <title>Registro Repartidor</title>
</head>
<body>
    <form method="POST" action="{{ route('repartidor.store') }}">
        @csrf
        @if ($errors->any())
            <div class="form-errors" style="color:red; margin-bottom: 1rem;">
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif
        @if (session('error'))
            <div class="session-error" style="color:red; margin-bottom: 1rem;">
                {{ session('error') }}
            </div>
        @endif
        <input type="text" id="cedula" name="cedula" inputmode="numeric" pattern="[0-9]{8}" maxlength="8" placeholder="Cédula de Identidad" value="{{ old('cedula') }}" required>
        <input type="email" id="correo" name="correo" placeholder="Correo Electrónico" value="{{ old('correo') }}" required>
        <input type="text" id="nombre" name="nombre" placeholder="Nombre" value="{{ old('nombre') }}" required>
        <input type="text" id="apellido" name="apellido" placeholder="Apellido" value="{{ old('apellido') }}" required>
        <input type="text" id="telefono" name="telefono" inputmode="numeric" pattern="[0-9]{9}" maxlength="9" placeholder="Teléfono" value="{{ old('telefono') }}" required>
        <input type="date" id="fecha_nacimiento" name="fecha_nacimiento" placeholder="Fecha de Nacimiento" value="{{ old('fecha_nacimiento') }}" required>
        <input type="password" id="contrasena" name="contrasena" placeholder="Contraseña" required minlength="8">
        <input type="password" id="contrasena_confirmation" name="contrasena_confirmation" placeholder="Repetir Contraseña" required minlength="8">
        <button type="submit">Registrar</button>
    












       
        <script src="{{ asset('js/ValidacionRegistro.js') }}"></script>
    </form>
</body>
</html>