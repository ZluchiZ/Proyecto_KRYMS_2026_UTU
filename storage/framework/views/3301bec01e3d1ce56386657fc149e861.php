<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Historial de ventas | <?php echo e($comercio->Nombre_Comercio); ?></title>
    <link rel="stylesheet" href="<?php echo e(asset('css/internal.css')); ?>">
    <link rel="stylesheet" href="<?php echo e(asset('css/order-history.css')); ?>">
</head>
<body class="internal-page order-history-page local-sales-history-page">
    <?php echo $__env->make('partials.internal-header', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
    <main class="order-history-main">
        <a class="profile-back-link" href="<?php echo e(route('dashboard.local')); ?>">&larr; Volver al panel</a>
        <header class="order-history-heading">
            <div>
                <p class="order-history-kicker"><?php echo e($comercio->Nombre_Comercio); ?></p>
                <h1>Historial de ventas</h1>
                <p>Consultá los pedidos recibidos, sus productos y sus clientes.</p>
            </div>
            <span class="order-history-count"><?php echo e($ventas->total()); ?> <?php echo e($ventas->total() === 1 ? 'venta' : 'ventas'); ?></span>
        </header>

        <?php if($ventas->isEmpty()): ?>
            <section class="history-empty">
                <span class="history-empty-mark" aria-hidden="true">↗</span>
                <h2>Todavía no hay ventas</h2>
                <p>Cuando recibas pedidos, van a aparecer en este historial.</p>
            </section>
        <?php else: ?>
            <section class="history-list" aria-label="Ventas anteriores">
                <?php $__currentLoopData = $ventas; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $venta): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <?php
                        $estadoVenta = strtolower($venta->Estado ?? 'pendiente');
                        $claseEstado = match ($estadoVenta) {
                            'rechazado', 'cancelado' => 'cancelled',
                            'entregado', 'completado', 'listo' => 'completed',
                            'aceptado', 'en_reparto' => 'progress',
                            default => 'pending',
                        };
                        $nombreCliente = trim(($venta->Nombre_Cliente ?? '').' '.($venta->Apellido_Cliente ?? ''));
                    ?>
                    <article class="history-card">
                        <header class="history-card-heading">
                            <div>
                                <span class="history-order-label">Pedido #<?php echo e($venta->N_Pedido); ?></span>
                                <span class="history-order-date"><?php echo e($venta->Fecha ?: 'Fecha no disponible'); ?><?php if($venta->Hora): ?> · <?php echo e(substr($venta->Hora, 0, 5)); ?><?php endif; ?></span>
                            </div>
                            <span class="history-status <?php echo e($claseEstado); ?>"><?php echo e(ucfirst(str_replace('_', ' ', $venta->Estado ?? 'pendiente'))); ?></span>
                        </header>

                        <div class="history-summary">
                            <div>
                                <span>Cliente</span>
                                <strong><?php echo e($nombreCliente ?: ($venta->Correo_Cliente ?? 'Cliente')); ?></strong>
                                <?php if($nombreCliente && $venta->Correo_Cliente): ?>
                                    <strong><?php echo e($venta->Correo_Cliente); ?></strong>
                                <?php endif; ?>
                            </div>
                            <div><span>Entrega</span><strong><?php echo e($venta->Ubicacion ?: 'No indicada'); ?></strong></div>
                            <div><span>Pago</span><strong><?php echo e(ucfirst($venta->Metodo_de_pago ?? 'No indicado')); ?></strong></div>
                            <div><span>Total</span><strong>$U <?php echo e(number_format((float) ($venta->Total_Local ?? 0), 2, ',', '.')); ?></strong></div>
                        </div>

                        <section class="history-store">
                            <header class="history-store-heading">
                                <h2>Productos</h2>
                                <span>Subpedido #<?php echo e($venta->N_Subpedido); ?></span>
                            </header>
                            <?php ($productos = $productosPorSubpedido->get($venta->N_Subpedido, collect())); ?>
                            <?php if($productos->isNotEmpty()): ?>
                                <ul class="history-products">
                                    <?php $__currentLoopData = $productos; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $producto): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <li><span><?php echo e($producto->Nombre_Producto); ?></span><strong>× <?php echo e($producto->Cantidad); ?></strong></li>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </ul>
                            <?php else: ?>
                                <p class="history-no-products">No hay productos detallados para este pedido.</p>
                            <?php endif; ?>
                        </section>
                    </article>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </section>

            <?php if($ventas->hasPages()): ?>
                <nav class="history-pagination" aria-label="Paginación de ventas">
                    <?php if($ventas->previousPageUrl()): ?>
                        <a href="<?php echo e($ventas->previousPageUrl()); ?>" rel="prev">&larr; Más recientes</a>
                    <?php else: ?>
                        <span></span>
                    <?php endif; ?>
                    <span>Página <?php echo e($ventas->currentPage()); ?> de <?php echo e($ventas->lastPage()); ?></span>
                    <?php if($ventas->nextPageUrl()): ?>
                        <a href="<?php echo e($ventas->nextPageUrl()); ?>" rel="next">Anteriores &rarr;</a>
                    <?php else: ?>
                        <span></span>
                    <?php endif; ?>
                </nav>
            <?php endif; ?>
        <?php endif; ?>
    </main>
</body>
</html><?php /**PATH /var/home/gsivoplas/htdocs/Proyecto_KRYMS_2026_UTU-main/resources/views/Local/HistorialVentas.blade.php ENDPATH**/ ?>