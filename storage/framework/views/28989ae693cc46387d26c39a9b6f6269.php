<?php if(session('tipo_usuario') && session('usuario_id')): ?>
    <div class="notifications" data-notifications>
        <button class="notifications-toggle" type="button" aria-label="Abrir notificaciones" aria-expanded="false" aria-controls="notifications-panel" data-notifications-toggle>
            <span aria-hidden="true">🔔</span>
            <span>Notificaciones</span>
            <?php if(($notificationSummary['unread'] ?? 0) > 0): ?>
                <span class="notifications-count"><?php echo e($notificationSummary['unread']); ?></span>
            <?php endif; ?>
        </button>
        <div class="notifications-panel" id="notifications-panel" data-notifications-panel hidden>
            <div class="notifications-heading">
                <strong>Notificaciones</strong>
                <?php if(($notificationSummary['unread'] ?? 0) > 0): ?>
                    <form method="POST" action="<?php echo e(route('notifications.read-all')); ?>">
                        <?php echo csrf_field(); ?>
                        <?php echo method_field('PATCH'); ?>
                        <button type="submit">Marcar todas leídas</button>
                    </form>
                <?php endif; ?>
            </div>
            <?php $__empty_1 = true; $__currentLoopData = ($notificationSummary['items'] ?? collect()); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $notification): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                <a class="notification-item <?php echo e($notification->read_at ? '' : 'unread'); ?>" href="<?php echo e(route('notifications.open', $notification->id)); ?>">
                    <span><?php echo e($notification->message); ?></span>
                    <time datetime="<?php echo e($notification->created_at); ?>"><?php echo e(\Illuminate\Support\Carbon::parse($notification->created_at)->diffForHumans()); ?></time>
                </a>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                <p class="notifications-empty">No tenés notificaciones nuevas.</p>
            <?php endif; ?>
            <a class="notifications-all" href="<?php echo e(route('notifications.index')); ?>">Ver todas las notificaciones</a>
        </div>
    </div>
<?php endif; ?>
<?php /**PATH /var/home/gsivoplas/htdocs/Proyecto_KRYMS_2026_UTU-main/resources/views/partials/notifications-menu.blade.php ENDPATH**/ ?>