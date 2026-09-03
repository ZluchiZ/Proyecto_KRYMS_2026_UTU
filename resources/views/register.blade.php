<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="{{ asset('css/styles.css') }}">
    <title>Registro</title>
</head>
<body>

<div class="container">

<div class="formularioregistro">
    <h2>Registro Cliente</h2> 
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
    <button type="submit">Entrar</button>
  </form>
   <p id="mensajeError" style="color: red; display: none;">Por favor, ingresa un correo válido.</p>
   <p id="errorCampo" style="color:red; margin-top:4px; margin-bottom:10px;"></p>
   <p id="errorApellido" style="color:red; margin-top:4px; margin-bottom:10px;"></p>
   <p id="errorContrasena" style="color:red;"></p>
   <p id="errorFecha" style="color:red;"></p>
  </div>
</div> 


<div class="FormarParteLocal">
    <h2>¿Eres un local?</h2>
    <p>Si eres un local y deseas registrarte, haz clic en el siguiente botón:</p>
    <a href="{{ route('registerLocal') }}" class="btn-registrar-local">Registrarse como Local</a>
</div>

<div class="FormarParteRepartidor">
    <h2>¿Eres un repartidor?</h2>
    <p>Si eres un repartidor y deseas registrarte, haz clic en el siguiente botón:</p>
    <a href="{{ route('registerRepartidor') }}" class="btn-registrar-repartidor">Registrarse como Repartidor</a>
</div>

<script src="{{ asset('js/ValidacionRegistro.js') }}"></script>


</body>
</html>