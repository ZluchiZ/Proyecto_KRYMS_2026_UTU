<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="<?php echo e(asset('css/internal.css')); ?>">
    <title>Notificaciones | El Gaucho Va</title>
    <link rel="stylesheet" href="<?php echo e(asset('css/visual-refresh.css')); ?>">
</head>
<body class="internal-page notification-history-page">
    <?php echo $__env->make('partials.internal-header', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
    <main class="notification-list-page">
        <header class="notification-history-heading">
            <span class="notification-history-icon" aria-hidden="true">
                <svg viewBox="0 0 24 24" fill="none">
                    <path d="M18 8a6 6 0 0 0-12 0c0 7-3 7-3 9h18c0-2-3-2-3-9" />
                    <path d="M10 21h4" />
                </svg>
            </span>
            <div>
                <p class="notification-history-kicker">Centro de actividad</p>
                <h1>Tus notificaciones</h1>
                <p class="notification-history-description">Novedades sobre tu cuenta y tus pedidos, en un solo lugar.</p>
            </div>
        </header>

        <?php if(session('success')): ?>
            <p class="notification-history-feedback" role="status"><?php echo e(session('success')); ?></p>
        <?php endif; ?>
        <?php if($notifications->isEmpty()): ?>
            <section class="notification-history-empty">
                <span aria-hidden="true">✓</span>
                <h2>Todo al día</h2>
                <p>Cuando haya novedades, las vas a encontrar acá.</p>
            </section>
        <?php else: ?>
            <div class="notification-list">
                <?php $__currentLoopData = $notifications; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $notification): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <article class="notification-record <?php echo e($notification->read_at ? '' : 'unread'); ?>">
                        <span class="notification-record-icon" aria-hidden="true">
                            <?php if($notification->order_id): ?>
                                <span>↗</span>
                            <?php else: ?>
                                <span>•</span>
                            <?php endif; ?>
                        </span>
                        <div class="notification-record-content">
                        <?php if($notification->order_id): ?>
                            <a class="notification-record-message" href="<?php echo e(route('notifications.open', $notification->id)); ?>">
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
                            <span class="notification-record-message"><?php echo e($notification->message); ?></span>
                        <?php endif; ?>
                            <div class="notification-record-footer">
                                <time datetime="<?php echo e($notification->created_at); ?>"><?php echo e(\Illuminate\Support\Carbon::parse($notification->created_at)->format('d/m/Y · H:i')); ?></time>
                                <?php if(! $notification->read_at): ?>
                                    <form method="POST" action="<?php echo e(route('notifications.read', $notification->id)); ?>">
                                        <?php echo csrf_field(); ?>
                                        <?php echo method_field('PATCH'); ?>
                                        <button type="submit">Marcar como leída</button>
                                    </form>
                                <?php else: ?>
                                    <span class="notification-read-label">Leída</span>
                                <?php endif; ?>
                            </div>
                        </div>
                    </article>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </div>
            <div class="notification-pagination">
                <?php echo e($notifications->links()); ?>

            </div>
        <?php endif; ?>
    </main>
</body>
</html>
<?php /**PATH /var/home/gsivoplas/htdocs/Proyecto_KRYMS_2026_UTU-main/resources/views/notifications/index.blade.php ENDPATH**/ ?>