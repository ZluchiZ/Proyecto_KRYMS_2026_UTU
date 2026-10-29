<header class="internal-header">
    <a class="internal-brand" href="<?php echo e(session('tipo_usuario') === 'repartidor' ? route('dashboard.repartidor') : route('home')); ?>" aria-label="El Gaucho Va">
        <img src="<?php echo e(asset('img/LogoGaucho_png.png')); ?>" alt="El Gaucho Va">
    </a>
    <div class="internal-header-actions">
        <?php echo $__env->make('partials.notifications-menu', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
        <?php if(session('tipo_usuario') !== 'repartidor'): ?>
            <a class="internal-home-link" href="<?php echo e(route('home')); ?>">Volver al inicio</a>
        <?php endif; ?>
    </div>
</header><?php /**PATH /opt/lampp/htdocs/Proyecto_KRYMS_2026_UTU-main/resources/views/partials/internal-header.blade.php ENDPATH**/ ?>