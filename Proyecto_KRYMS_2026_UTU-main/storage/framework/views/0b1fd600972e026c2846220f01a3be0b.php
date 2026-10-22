<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="<?php echo e(asset('css/internal.css')); ?>">
    <title>Mi perfil | El Gaucho Va</title>
</head>
<body class="internal-page customer-profile-page">
    <?php echo $__env->make('partials.internal-header', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
    <main class="customer-profile-main">
        <a class="profile-back-link" href="<?php echo e(route('home')); ?>">&larr; Volver al inicio</a>
        <section class="customer-profile-card">
            <div class="customer-profile-cover">
                <span class="customer-avatar"><?php echo e(strtoupper(substr($cliente->Nombre ?? 'C', 0, 1))); ?></span>
                <div>
                    <span class="profile-kicker">Cuenta personal</span>
                    <h1><?php echo e($cliente->Nombre ?? 'Mi perfil'); ?> <?php echo e($cliente->Apellido ?? ''); ?></h1>
                    <p><?php echo e($cliente->Email_Usuario ?? 'Cliente de El Gaucho Va'); ?></p>
                </div>
            </div>
            <div class="customer-profile-grid">
                <div class="profile-info-block"><span>Correo electrónico</span><strong><?php echo e($cliente->Email_Usuario ?? 'No informado'); ?></strong></div>
                <div class="profile-info-block"><span>Teléfono</span><strong><?php echo e($cliente->{'Teléfono'} ?? 'No informado'); ?></strong></div>
                <div class="profile-info-block profile-info-wide"><span>Dirección guardada</span><strong><?php echo e($cliente->direccion ?? $cliente->Direccion_Entrega ?? 'Todavía no agregaste una dirección'); ?></strong></div>
            </div>
            <div class="customer-profile-actions">
                <a class="profile-primary-action" href="<?php echo e(route('cliente.location.form')); ?>">Editar ubicación</a>
                <a class="profile-secondary-action" href="<?php echo e(route('carrito')); ?>">Ver mi carrito</a>
                <form method="POST" action="<?php echo e(route('logout')); ?>"><?php echo csrf_field(); ?><button type="submit">Cerrar sesión</button></form>
            </div>
        </section>
    </main>
</body>
</html><?php /**PATH /opt/lampp/htdocs/Proyecto_KRYMS_2026_UTU/resources/views/Cliente/Perfil.blade.php ENDPATH**/ ?>