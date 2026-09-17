<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link rel="stylesheet" href="<?php echo e(asset('css/styles.css')); ?>">
  <link rel="stylesheet" href="<?php echo e(asset('css/register-local.css')); ?>">
    <title>Registro Local</title>
</head>
<body>
    
<form method="POST" action="<?php echo e(route('local.store')); ?>">
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
    <input type="text" id="rut" name="rut" inputmode="numeric" pattern="[0-9]{12}" maxlength="12" placeholder="RUT (Opcional)" value="<?php echo e(old('rut')); ?>">
    <input type="text" id="nombre" name="nombre" placeholder="Nombre del local" value="<?php echo e(old('nombre')); ?>" required>
    <input type="text" id="nombre_dueno" name="nombre_dueno" placeholder="Nombre del dueño" value="<?php echo e(old('nombre_dueno')); ?>" required>
    <input type="text" id="cedula_dueno" name="cedula_dueno" inputmode="numeric" maxlength="20" placeholder="Cédula de Identidad" value="<?php echo e(old('cedula_dueno')); ?>" required>
    <input type="text" id="horario" name="horario" placeholder="08:00 hrs - 18:00 hrs" pattern="([01][0-9]|2[0-3]):[0-5][0-9] hrs - ([01][0-9]|2[0-3]):[0-5][0-9] hrs" value="<?php echo e(old('horario')); ?>" required>
    <input type="text" id="direccion" name="direccion" placeholder="Dirección" value="<?php echo e(old('direccion')); ?>" required>
    <input type="text" id="logo" name="logo" placeholder="URL del Logo" value="<?php echo e(old('logo')); ?>" required>
    <input type="text" id="numero_cuenta" name="numero_cuenta" placeholder="Número de cuenta" value="<?php echo e(old('numero_cuenta')); ?>" required>
    <input type="email" id="correo" name="correo" placeholder="Correo Electrónico" value="<?php echo e(old('correo')); ?>" required>
    <input type="password" id="contrasena" name="contrasena" placeholder="Contraseña" required minlength="8">
    <input type="password" id="contrasena_confirmation" name="contrasena_confirmation" placeholder="Repetir Contraseña" required minlength="8">

    <button type="submit">Registrar</button>
</form>
















  <script src="<?php echo e(asset('js/ValidacionRegistro.js')); ?>"></script>
















  <script src="<?php echo e(asset('js/ValidacionRegistro.js')); ?>"></script>

</body>
</html><?php /**PATH C:\xampp\htdocs\Proyecto_KRYMS_2026_UTU-main\resources\views/registerLocal.blade.php ENDPATH**/ ?>