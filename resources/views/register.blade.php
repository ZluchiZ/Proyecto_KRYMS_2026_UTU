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
  <form id="registroFormulario" method="POST" action="{{ route('cliente.store') }}">
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

    <input type="text" id="CI" name="cedula" inputmode="numeric" pattern="[0-9]{8}" maxlength="8" placeholder="Cédula de Identidad" value="{{ old('cedula') }}" required>
    <input type="email" id="email" name="email" placeholder="Correo Electrónico" value="{{ old('email') }}" required>
    <input type="text" id="Nombre" name="nombre" placeholder="Nombre" value="{{ old('nombre') }}" required>
    <input type="text" id="Apellido" name="apellido" placeholder="Apellido" value="{{ old('apellido') }}" required>
    <input type="text" id="Num" name="Numero" inputmode="numeric" pattern="[0-9]{9}" maxlength="9" placeholder="Telefono" value="{{ old('Numero') }}" required>
    <input type="date" id="fechaNacimiento" name="nacimiento" placeholder="Fecha de Nacimiento" value="{{ old('nacimiento') }}" required>
    <input type="password" id="password" name="password" placeholder="Contraseña" required minlength="8">
    <input type="password" id="password2" name="password2" placeholder="Repetir Contraseña" required minlength="8">
    <select name="opciones" id="opciones">
    <option value="opcion1">Cliente</option>
    <option value="opcion2">Local</option>
    <option value="opcion3">Repartidor</option>
</select>
    <button type="submit">Entrar</button>
  </form>
   <p id="mensajeError" style="color: red; display: none;">Por favor, ingresa un correo válido.</p>
   <p id="errorCampo" style="color:red; margin-top:4px; margin-bottom:10px;"></p>
   <p id="errorApellido" style="color:red; margin-top:4px; margin-bottom:10px;"></p>
   <p id="errorContrasena" style="color:red;"></p>
   <p id="errorFecha" style="color:red;"></p>
  </div>
</div> 

<script src="{{ asset('js/ValidacionRegistro.js') }}"></script>


</body>
</html>