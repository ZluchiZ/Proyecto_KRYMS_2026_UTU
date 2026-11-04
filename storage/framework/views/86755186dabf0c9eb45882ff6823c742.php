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

        <?php if(session('success')): ?>
            <p class="history-feedback history-feedback-success" role="status"><?php echo e(session('success')); ?></p>
        <?php endif; ?>
        <?php if($errors->any()): ?>
            <p class="history-feedback history-feedback-error" role="alert">Revisá los datos de la reseña e intentá nuevamente.</p>
        <?php endif; ?>

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
                            'requiere_decision_cliente' => 'cancelled',
                            'entregado', 'completado', 'listo' => 'completed',
                            'aceptado', 'en_reparto' => 'progress',
                            default => 'pending',
                        };
                    ?>
                    <article class="history-card" id="pedido-<?php echo e($pedido->N_Pedido); ?>">
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

                        <?php if($estadoPedido === 'entregado' && $pedido->CI_Repartidor): ?>
                            <section class="history-store courier-review" aria-label="Reseña del repartidor">
                                <?php if($pedido->resenaRepartidor): ?>
                                    <h2>Tu reseña al repartidor</h2>
                                    <p class="courier-review-rating" aria-label="<?php echo e($pedido->resenaRepartidor->Puntuacion); ?> de 5 estrellas">
                                        <?php echo e(str_repeat('★', (int) $pedido->resenaRepartidor->Puntuacion)); ?><?php echo e(str_repeat('☆', 5 - (int) $pedido->resenaRepartidor->Puntuacion)); ?>

                                    </p>
                                    <?php if($pedido->resenaRepartidor->Comentario): ?>
                                        <p><?php echo e($pedido->resenaRepartidor->Comentario); ?></p>
                                    <?php endif; ?>
                                <?php else: ?>
                                    <h2>¿Cómo fue la entrega?</h2>
                                    <p>Dejá una reseña para el repartidor de este pedido.</p>
                                    <form method="POST" action="<?php echo e(route('pedidos.reviews.courier.store', $pedido->N_Pedido)); ?>">
                                        <?php echo csrf_field(); ?>
                                        <label for="puntuacion-repartidor-<?php echo e($pedido->N_Pedido); ?>">Puntuación (1 a 5 estrellas)</label>
                                        <select id="puntuacion-repartidor-<?php echo e($pedido->N_Pedido); ?>" name="puntuacion" required>
                                            <option value="">Seleccionar puntuación</option>
                                            <?php for($puntuacion = 5; $puntuacion >= 1; $puntuacion--): ?>
                                                <option value="<?php echo e($puntuacion); ?>" <?php if(old('puntuacion') == $puntuacion): echo 'selected'; endif; ?>><?php echo e($puntuacion); ?> <?php echo e($puntuacion === 1 ? 'estrella' : 'estrellas'); ?></option>
                                            <?php endfor; ?>
                                        </select>
                                        <label for="comentario-repartidor-<?php echo e($pedido->N_Pedido); ?>">Comentario (opcional, máximo 250 caracteres)</label>
                                        <textarea id="comentario-repartidor-<?php echo e($pedido->N_Pedido); ?>" name="comentario" maxlength="250"><?php echo e(old('comentario')); ?></textarea>
                                        <?php $__errorArgs = ['puntuacion'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><small class="review-field-error"><?php echo e($message); ?></small><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                                        <?php $__errorArgs = ['comentario'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><small class="review-field-error"><?php echo e($message); ?></small><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                                        <button type="submit">Enviar reseña</button>
                                    </form>
                                <?php endif; ?>
                            </section>
                        <?php endif; ?>

                        <?php if($estadoPedido === 'en_reparto' && $pedido->Codigo_Secreto): ?>
                            <section class="history-store" aria-label="Código secreto de entrega">
                                <h2>Código secreto de entrega</h2>
                                <p>Compartí este código con el repartidor cuando recibas tu pedido. Solo vos podés verlo.</p>
                                <strong><?php echo e($pedido->Codigo_Secreto); ?></strong>
                            </section>
                        <?php endif; ?>

                        <?php if($estadoPedido === 'en_reparto'): ?>
                            <?php
                                $etapasEntrega = [
                                    'productos_recolectados' => 'El gaucho recogió todos sus productos',
                                    'en_camino' => 'El gaucho va en camino',
                                    'en_puerta' => 'El gaucho está en tu puerta',
                                ];
                                $etapaActual = $pedido->Estado_Entrega;
                                $etapaActualIndice = array_search($etapaActual, array_keys($etapasEntrega), true);
                            ?>
                            <section class="history-store" aria-label="Progreso de entrega">
                                <h2>Seguimiento de entrega</h2>
                                <?php if(! $etapaActual): ?>
                                    <p>Esperando que el repartidor recoja todos los productos.</p>
                                <?php endif; ?>
                                <ol class="delivery-progress">
                                    <?php $__currentLoopData = $etapasEntrega; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $etapa => $etiqueta): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <li class="<?php echo e($etapaActual === $etapa ? 'current' : ($etapaActualIndice !== false && array_search($etapa, array_keys($etapasEntrega), true) < $etapaActualIndice ? 'complete' : '')); ?>">
                                            <?php echo e($etiqueta); ?>

                                        </li>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </ol>
                            </section>
                        <?php endif; ?>

                        <?php if($estadoPedido === 'requiere_decision_cliente'): ?>
                            <div class="history-store">
                                <strong>Un comercio rechazó su parte del pedido.</strong>
                                <p>Podés continuar sin los comercios que rechazaron o cancelar el pedido completo.</p>
                                <?php if($pedido->locales->contains(fn ($local) => ! in_array($local->estado, ['rechazado', 'omitido', 'cancelado'], true))): ?>
                                    <form method="POST" action="<?php echo e(route('pedidos.client-decision', $pedido->N_Pedido)); ?>">
                                        <?php echo csrf_field(); ?>
                                        <?php echo method_field('PATCH'); ?>
                                        <input type="hidden" name="decision" value="continuar">
                                        <button type="submit">Continuar sin esos comercios</button>
                                    </form>
                                <?php endif; ?>
                                <form method="POST" action="<?php echo e(route('pedidos.client-decision', $pedido->N_Pedido)); ?>" onsubmit="return confirm('¿Cancelar el pedido completo?');">
                                    <?php echo csrf_field(); ?>
                                    <?php echo method_field('PATCH'); ?>
                                    <input type="hidden" name="decision" value="cancelar">
                                    <button type="submit">Cancelar pedido completo</button>
                                </form>
                            </div>
                        <?php endif; ?>

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
                                <?php if($local->total !== null && $local->estado !== 'omitido'): ?>
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
</html><?php /**PATH /var/home/gsivoplas/htdocs/Proyecto_KRYMS_2026_UTU-main/resources/views/Cliente/HistorialPedidos.blade.php ENDPATH**/ ?>