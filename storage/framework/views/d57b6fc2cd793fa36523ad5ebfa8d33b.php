<?php if(session('tipo_usuario') && session('usuario_id')): ?>
    <div class="notifications" data-notifications>
        <button class="notifications-toggle" type="button" aria-label="Notificaciones" aria-expanded="false" aria-controls="notifications-panel" data-notifications-toggle>
            <svg class="notifications-bell" aria-hidden="true" viewBox="0 0 24 24" fill="none">
                <path d="M18 8a6 6 0 0 0-12 0c0 7-3 7-3 9h18c0-2-3-2-3-9" />
                <path d="M10 21h4" />
            </svg>
            <span class="notifications-label">Notificaciones</span>
            <?php if(($notificationSummary['unread'] ?? 0) > 0): ?>
                <span class="notifications-count"><?php echo e($notificationSummary['unread']); ?></span>
            <?php endif; ?>
        </button>
        <div class="notifications-panel <?php echo e(request()->routeIs('cliente.profile') ? 'home-notifications-panel' : ''); ?>" id="notifications-panel" data-notifications-panel aria-label="Notificaciones recientes" hidden>
            <div class="notifications-heading">
                <div class="notifications-heading-copy">
                    <strong>Notificaciones</strong>
                    <span>Lo último sobre tu cuenta y pedidos</span>
                </div>
                <?php if(($notificationSummary['unread'] ?? 0) > 0): ?>
                    <form method="POST" action="<?php echo e(route('notifications.read-all')); ?>">
                        <?php echo csrf_field(); ?>
                        <?php echo method_field('PATCH'); ?>
                        <button type="submit">Marcar leídas</button>
                    </form>
                <?php endif; ?>
            </div>
            <?php $__empty_1 = true; $__currentLoopData = ($notificationSummary['items'] ?? collect()); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $notification): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                <a class="notification-item <?php echo e($notification->read_at ? '' : 'unread'); ?>" href="<?php echo e(route('notifications.open', $notification->id)); ?>">
                    <span><?php echo e($notification->message); ?></span>
                    <time datetime="<?php echo e($notification->created_at); ?>"><?php echo e(\Illuminate\Support\Carbon::parse($notification->created_at)->diffForHumans()); ?></time>
                </a>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                <div class="notifications-empty">
                    <span aria-hidden="true">✓</span>
                    <strong>Todo al día</strong>
                    <p>No tenés notificaciones nuevas.</p>
                </div>
            <?php endif; ?>
            <a class="notifications-all" href="<?php echo e(route('notifications.index')); ?>">Ver todas las notificaciones</a>
        </div>
    </div>
<?php endif; ?>
<?php /**PATH C:\xampp\htdocs\Proyecto_KRYMS_2026_UTU-main\resources\views/partials/notifications-menu.blade.php ENDPATH**/ ?>