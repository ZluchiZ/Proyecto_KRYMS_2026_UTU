<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="<?php echo e(asset('css/carrito.css')); ?>">
    <link rel="stylesheet" href="<?php echo e(asset('css/internal.css')); ?>">
    <title>Mi carrito | El Gaucho Va</title>
</head>
<body class="internal-page internal-cart">
    <?php echo $__env->make('partials.internal-header', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
    <main>
        <header class="header">
            <div>
                <p class="cart-eyebrow">Tu pedido</p>
                <h1>Mi carrito</h1>
            </div>
            <strong class="cart-count"><?php echo e($items->sum('Cantidad')); ?> producto(s)</strong>
        </header>

        <?php if(session('success')): ?>
            <p class="success" role="status"><?php echo e(session('success')); ?></p>
        <?php endif; ?>
        <?php if($errors->any()): ?>
            <div class="error" role="alert">
                <?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $error): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <p><?php echo e($error); ?></p>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </div>
        <?php endif; ?>

        <?php if($items->isEmpty()): ?>
            <section class="empty-cart">
                <span class="empty-cart-icon">&#128722;</span>
                <p class="cart-eyebrow">Todavia no agregaste productos</p>
                <h2>Tu carrito esta vacio</h2>
                <p>Explora los locales y encontra algo rico para pedir.</p>
                <a class="empty-cart-action" href="<?php echo e(route('home')); ?>">Ver productos</a>
            </section>
        <?php else: ?>
            <section class="items" aria-label="Productos en el carrito">
                <?php $__currentLoopData = $items; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <article class="item">
                        <img src="<?php echo e($item->Foto_Producto); ?>" alt="<?php echo e($item->Nombre_Producto); ?>">
                        <div class="item-details">
                            <span class="item-store"><?php echo e($item->Nombre_Comercio ?? 'Local no disponible'); ?></span>
                            <h2><?php echo e($item->Nombre_Producto); ?></h2>
                            <?php
                                $descuentoItem = (float) ($item->Descuento_Porcentaje ?? 0);
                            ?>
                            <p>
                                <?php echo e($item->Cantidad); ?> x
                                <?php if($descuentoItem > 0): ?>
                                    <span class="old-price">$U <?php echo e(number_format($item->Precio, 2, ',', '.')); ?></span>
                                    <span class="new-price">$U <?php echo e(number_format($item->Precio_Con_Descuento, 2, ',', '.')); ?></span>
                                    <span class="discount-tag">-<?php echo e(number_format($descuentoItem, 0)); ?>%</span>
                                <?php else: ?>
                                    <span>$U <?php echo e(number_format($item->Precio, 2, ',', '.')); ?></span>
                                <?php endif; ?>
                            </p>
                            <strong>$U <?php echo e(number_format($item->Subtotal, 2, ',', '.')); ?></strong>
                        </div>
                        <form method="POST" action="<?php echo e(route('carrito.remove', $item->ID_Carrito)); ?>">
                            <?php echo csrf_field(); ?>
                            <?php echo method_field('DELETE'); ?>
                            <button type="submit" class="remove">Quitar</button>
                        </form>
                    </article>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </section>
            <section class="checkout">
                <div class="cart-total-box">
                    <span>Total del carrito</span>
                    <strong>$U <?php echo e(number_format($totalCarrito, 2, ',', '.')); ?></strong>
                </div>
                <div class="checkout-heading">
                    <span class="cart-eyebrow">Ultimo paso</span>
                    <h2>Confirmar pedido</h2>
                    <p>Completa los datos para recibir tu pedido.</p>
                </div>
                <form method="POST" action="<?php echo e(route('carrito.confirm')); ?>">
                    <?php echo csrf_field(); ?>
                    <label for="direccion-envio">Ubicacion de envio</label>
                    <input type="text" name="direccion_envio" id="direccion-envio" value="<?php echo e(old('direccion_envio', $direccionEntrega)); ?>" maxlength="500" placeholder="Calle, numero y barrio" required>
                    <label for="telefono-contacto">Telefono de contacto</label>
                    <input type="tel" name="telefono_contacto" id="telefono-contacto" value="<?php echo e(old('telefono_contacto', $telefonoUsuario)); ?>" maxlength="30" placeholder="Tu telefono">
                    <label for="referencias">Referencias para la entrega</label>
                    <textarea name="referencias" id="referencias" maxlength="1000" placeholder="Apartamento, esquina u otra indicacion"><?php echo e(old('referencias')); ?></textarea>
                    <label for="metodo-pago">Metodo de pago</label>
                    <select name="metodo_pago" id="metodo-pago" required>
                        <option value="efectivo">Efectivo contra entrega</option>
                        <option value="tarjeta">Tarjeta contra entrega</option>
                    </select>
                    <button type="submit" class="confirm">Confirmar todos los productos</button>
                </form>
            </section>
        <?php endif; ?>
    </main>
    <?php if(session('order_sent')): ?>
        <div class="toast" role="status" aria-live="polite">Pedido enviado correctamente (<?php echo e(session('order_sent')); ?> producto(s)).</div>
    <?php endif; ?>
</body>
</html><?php /**PATH /opt/lampp/htdocs/Proyecto_KRYMS_2026_UTU/resources/views/Cliente/CarritoNuevo.blade.php ENDPATH**/ ?>