<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="<?php echo e(asset('css/styles.css')); ?>">
    <link rel="stylesheet" href="<?php echo e(asset('css/register-repartidor.css')); ?>">
    <title>Registro Repartidor</title>
</head>
<body>
    <form method="POST" action="<?php echo e(route('repartidor.store')); ?>">
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
        <input type="text" id="cedula" name="cedula" inputmode="numeric" pattern="[0-9]{8}" maxlength="8" placeholder="Cédula de Identidad" value="<?php echo e(old('cedula')); ?>" required>
        <input type="email" id="correo" name="correo" placeholder="Correo Electrónico" value="<?php echo e(old('correo')); ?>" required>
        <input type="text" id="nombre" name="nombre" placeholder="Nombre" value="<?php echo e(old('nombre')); ?>" required>
        <input type="text" id="apellido" name="apellido" placeholder="Apellido" value="<?php echo e(old('apellido')); ?>" required>
        <input type="text" id="telefono" name="telefono" inputmode="numeric" pattern="[0-9]{9}" maxlength="9" placeholder="Teléfono" value="<?php echo e(old('telefono')); ?>" required>
        <input type="date" id="fecha_nacimiento" name="fecha_nacimiento" placeholder="Fecha de Nacimiento" value="<?php echo e(old('fecha_nacimiento')); ?>" required>
        <input type="password" id="contrasena" name="contrasena" placeholder="Contraseña" required minlength="8">
        <input type="password" id="contrasena_confirmation" name="contrasena_confirmation" placeholder="Repetir Contraseña" required minlength="8">
        <button type="submit">Registrar</button>
    












       
        <script src="<?php echo e(asset('js/ValidacionRegistro.js')); ?>"></script>
    </form>
</body>
</html><?php /**PATH C:\xampp\htdocs\Proyecto_KRYMS_2026_UTU-main\resources\views/registerRepartidor.blade.php ENDPATH**/ ?>