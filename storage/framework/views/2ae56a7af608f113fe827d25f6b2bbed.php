<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mis pedidos | El Gaucho Va</title>
    <link rel="stylesheet" href="<?php echo e(asset('css/internal.css')); ?>">
    <link rel="stylesheet" href="<?php echo e(asset('css/order-history.css')); ?>">
</head>
<body class="internal-page internal-cart order-history-page">
    <?php echo $__env->make('partials.internal-header', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
    <main class="order-history-main">
        <a class="profile-back-link" href="<?php echo e(route('cliente.profile')); ?>">&larr; Volver a mi perfil</a>
        <header class="order-history-heading">
            <div>
                <p class="order-history-kicker">Tu actividad</p>
                <h1>Historial de pedidos</h1>
                <p>Consultá el estado y los detalles de tus compras.</p>
            </div>
            <span class="order-history-count"><?php echo e($pedidos->total()); ?> <?php echo e($pedidos->total() === 1 ? 'pedido' : 'pedidos'); ?></span>
        </header>

        <?php if($pedidos->isEmpty()): ?>
            <section class="history-empty">
                <span class="history-empty-mark" aria-hidden="true">↗</span>
                <h2>Todavía no tenés pedidos</h2>
                <p>Cuando hagas una compra, vas a poder seguirla desde acá.</p>
                <a class="history-primary-link" href="<?php echo e(route('home')); ?>">Explorar productos</a>
            </section>
        <?php else: ?>
            <section class="history-list" aria-label="Pedidos anteriores">
                <?php $__currentLoopData = $pedidos; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $pedido): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <?php
                        $estadoPedido = strtolower($pedido->Estado ?? 'pendiente');
                        $claseEstado = match ($estadoPedido) {
                            'rechazado', 'cancelado' => 'cancelled',
                            'entregado', 'completado', 'listo' => 'completed',
                            'aceptado', 'en_reparto' => 'progress',
                            default => 'pending',
                        };
                    ?>
                    <article class="history-card">
                        <header class="history-card-heading">
                            <div>
                                <span class="history-order-label">Pedido #<?php echo e($pedido->N_Pedido); ?></span>
                                <span class="history-order-date"><?php echo e($pedido->Fecha ?: 'Fecha no disponible'); ?><?php if($pedido->Hora): ?> · <?php echo e(substr($pedido->Hora, 0, 5)); ?><?php endif; ?></span>
                            </div>
                            <span class="history-status <?php echo e($claseEstado); ?>"><?php echo e(ucfirst(str_replace('_', ' ', $pedido->Estado ?? 'pendiente'))); ?></span>
                        </header>

                        <div class="history-summary">
                            <div><span>Entrega en</span><strong><?php echo e($pedido->Ubicacion ?: 'Dirección no disponible'); ?></strong></div>
                            <div><span>Pago</span><strong><?php echo e(ucfirst($pedido->Metodo_de_pago ?? 'No indicado')); ?></strong></div>
                            <div><span>Total</span><strong>$U <?php echo e(number_format((float) ($pedido->Monto_Total ?? 0), 2, ',', '.')); ?></strong></div>
                        </div>

                        <?php $__currentLoopData = $pedido->locales; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $local): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <section class="history-store">
                                <header class="history-store-heading">
                                    <h2><?php echo e($local->nombre); ?></h2>
                                    <span><?php echo e(ucfirst(str_replace('_', ' ', $local->estado))); ?></span>
                                </header>
                                <?php if($local->productos->isNotEmpty()): ?>
                                    <ul class="history-products">
                                        <?php $__currentLoopData = $local->productos; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $producto): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                            <li><span><?php echo e($producto->Nombre_Producto); ?></span><strong>× <?php echo e($producto->Cantidad); ?></strong></li>
                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                    </ul>
                                <?php else: ?>
                                    <p class="history-no-products">No hay productos detallados para este local.</p>
                                <?php endif; ?>
                                <?php if($local->total !== null): ?>
                                    <p class="history-store-total">Subtotal del local <strong>$U <?php echo e(number_format((float) $local->total, 2, ',', '.')); ?></strong></p>
                                <?php endif; ?>
                            </section>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </article>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </section>

            <?php if($pedidos->hasPages()): ?>
                <nav class="history-pagination" aria-label="Paginación de pedidos">
                    <?php if($pedidos->previousPageUrl()): ?>
                        <a href="<?php echo e($pedidos->previousPageUrl()); ?>" rel="prev">&larr; Más recientes</a>
                    <?php else: ?>
                        <span></span>
                    <?php endif; ?>
                    <span>Página <?php echo e($pedidos->currentPage()); ?> de <?php echo e($pedidos->lastPage()); ?></span>
                    <?php if($pedidos->nextPageUrl()): ?>
                        <a href="<?php echo e($pedidos->nextPageUrl()); ?>" rel="next">Anteriores &rarr;</a>
                    <?php else: ?>
                        <span></span>
                    <?php endif; ?>
                </nav>
            <?php endif; ?>
        <?php endif; ?>
    </main>
</body>
</html><?php /**PATH C:\xampp\htdocs\Proyecto_KRYMS_2026_UTU-main\resources\views/Cliente/HistorialPedidos.blade.php ENDPATH**/ ?>