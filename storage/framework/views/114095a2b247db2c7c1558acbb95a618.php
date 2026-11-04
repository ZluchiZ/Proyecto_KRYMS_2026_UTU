<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="<?php echo e(asset('css/internal.css')); ?>">
    <title>Verificar correo | El Gaucho Va</title>
</head>
<body class="internal-page internal-auth">
    <?php echo $__env->make('partials.internal-header', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
    <main class="registration-verification-card">
        <span class="profile-kicker">Confirmación de correo</span>
        <h1>Revisá tu email</h1>
        <p>Enviamos un código de 6 dígitos a <strong><?php echo e($email); ?></strong>. El código vence en 15 minutos.</p>

        <?php if(session('success')): ?>
            <p class="success" role="status"><?php echo e(session('success')); ?></p>
        <?php endif; ?>

        <?php if($errors->any()): ?>
            <div class="error" role="alert">
                <?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $error): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <p><?php echo e($error); ?></p>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </div>
        <?php endif; ?>

        <?php if($expired): ?>
            <p class="error">El código venció. Solicitá uno nuevo para continuar.</p>
        <?php endif; ?>

        <form method="POST" action="<?php echo e(route('registration.verify')); ?>">
            <?php echo csrf_field(); ?>
            <input type="hidden" name="email" value="<?php echo e($email); ?>">
            <label for="code">Código de verificación</label>
            <input id="code" name="code" type="text" inputmode="numeric" autocomplete="one-time-code" pattern="[0-9]{6}" maxlength="6" required autofocus>
            <button type="submit">Verificar correo</button>
        </form>

        <form class="registration-resend-form" method="POST" action="<?php echo e(route('registration.verify.resend')); ?>">
            <?php echo csrf_field(); ?>
            <input type="hidden" name="email" value="<?php echo e($email); ?>">
            <button type="submit">Reenviar código</button>
        </form>
    </main>
</body>
</html>
<?php /**PATH C:\xampp\htdocs\Proyecto_KRYMS_2026_UTU-main\resources\views\auth\registration-verification.blade.php ENDPATH**/ ?>