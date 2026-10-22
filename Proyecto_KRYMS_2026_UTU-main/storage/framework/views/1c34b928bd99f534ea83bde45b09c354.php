<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="<?php echo e(asset('css/styles.css')); ?>">
    <link rel="stylesheet" href="<?php echo e(asset('css/login.css')); ?>">
    <link rel="stylesheet" href="<?php echo e(asset('css/internal.css')); ?>">
    <title>Inicio de sesión</title>
</head>
<body class="internal-page internal-auth login-page">
<?php echo $__env->make('partials.internal-header', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
<main class="login-shell">
    <section class="login-intro">
        <span class="login-eyebrow">EL GAUCHO VA</span>
        <h1>Pedí cerca,<br><span>recibí fácil.</span></h1>
        <p>Entrá para seguir tus pedidos y descubrir productos de tus locales favoritos.</p>
    </section>

    <section class="container login-card">
        <div class="login-heading">
            <span class="login-kicker">Tu cuenta</span>
            <h2>Iniciar sesión</h2>
            <p>Usá tus datos para continuar.</p>
        </div>

<?php if(session('error')): ?>
    <div class="alert alert-danger">
        <?php echo e(session('error')); ?>

    </div>
<?php endif; ?>

<?php if($errors->any()): ?>
    <div class="alert alert-danger">
        <ul class="error-list">
            <?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $error): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <li><?php echo e($error); ?></li>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </ul>
    </div>
<?php endif; ?>

        <form method="POST" action="/login">
            <?php echo csrf_field(); ?>
            <label for="login-email">Correo electrónico</label>
            <input id="login-email" type="email" name="email" placeholder="tu@correo.com" value="<?php echo e(old('email')); ?>" required>
            <label for="login-password">Contraseña</label>
            <input id="login-password" type="password" name="password" placeholder="Tu contraseña" required>
            <button type="submit">Entrar a mi cuenta</button>
        </form>

        <div class="login-divider"><span>o continuá con</span></div>
        <a href="<?php echo e(route('google.login')); ?>" class="google-btn">Continuar con Google</a>

        <p class="login-register">
            ¿Todavía no tenés cuenta?
            <a href="<?php echo e(route('register')); ?>">Crear cuenta</a>
        </p>
    </section>
</main>

</body>
</html><?php /**PATH /opt/lampp/htdocs/Proyecto_KRYMS_2026_UTU/resources/views/Login.blade.php ENDPATH**/ ?>