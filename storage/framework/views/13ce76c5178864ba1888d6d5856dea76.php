<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="<?php echo e(asset('css/styles.css')); ?>">
    <link rel="stylesheet" href="<?php echo e(asset('css/register.css')); ?>">
    <title>Registro</title>
</head>
<body>

<div class="container">

<div class="formularioregistro">
    <h2>Registro Cliente</h2> 
  <form id="registroFormulario" method="POST" action="<?php echo e(route('cliente.store')); ?>">
    <?php echo csrf_field(); ?>
    <?php if($errors->any()): ?>
        <div class="form-errors">
            <ul>
                <?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $error): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <li><?php echo e($error); ?></li>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </ul>
        </div>
    <?php endif; ?>

    <?php if(session('error')): ?>
        <div class="session-error">
            <?php echo e(session('error')); ?>

        </div>
    <?php endif; ?>

    <input type="text" id="CI" name="cedula" inputmode="numeric" pattern="[0-9]{8}" maxlength="8" placeholder="Cédula de Identidad" value="<?php echo e(old('cedula')); ?>" required>
    <input type="email" id="email" name="email" placeholder="Correo Electrónico" value="<?php echo e(old('email')); ?>" required>
    <input type="text" id="Nombre" name="nombre" placeholder="Nombre" value="<?php echo e(old('nombre')); ?>" required>
    <input type="text" id="Apellido" name="apellido" placeholder="Apellido" value="<?php echo e(old('apellido')); ?>" required>
    <input type="text" id="Num" name="Numero" inputmode="numeric" pattern="[0-9]{9}" maxlength="9" placeholder="Telefono" value="<?php echo e(old('Numero')); ?>" required>
    <input type="date" id="fechaNacimiento" name="nacimiento" placeholder="Fecha de Nacimiento" value="<?php echo e(old('nacimiento')); ?>" required>
    <input type="password" id="password" name="password" placeholder="Contraseña" required minlength="8">
    <input type="password" id="password2" name="password2" placeholder="Repetir Contraseña" required minlength="8">
    <button type="submit">Registrar</button>
  </form>
    <p id="mensajeError" role="alert">Por favor, ingresa un correo válido.</p>
    <p id="errorCampo" role="alert"></p>
    <p id="errorApellido" role="alert"></p>
    <p id="errorContrasena" role="alert"></p>
    <p id="errorFecha" role="alert"></p>
  </div>
</div> 


<div class="FormarParteLocal">
    <h2>¿Eres un local?</h2>
    <p>Si eres un local y deseas registrarte, haz clic en el siguiente botón:</p>
    <a href="<?php echo e(route('registerLocal')); ?>" class="btn-registrar-local">Registrarse como Local</a>
</div>

<div class="FormarParteRepartidor">
    <h2>¿Eres un repartidor?</h2>
    <p>Si eres un repartidor y deseas registrarte, haz clic en el siguiente botón:</p>
    <a href="<?php echo e(route('registerRepartidor')); ?>" class="btn-registrar-repartidor">Registrarse como Repartidor</a>
</div>

<script src="<?php echo e(asset('js/ValidacionRegistro.js')); ?>"></script>


</body>
</html><?php /**PATH C:\xampp\htdocs\Proyecto_KRYMS_2026_UTU-main\resources\views/register.blade.php ENDPATH**/ ?>