<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo e(trim(($repartidor->Nombre ?? '').' '.($repartidor->Apellido ?? '')) ?: 'Repartidor'); ?> | El Gaucho Va</title>
    <link rel="stylesheet" href="<?php echo e(asset('css/internal.css')); ?>">
    <link rel="stylesheet" href="<?php echo e(asset('css/order-history.css')); ?>">
    <link rel="stylesheet" href="<?php echo e(asset('css/visual-refresh.css')); ?>">
</head>
<body class="internal-page internal-cart order-history-page courier-profile-page">
    <?php echo $__env->make('partials.internal-header', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
    <main class="order-history-main">
        <a class="profile-back-link" href="<?php echo e(route('cliente.pedidos')); ?>">&larr; Volver al historial</a>
        <header class="order-history-heading">
            <div>
                <p class="order-history-kicker">Perfil del repartidor</p>
                <h1><?php echo e(trim(($repartidor->Nombre ?? '').' '.($repartidor->Apellido ?? '')) ?: 'Repartidor'); ?></h1>
                <p>Información y calificaciones de tus entregas.</p>
            </div>
        </header>

        <section class="courier-profile-info" aria-label="Información del repartidor">
            <div>
                <span>Teléfono</span>
                <?php if($repartidor->{'Teléfono'}): ?>
                    <a href="tel:<?php echo e($repartidor->{'Teléfono'}); ?>"><?php echo e($repartidor->{'Teléfono'}); ?></a>
                <?php else: ?>
                    <strong>No informado</strong>
                <?php endif; ?>
            </div>
            <div>
                <span>Calificación promedio</span>
                <?php if($totalCalificaciones > 0): ?>
                    <strong><?php echo e(number_format((float) $promedioCalificaciones, 1, ',', '.')); ?> / 5</strong>
                    <span class="courier-profile-stars" aria-label="Promedio de <?php echo e(number_format((float) $promedioCalificaciones, 1, ',', '.')); ?> sobre 5"><?php echo e(str_repeat('★', (int) round($promedioCalificaciones))); ?><?php echo e(str_repeat('☆', 5 - (int) round($promedioCalificaciones))); ?></span>
                <?php else: ?>
                    <strong>Sin calificaciones</strong>
                <?php endif; ?>
                <small><?php echo e($totalCalificaciones); ?> <?php echo e($totalCalificaciones === 1 ? 'calificación' : 'calificaciones'); ?></small>
            </div>
        </section>

        <section class="courier-profile-reviews" aria-labelledby="courier-reviews-title">
            <h2 id="courier-reviews-title">Opiniones de clientes</h2>
            <?php if($resenas->isEmpty()): ?>
                <p class="courier-profile-empty">Todavía no hay calificaciones para este repartidor.</p>
            <?php else: ?>
                <div class="courier-profile-review-list">
                    <?php $__currentLoopData = $resenas; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $resena): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <?php ($puntuacion = max(0, min(5, (int) $resena->Puntuacion))); ?>
                        <article class="courier-profile-review">
                            <header>
                                <strong><?php echo e($resena->Nombre_Cliente ?: 'Cliente'); ?></strong>
                                <span aria-label="<?php echo e($puntuacion); ?> de 5 estrellas"><?php echo e(str_repeat('★', $puntuacion)); ?><?php echo e(str_repeat('☆', 5 - $puntuacion)); ?></span>
                            </header>
                            <p><?php echo e($resena->Comentario ?: 'El cliente no dejó un comentario.'); ?></p>
                        </article>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </div>
                <?php if($resenas->hasPages()): ?>
                    <nav class="history-pagination" aria-label="Paginación de calificaciones">
                        <?php if($resenas->previousPageUrl()): ?><a href="<?php echo e($resenas->previousPageUrl()); ?>" rel="prev">&larr; Anteriores</a><?php else: ?><span></span><?php endif; ?>
                        <span>Página <?php echo e($resenas->currentPage()); ?> de <?php echo e($resenas->lastPage()); ?></span>
                        <?php if($resenas->nextPageUrl()): ?><a href="<?php echo e($resenas->nextPageUrl()); ?>" rel="next">Siguientes &rarr;</a><?php else: ?><span></span><?php endif; ?>
                    </nav>
                <?php endif; ?>
            <?php endif; ?>
        </section>
    </main>
</body>
</html>
<?php /**PATH /var/home/gsivoplas/htdocs/Proyecto_KRYMS_2026_UTU-main/resources/views/courier-profile.blade.php ENDPATH**/ ?>