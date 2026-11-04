<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="<?php echo e(asset('css/styles.css')); ?>">
    <link rel="stylesheet" href="<?php echo e(asset('css/login.css')); ?>">
    <link rel="stylesheet" href="<?php echo e(asset('css/internal.css')); ?>">
    <title>Verificar correo</title>
</head>
<body class="internal-page internal-auth login-page">
<?php echo $__env->make('partials.internal-header', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
<main class="login-shell">
    <section class="login-intro">
        <span class="login-eyebrow">EL GAUCHO VA</span>
        <h1>Un paso más<br><span>para empezar.</span></h1>
        <p>Confirmá que este correo te pertenece para activar tu cuenta.</p>
    </section>

    <section class="container login-card">
        <div class="login-heading">
            <span class="login-kicker">Verificación de correo</span>
            <h2>Ingresá tu código</h2>
            <p>Enviamos un código a <strong><?php echo e($email); ?></strong>. Es válido durante 5 minutos.</p>
        </div>

        <?php if(session('error')): ?>
            <div class="alert alert-danger" role="alert"><?php echo e(session('error')); ?></div>
        <?php endif; ?>
        <?php if(session('success')): ?>
            <div class="alert alert-success" role="status"><?php echo e(session('success')); ?></div>
        <?php endif; ?>
        <?php if($errors->any()): ?>
            <div class="alert alert-danger" role="alert">
                <?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $error): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <p><?php echo e($error); ?></p>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="<?php echo e(route('email-verification.verify')); ?>">
            <?php echo csrf_field(); ?>
            <label for="verification-code">Código de 6 dígitos</label>
            <input
                id="verification-code"
                type="text"
                name="code"
                inputmode="numeric"
                autocomplete="one-time-code"
                pattern="[0-9]{6}"
                maxlength="6"
                value="<?php echo e(old('code')); ?>"
                required
                autofocus
            >
            <button type="submit">Confirmar correo</button>
        </form>

        <form method="POST" action="<?php echo e(route('email-verification.resend')); ?>" class="verification-resend">
            <?php echo csrf_field(); ?>
            <p id="resend-countdown" data-available-at="<?php echo e($resendAvailableAt->timestamp); ?>">
                Podés solicitar otro código en <span id="resend-seconds"></span> segundos.
            </p>
            <button id="resend-button" type="submit" disabled>Reenviar código</button>
        </form>
    </section>
</main>
<script>
    const countdown = document.getElementById('resend-countdown');
    const secondsLabel = document.getElementById('resend-seconds');
    const resendButton = document.getElementById('resend-button');
    const availableAt = Number(countdown.dataset.availableAt);

    function updateResendCountdown() {
        const remaining = Math.max(0, availableAt - Math.floor(Date.now() / 1000));
        secondsLabel.textContent = remaining;
        resendButton.disabled = remaining > 0;
        countdown.hidden = remaining === 0;
    }

    updateResendCountdown();
    window.setInterval(updateResendCountdown, 1000);
</script>
</body>
</html>
<?php /**PATH C:\xampp\htdocs\Proyecto_KRYMS_2026_UTU-main\resources\views/auth/verify-email.blade.php ENDPATH**/ ?>