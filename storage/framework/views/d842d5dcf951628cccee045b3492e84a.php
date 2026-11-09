<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="<?php echo e(asset('css/carrito.css')); ?>">
    <link rel="stylesheet" href="<?php echo e(asset('css/internal.css')); ?>">
    <title>Mi carrito | El Gaucho Va</title>
    <link rel="stylesheet" href="<?php echo e(asset('css/visual-refresh.css')); ?>">
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
                <div class="cart-total-box" aria-label="Desglose del pedido">
                    <div class="cart-total-row">
                        <span>Subtotal de productos</span>
                        <strong>$U <?php echo e(number_format($subtotalCarrito, 2, ',', '.')); ?></strong>
                    </div>
                    <div class="cart-total-row">
                        <span>Comisión por servicio</span>
                        <strong>$U <?php echo e(number_format($comisionServicio, 2, ',', '.')); ?></strong>
                    </div>
                    <div class="cart-total-row cart-total-final">
                        <span>Total a pagar</span>
                        <strong>$U <?php echo e(number_format($totalCarrito, 2, ',', '.')); ?></strong>
                    </div>
                </div>
                <div class="checkout-heading">
                    <span class="cart-eyebrow">Ultimo paso</span>
                    <h2>Confirmar pedido</h2>
                    <p>Completa los datos para recibir tu pedido.</p>
                </div>
                <form method="POST" action="<?php echo e(route('carrito.confirm')); ?>">
                    <?php echo csrf_field(); ?>
                    <label for="direccion-envio">Ubicacion de envio</label>
                    <input type="text" name="direccion_envio" id="direccion-envio" value="<?php echo e(old('direccion_envio', $direccionEntrega)); ?>" maxlength="500" placeholder="Calle, numero y barrio" <?php $__errorArgs = ['direccion_envio'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> aria-describedby="direccion-envio-error" <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?> required>
                    <?php $__errorArgs = ['direccion_envio'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                        <small class="error" id="direccion-envio-error" role="alert"><?php echo e($message); ?></small>
                    <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                    <label for="telefono-contacto">Telefono de contacto</label>
                    <input type="tel" name="telefono_contacto" id="telefono-contacto" value="<?php echo e(old('telefono_contacto', $telefonoUsuario)); ?>" maxlength="30" placeholder="Tu telefono">
                    <label for="referencias">Referencias para la entrega</label>
                    <textarea name="referencias" id="referencias" maxlength="1000" placeholder="Apartamento, esquina u otra indicacion"><?php echo e(old('referencias')); ?></textarea>
                    <label for="metodo-pago">Metodo de pago</label>
                    <select name="metodo_pago" id="metodo-pago" required>
                        <option value="efectivo" <?php if(old('metodo_pago', 'efectivo') === 'efectivo'): echo 'selected'; endif; ?>>Efectivo contra entrega</option>
                        <option value="tarjeta" <?php if(old('metodo_pago') === 'tarjeta'): echo 'selected'; endif; ?>>Tarjeta</option>
                        <?php if($metodosPagoGuardados->isNotEmpty()): ?>
                            <option value="tarjeta_guardada" <?php if(old('metodo_pago') === 'tarjeta_guardada'): echo 'selected'; endif; ?>>Usar tarjeta guardada</option>
                        <?php endif; ?>
                    </select>
                    <fieldset id="tarjeta-simulada" class="simulated-card" hidden disabled>
                        <legend>Datos de tarjeta</legend>
                        <label for="numero-tarjeta">Numero de tarjeta</label>
                        <input type="text" name="numero_tarjeta" id="numero-tarjeta" inputmode="numeric" autocomplete="off" maxlength="19" placeholder="0000 0000 0000 0000" value="0000 0000 0000 0000">
                        <?php $__errorArgs = ['numero_tarjeta'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><small role="alert"><?php echo e($message); ?></small><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                        <label for="vencimiento-tarjeta">Vencimiento</label>
                        <input type="text" name="vencimiento_tarjeta" id="vencimiento-tarjeta" autocomplete="off" maxlength="5" placeholder="12/30" value="12/30">
                        <?php $__errorArgs = ['vencimiento_tarjeta'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><small role="alert"><?php echo e($message); ?></small><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                        <label for="cvv-tarjeta">Codigo de seguridad</label>
                        <input type="text" name="cvv_tarjeta" id="cvv-tarjeta" inputmode="numeric" autocomplete="off" maxlength="3" placeholder="000" value="000">
                        <?php $__errorArgs = ['cvv_tarjeta'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><small role="alert"><?php echo e($message); ?></small><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                        <label for="guardar-metodo-pago" class="save-payment-option">
                            <input type="checkbox" name="guardar_metodo_pago" id="guardar-metodo-pago" value="1" <?php if(old('guardar_metodo_pago')): echo 'checked'; endif; ?>>
                            Guardar método de pago
                        </label>
                    </fieldset>
                    <?php if($metodosPagoGuardados->isNotEmpty()): ?>
                        <fieldset id="tarjetas-guardadas" class="simulated-card" hidden disabled>
                            <legend>Tarjetas guardadas</legend>
                            <label for="tarjeta-guardada-id">Selecciona una tarjeta</label>
                            <select name="tarjeta_guardada_id" id="tarjeta-guardada-id">
                                <?php $__currentLoopData = $metodosPagoGuardados; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $metodoGuardado): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <option value="<?php echo e($metodoGuardado->ID); ?>" <?php if((string) old('tarjeta_guardada_id') === (string) $metodoGuardado->ID): echo 'selected'; endif; ?>>
                                        <?php if($metodoGuardado->Ultimos_4): ?>
                                            **** **** **** <?php echo e($metodoGuardado->Ultimos_4); ?>

                                        <?php else: ?>
                                            Tarjeta guardada #<?php echo e($metodoGuardado->ID); ?>

                                        <?php endif; ?>
                                    </option>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </select>
                            <?php $__errorArgs = ['tarjeta_guardada_id'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><small role="alert"><?php echo e($message); ?></small><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                        </fieldset>
                    <?php endif; ?>
                    <button type="submit" class="confirm">Confirmar todos los productos</button>
                </form>
            </section>
        <?php endif; ?>
    </main>
    <?php if(session('order_sent')): ?>
    <div class="toast" role="status" aria-live="polite">Pedido enviado a <?php echo e(session('order_sent')); ?> local(es). Cuando todos lo finalicen, aparecerá para el repartidor como una sola entrega. <a href="<?php echo e(route('cliente.pedidos')); ?>">Ver historial</a></div>
    <?php endif; ?>
    <script>
        const metodoPago = document.getElementById('metodo-pago');
        const tarjetaSimulada = document.getElementById('tarjeta-simulada');
        const tarjetasGuardadas = document.getElementById('tarjetas-guardadas');

        function actualizarFormularioTarjeta() {
            const usarTarjeta = metodoPago.value === 'tarjeta';
            tarjetaSimulada.hidden = !usarTarjeta;
            tarjetaSimulada.disabled = !usarTarjeta;
            if (tarjetasGuardadas) {
                const usarTarjetaGuardada = metodoPago.value === 'tarjeta_guardada';
                tarjetasGuardadas.hidden = !usarTarjetaGuardada;
                tarjetasGuardadas.disabled = !usarTarjetaGuardada;
            }
        }

        if (metodoPago) {
            metodoPago.addEventListener('change', actualizarFormularioTarjeta);
            actualizarFormularioTarjeta();
        }
    </script>
</body>
</html><?php /**PATH C:\xampp\htdocs\Proyecto_KRYMS_2026_UTU-main\resources\views/Cliente/CarritoNuevo.blade.php ENDPATH**/ ?>