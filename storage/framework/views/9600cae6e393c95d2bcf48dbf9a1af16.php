<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="<?php echo e(asset('css/internal.css')); ?>">
    <title>Notificaciones | El Gaucho Va</title>
</head>
<body class="internal-page">
    <?php echo $__env->make('partials.internal-header', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
    <main class="notification-list-page">
        <h1>Todas las notificaciones</h1>
        <?php if(session('success')): ?>
            <p role="status"><?php echo e(session('success')); ?></p>
        <?php endif; ?>
        <?php if($notifications->isEmpty()): ?>
            <p>No tenés notificaciones.</p>
        <?php else: ?>
            <div class="notification-list">
                <?php $__currentLoopData = $notifications; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $notification): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <article class="notification-item <?php echo e($notification->read_at ? '' : 'unread'); ?>">
                        <?php if($notification->order_id): ?>
                            <a href="<?php echo e(route('notifications.open', $notification->id)); ?>">
                                <span><?php echo e($notification->message); ?></span>
                                <?php if(! empty($notification->data)): ?>
                                    <small>
                                        <?php $__currentLoopData = $notification->data; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $value): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                            <?php if(is_scalar($value)): ?>
                                                <?php echo e(ucfirst(str_replace('_', ' ', $key))); ?>: <?php echo e($value); ?><?php if(! $loop->last): ?> · <?php endif; ?>
                                            <?php endif; ?>
                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                    </small>
                                <?php endif; ?>
                            </a>
                        <?php else: ?>
                            <span><?php echo e($notification->message); ?></span>
                        <?php endif; ?>
                        <time datetime="<?php echo e($notification->created_at); ?>"><?php echo e(\Illuminate\Support\Carbon::parse($notification->created_at)->format('d/m/Y H:i')); ?></time>
                        <?php if(! $notification->read_at): ?>
                            <form method="POST" action="<?php echo e(route('notifications.read', $notification->id)); ?>">
                                <?php echo csrf_field(); ?>
                                <?php echo method_field('PATCH'); ?>
                                <button type="submit">Marcar como leída</button>
                            </form>
                        <?php endif; ?>
                    </article>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </div>
            <?php echo e($notifications->links()); ?>

        <?php endif; ?>
    </main>
</body>
</html>
<?php /**PATH /var/home/gsivoplas/htdocs/Proyecto_KRYMS_2026_UTU-main/resources/views/notifications/index.blade.php ENDPATH**/ ?>