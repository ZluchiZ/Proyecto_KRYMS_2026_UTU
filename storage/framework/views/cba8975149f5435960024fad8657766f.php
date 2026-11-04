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
        <a class="profile-back-link" href="<?php echo e($type === 'cliente' ? route('cliente.profile') : ($type === 'comercio' ? route('dashboard.local') : route('dashboard.repartidor'))); ?>">&larr; Volver</a>
        <section class="customer-profile-card">
            <div class="customer-profile-cover">
                <span class="customer-avatar"><?php echo e(strtoupper(substr($fields[0]['value'] ?: 'P', 0, 1))); ?></span>
                <div>
                    <span class="profile-kicker">Mi cuenta</span>
                    <h1>Mi perfil</h1>
                    <p><?php echo e($email); ?></p>
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
                <?php $__currentLoopData = $fields; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $field): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <div class="profile-edit-field <?php echo e($field['type'] === 'textarea' ? 'profile-edit-field-wide' : ''); ?>">
                        <label for="<?php echo e($field['name']); ?>"><?php echo e($field['label']); ?></label>
                        <?php if($field['type'] === 'textarea'): ?>
                            <textarea id="<?php echo e($field['name']); ?>" name="<?php echo e($field['name']); ?>" rows="3"><?php echo e(old($field['name'], $field['value'])); ?></textarea>
                        <?php else: ?>
                            <input id="<?php echo e($field['name']); ?>" name="<?php echo e($field['name']); ?>" type="text" value="<?php echo e(old($field['name'], $field['value'])); ?>" <?php if($field['required']): ?> required <?php endif; ?>>
                        <?php endif; ?>
                    </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                <p class="profile-edit-note">El correo de acceso y los identificadores de la cuenta no se pueden modificar desde esta pantalla.</p>
                <div class="profile-edit-actions">
                    <button type="submit">Guardar cambios</button>
                    <a href="<?php echo e($type === 'cliente' ? route('cliente.profile') : ($type === 'comercio' ? route('dashboard.local') : route('dashboard.repartidor'))); ?>">Cancelar</a>
                </div>
            </form>
        </section>
    </main>
</body>
</html>
<?php /**PATH /opt/lampp/htdocs/Proyecto_KRYMS_2026_UTU-main/resources/views/profile-edit.blade.php ENDPATH**/ ?>