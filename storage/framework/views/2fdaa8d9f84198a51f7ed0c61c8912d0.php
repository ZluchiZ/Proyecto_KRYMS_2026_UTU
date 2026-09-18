<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="<?php echo e(asset('css/dashboard-repartidor.css')); ?>">
    <link rel="stylesheet" href="<?php echo e(asset('css/internal.css')); ?>">
    <title>Dashboard repartidor</title>
</head>
<body class="internal-page internal-dashboard">
    <?php echo $__env->make('partials.internal-header', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
    <main>
        <section class="profile">
            <div class="profile-info">
                <strong><?php echo e($nombreUsuario); ?></strong>
                <span><?php echo e($tipoUsuario); ?></span>
            </div>
            <form method="POST" action="<?php echo e(route('logout')); ?>">
                <?php echo csrf_field(); ?>
                <button type="submit">Cerrar sesión</button>
            </form>
        </section>

        <?php if(session('success')): ?>
            <div class="success"><?php echo e(session('success')); ?></div>
        <?php endif; ?>

        <section class="dashboard-panel">
            <div class="panel-header">
                <div>
                    <p class="eyebrow">Entrega</p>
                    <h1>Pedidos listos</h1>
                </div>
            </div>

            <?php if($pedidosListos->isEmpty()): ?>
                <div class="empty">No hay pedidos listos para repartir.</div>
            <?php else: ?>
                <div class="order-list">
                    <?php $__currentLoopData = $pedidosListos; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $pedido): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <article class="order-card">
                            <div class="order-header">
                                <strong>Pedido #<?php echo e($pedido->N_Pedido); ?></strong>
                                <span class="status-pill ready">Listo</span>
                            </div>

                            <div class="order-grid">
                                <p><strong>Local:</strong> <?php echo e($pedido->Nombre_Comercio); ?></p>
                                <p><strong>Producto:</strong> <?php echo e($pedido->Nombre_Producto); ?></p>
                                <p><strong>Cliente:</strong> <?php echo e(trim(($pedido->Nombre_Cliente ?? '').' '.($pedido->Apellido_Cliente ?? '')) ?: ($pedido->Correo_Cliente ?? 'Cliente')); ?></p>
                                <p><strong>Cantidad:</strong> <?php echo e($pedido->Cantidad); ?></p>
                                <p><strong>Dirección:</strong> <?php echo e($pedido->Direccion_Envio); ?></p>
                                <p><strong>Total:</strong> $U <?php echo e(number_format($pedido->Total ?? $pedido->Monto_Total, 2, ',', '.')); ?></p>
                                <?php if($pedido->Telefono_Cliente): ?>
                                    <p><strong>Teléfono:</strong> <?php echo e($pedido->Telefono_Cliente); ?></p>
                                <?php endif; ?>
                            </div>

                            <div class="order-actions">
                                <form method="POST" action="<?php echo e(route('repartidor.pedidos.estado', $pedido->N_Pedido)); ?>">
                                    <?php echo csrf_field(); ?>
                                    <?php echo method_field('PATCH'); ?>
                                    <input type="hidden" name="estado" value="aceptado">
                                    <button type="submit">Aceptar entrega</button>
                                </form>
                                <form method="POST" action="<?php echo e(route('repartidor.pedidos.estado', $pedido->N_Pedido)); ?>">
                                    <?php echo csrf_field(); ?>
                                    <?php echo method_field('PATCH'); ?>
                                    <input type="hidden" name="estado" value="rechazado">
                                    <button type="submit" class="secondary">Rechazar</button>
                                </form>
                            </div>
                        </article>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </div>
            <?php endif; ?>
        </section>

        <section class="dashboard-panel my-delivery">
            <div class="panel-header">
                <div>
                    <p class="eyebrow">Mi entrega</p>
                    <h2>Pedidos asignados</h2>
                </div>
            </div>

            <?php if($miEntrega->isEmpty()): ?>
                <div class="empty">Todavía no aceptaste ninguna entrega.</div>
            <?php else: ?>
                <div class="order-list">
                    <?php $__currentLoopData = $miEntrega; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $pedido): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <article class="order-card assigned">
                            <div class="order-header">
                                <strong>Pedido #<?php echo e($pedido->N_Pedido); ?></strong>
                                <span class="status-pill assigned">Asignado</span>
                            </div>

                            <div class="order-grid">
                                <p><strong>Local:</strong> <?php echo e($pedido->Nombre_Comercio); ?></p>
                                <p><strong>Producto:</strong> <?php echo e($pedido->Nombre_Producto); ?></p>
                                <p><strong>Cliente:</strong> <?php echo e(trim(($pedido->Nombre_Cliente ?? '').' '.($pedido->Apellido_Cliente ?? '')) ?: ($pedido->Correo_Cliente ?? 'Cliente')); ?></p>
                                <p><strong>Cantidad:</strong> <?php echo e($pedido->Cantidad); ?></p>
                                <p><strong>Dirección:</strong> <?php echo e($pedido->Direccion_Envio); ?></p>
                                <p><strong>Total:</strong> $U <?php echo e(number_format($pedido->Total ?? $pedido->Monto_Total, 2, ',', '.')); ?></p>
                                <?php if($pedido->Telefono_Cliente): ?>
                                    <p><strong>Teléfono:</strong> <?php echo e($pedido->Telefono_Cliente); ?></p>
                                <?php endif; ?>
                            </div>
                        </article>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </div>
            <?php endif; ?>
        </section>
    </main>
</body>
</html><?php /**PATH /opt/lampp/htdocs/Proyecto_KRYMS_2026_UTU/resources/views/Repartidor/DashboardRepartidor.blade.php ENDPATH**/ ?>