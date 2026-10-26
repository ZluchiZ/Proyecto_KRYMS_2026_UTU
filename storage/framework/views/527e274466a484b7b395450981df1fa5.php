<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="<?php echo e(asset('css/styles.css')); ?>">
    <link rel="stylesheet" href="<?php echo e(asset('css/login.css')); ?>">
    <link rel="stylesheet" href="<?php echo e(asset('css/internal.css')); ?>">
    <title>Recuperar contraseña</title>
</head>
<body class="internal-page internal-auth login-page">
    <?php echo $__env->make('partials.internal-header', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
    <main class="login-shell">
        <section class="login-intro">
            <span class="login-eyebrow">EL GAUCHO VA</span>
            <h1>Recuperá el acceso<br><span>a tu cuenta.</span></h1>
            <p>La solicitud debe ser aprobada por un administrador antes de cambiar la contraseña.</p>
        </section>

        <section class="container login-card">
            <div class="login-heading">
                <span class="login-kicker">Seguridad de la cuenta</span>
                <h2>Recuperar contraseña</h2>
                <p>Ingresá el correo y la cédula asociados a tu cuenta.</p>
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

            <form method="POST" action="<?php echo e(route('password-recovery.submit')); ?>">
                <?php echo csrf_field(); ?>
                <label for="recovery-email">Correo electrónico</label>
                <input id="recovery-email" type="email" name="email" value="<?php echo e(old('email')); ?>" autocomplete="email" required>
                <label for="recovery-cedula">Cédula</label>
                <input id="recovery-cedula" type="text" name="cedula" value="<?php echo e(old('cedula')); ?>" inputmode="numeric" maxlength="30" autocomplete="off" required>
                <button type="submit">Solicitar recuperación</button>
            </form>

            <p class="login-register"><a href="<?php echo e(route('login')); ?>">Volver a iniciar sesión</a></p>
        </section>
    </main>
    <?php if(session('status')): ?>
        <dialog class="recovery-success-dialog" id="recovery-success-dialog" aria-labelledby="recovery-success-title">
            <div class="recovery-success-content">
                <span class="recovery-success-icon" aria-hidden="true">✓</span>
                <h2 id="recovery-success-title">Solicitud enviada</h2>
                <p role="status"><?php echo e(session('status')); ?></p>
                <button type="button" data-close-recovery-dialog>Entendido</button>
            </div>
        </dialog>
        <script>
            const recoverySuccessDialog = document.getElementById('recovery-success-dialog');
            recoverySuccessDialog?.showModal();
            recoverySuccessDialog?.querySelector('[data-close-recovery-dialog]')?.addEventListener('click', () => recoverySuccessDialog.close());
            recoverySuccessDialog?.addEventListener('click', (event) => {
                if (event.target === recoverySuccessDialog) recoverySuccessDialog.close();
            });
        </script>
    <?php endif; ?>
</body>
</html><?php /**PATH /opt/lampp/htdocs/Proyecto_KRYMS_2026_UTU-main/resources/views/auth/password-recovery-request.blade.php ENDPATH**/ ?>