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
            <?php if(session('success')): ?>
                <p class="profile-form-message success" role="status"><?php echo e(session('success')); ?></p>
            <?php endif; ?>

            <?php if($errors->any()): ?>
                <div class="profile-form-message error" role="alert">
                    <?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $error): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <p><?php echo e($error); ?></p>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </div>
            <?php endif; ?>

            <form class="profile-edit-form" method="POST" action="<?php echo e(route('profile.update')); ?>">
                <?php echo csrf_field(); ?>
                <?php echo method_field('PATCH'); ?>
                <div class="profile-edit-field">
                    <label for="nombre">Nombre</label>
                    <input id="nombre" name="nombre" type="text" value="<?php echo e(old('nombre', $cliente->Nombre ?? '')); ?>" required>
                </div>
                <div class="profile-edit-field">
                    <label for="apellido">Apellido</label>
                    <input id="apellido" name="apellido" type="text" value="<?php echo e(old('apellido', $cliente->Apellido ?? '')); ?>" required>
                </div>
                <div class="profile-edit-field">
                    <label for="telefono">Teléfono</label>
                    <input id="telefono" name="telefono" type="text" value="<?php echo e(old('telefono', $cliente->{'Teléfono'} ?? '')); ?>" required>
                </div>
                <div class="profile-edit-field">
                    <label for="correo">Correo electrónico</label>
                    <input id="correo" type="email" value="<?php echo e($cliente->Email_Usuario ?? ''); ?>" disabled>
                </div>
                <div class="profile-edit-field profile-edit-field-wide">
                    <label for="direccion">Dirección de entrega</label>
                    <textarea id="direccion" name="direccion" rows="3"><?php echo e(old('direccion', $cliente->direccion ?? $cliente->Direccion_Entrega ?? '')); ?></textarea>
                </div>
                <p class="profile-edit-note">El correo de acceso y la cédula no se pueden modificar.</p>
                <div class="profile-edit-actions">
                    <button type="submit">Guardar cambios</button>
                </div>
            </form>

            <div class="customer-profile-actions">
                <a class="profile-primary-action" href="<?php echo e(route('cliente.location.form')); ?>">Editar ubicación</a>
                <a class="profile-secondary-action" href="<?php echo e(route('carrito')); ?>">Ver mi carrito</a>
                <a class="profile-secondary-action" href="<?php echo e(route('cliente.pedidos')); ?>">Historial de pedidos</a>
                <a class="profile-secondary-action" href="<?php echo e(route('cliente.reportes.index')); ?>">Mis reportes</a>
                <form method="POST" action="<?php echo e(route('logout')); ?>"><?php echo csrf_field(); ?><button type="submit">Cerrar sesión</button></form>
            </div>
        </section>
    </main>
</body>
</html><?php /**PATH /var/home/gsivoplas/htdocs/Proyecto_KRYMS_2026_UTU-main/resources/views/Cliente/Perfil.blade.php ENDPATH**/ ?>