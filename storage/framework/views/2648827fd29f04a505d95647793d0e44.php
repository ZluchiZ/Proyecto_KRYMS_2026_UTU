<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="<?php echo e(asset('css/styles.css')); ?>">
    <link rel="stylesheet" href="<?php echo e(asset('css/login.css')); ?>">
    <link rel="stylesheet" href="<?php echo e(asset('css/internal.css')); ?>">
    <title>Crear nueva contraseña</title>
</head>
<body class="internal-page internal-auth login-page">
    <?php echo $__env->make('partials.internal-header', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
    <main class="login-shell">
        <section class="login-intro">
            <span class="login-eyebrow">EL GAUCHO VA</span>
            <h1>Una contraseña nueva,<br><span>un acceso seguro.</span></h1>
            <p>Elegí una contraseña de al menos ocho caracteres.</p>
        </section>

        <section class="container login-card">
            <div class="login-heading">
                <span class="login-kicker">Solicitud aprobada</span>
                <h2>Nueva contraseña</h2>
                <p>Este enlace se puede usar una sola vez.</p>
            </div>

            <?php if($errors->any()): ?>
                <div class="alert alert-danger" role="alert">
                    <ul class="error-list">
                        <?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $error): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <li><?php echo e($error); ?></li>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </ul>
                </div>
            <?php endif; ?>

            <form method="POST" action="<?php echo e(route('password-recovery.reset', [$recovery, $token])); ?>">
                <?php echo csrf_field(); ?>
                <label for="new-password">Nueva contraseña</label>
                <input id="new-password" type="password" name="password" autocomplete="new-password" minlength="8" required>
                <label for="new-password-confirmation">Confirmar contraseña</label>
                <input id="new-password-confirmation" type="password" name="password_confirmation" autocomplete="new-password" minlength="8" required>
                <button type="submit">Guardar nueva contraseña</button>
            </form>
        </section>
    </main>
</body>
</html><?php /**PATH /var/home/gsivoplas/htdocs/Proyecto_KRYMS_2026_UTU-main/resources/views/auth/password-reset.blade.php ENDPATH**/ ?>