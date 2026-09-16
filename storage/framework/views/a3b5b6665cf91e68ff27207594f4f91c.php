<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Completar registro</title>
</head>
<body>
    <div style="max-width: 420px; margin: 60px auto; padding: 24px; border: 1px solid #ddd; border-radius: 12px; font-family: Arial, sans-serif;">
        <h2>Completa tu registro</h2>
        <p>Ingresaste con Google con el correo <strong><?php echo e($email); ?></strong>.</p>

        <?php if($errors->any()): ?>
            <div style="color: #b00020; margin-bottom: 12px;">
                <ul>
                    <?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $error): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <li><?php echo e($error); ?></li>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </ul>
            </div>
        <?php endif; ?>

        <form method="POST" action="<?php echo e(route('google.complete.register')); ?>">
            <?php echo csrf_field(); ?>

            <label for="cedula">Cédula</label>
            <input id="cedula" name="cedula" type="text" maxlength="8" required value="<?php echo e(old('cedula')); ?>">

            <label for="nombre">Nombre</label>
            <input id="nombre" name="nombre" type="text" required value="<?php echo e(old('nombre', $nombre)); ?>">

            <label for="apellido">Apellido</label>
            <input id="apellido" name="apellido" type="text" required value="<?php echo e(old('apellido')); ?>">

            <label for="telefono">Teléfono</label>
            <input id="telefono" name="telefono" type="text" maxlength="9" required value="<?php echo e(old('telefono')); ?>">

            <label for="nacimiento">Fecha de nacimiento</label>
            <input id="nacimiento" name="nacimiento" type="date" required value="<?php echo e(old('nacimiento')); ?>">

            <button type="submit" style="margin-top: 16px; width: 100%;">Crear cuenta</button>
        </form>
    </div>
</body>
</html>
<?php /**PATH C:\xampp\htdocs\Proyecto_KRYMS_2026_UTU-main\resources\views/auth/google-complete-register.blade.php ENDPATH**/ ?>