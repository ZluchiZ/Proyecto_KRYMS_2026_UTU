<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="<?php echo e(asset('css/dashboard-local.css')); ?>">
    <title>Productos del local</title>
</head>
<body>
    <main>
        <section class="profile">
            <div class="profile-info">
                <strong><?php echo e($nombreUsuario); ?></strong>
                <span><?php echo e($tipoUsuario); ?></span>
                <small><?php echo e($correoUsuario); ?></small>
            </div>
            <form method="POST" action="<?php echo e(route('logout')); ?>">
                <?php echo csrf_field(); ?>
                <button type="submit">Cerrar sesión</button>
            </form>
        </section>

        <header class="dashboard-header">
            <div>
                <h1>Productos del local</h1>
                <p>Administra los productos disponibles para pedidos.</p>
            </div>
            <button type="button" id="open-product-modal">Añadir producto</button>
        </header>

        <?php if($errors->any()): ?>
            <div class="error">
                <?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $error): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <div><?php echo e($error); ?></div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </div>
        <?php endif; ?>

        <section class="orders">
            <h2>Pedidos recibidos</h2>
            <?php if($pedidos->isEmpty()): ?>
                <div class="empty">Todavía no hay pedidos para tus productos.</div>
            <?php else: ?>
                <div class="order-list">
                    <?php $__currentLoopData = $pedidos; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $pedido): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <article class="order">
                            <div class="order-title">
                                <span>Pedido #<?php echo e($pedido->ID_Pedido ?? $pedido->N_Pedido); ?></span>
                                <span class="order-status <?php echo e(($pedido->Estado ?? '') === 'rechazado' ? 'rejected' : ''); ?>"><?php echo e($pedido->Estado ?? 'pendiente'); ?></span>
                            </div>
                            <p><strong>Producto:</strong> <?php echo e($pedido->Nombre_Producto); ?></p>
                            <p><strong>Cliente:</strong> <?php echo e(trim(($pedido->Nombre_Cliente ?? '').' '.($pedido->Apellido_Cliente ?? '')) ?: ($pedido->Correo_Cliente ?? 'Cliente')); ?></p>
                            <p><strong>Cantidad:</strong> <?php echo e($pedido->Cantidad); ?></p>
                            <p><strong>Envío:</strong> <?php echo e($pedido->Direccion_Envio ?? $pedido->Ubicacion); ?></p>
                            <p><strong>Total:</strong> $U <?php echo e(number_format($pedido->Total ?? $pedido->Monto_Total, 2, ',', '.')); ?></p>
                            <p><strong>Pago:</strong> <?php echo e(ucfirst($pedido->Metodo_Pago ?? $pedido->Metodo_de_pago ?? 'No indicado')); ?></p>
                            <?php if($pedido->Telefono_Cliente): ?>
                                <p><strong>Teléfono:</strong> <?php echo e($pedido->Telefono_Cliente); ?></p>
                            <?php endif; ?>
                            <?php if(($pedido->Estado ?? 'pendiente') === 'pendiente'): ?>
                                <div class="order-actions">
                                    <form method="POST" action="<?php echo e(route('pedidos.status', $pedido->ID_Pedido ?? $pedido->N_Pedido)); ?>" onsubmit="return confirm('¿Rechazar y eliminar este pedido?');">
                                        <?php echo csrf_field(); ?>
                                        <?php echo method_field('PATCH'); ?>
                                        <input type="hidden" name="estado" value="aceptado">
                                        <button type="submit">Aceptar pedido</button>
                                    </form>
                                    <form method="POST" action="<?php echo e(route('pedidos.status', $pedido->ID_Pedido ?? $pedido->N_Pedido)); ?>">
                                        <?php echo csrf_field(); ?>
                                        <?php echo method_field('PATCH'); ?>
                                        <input type="hidden" name="estado" value="rechazado">
                                        <button type="submit" class="reject">Rechazar pedido</button>
                                    </form>
                                </div>
                            <?php endif; ?>
                        </article>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </div>
            <?php endif; ?>
        </section>

        <?php if($productos->isEmpty()): ?>
            <div class="empty">Todavía no hay productos cargados.</div>
        <?php else: ?>
            <section class="products">
                <?php $__currentLoopData = $productos; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $producto): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <article class="product">
                        <img src="<?php echo e($producto->Foto_Producto); ?>" alt="<?php echo e($producto->Nombre_Producto); ?>">
                        <div class="product-content">
                            <h2><?php echo e($producto->Nombre_Producto); ?></h2>
                            <p><?php echo e($producto->Categoria); ?></p>
                            <p class="price">$U <?php echo e(number_format($producto->Precio, 2, ',', '.')); ?></p>
                            <p class="status <?php echo e($producto->Disponible ? '' : 'unavailable'); ?>">
                                <?php echo e($producto->Disponible ? 'Disponible para pedidos' : 'No disponible para pedidos'); ?>

                            </p>
                            <form method="POST" action="<?php echo e(route('productos.destroy', $producto->ID_Producto)); ?>" onsubmit="return confirm('¿Eliminar este producto?');">
                                <?php echo csrf_field(); ?>
                                <?php echo method_field('DELETE'); ?>
                                <button type="submit">Eliminar producto</button>
                            </form>
                        </div>
                    </article>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </section>
        <?php endif; ?>
    </main>

    <dialog id="product-modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2>Añadir producto</h2>
                <button type="button" class="close" id="close-product-modal" aria-label="Cerrar">&times;</button>
            </div>
            <form method="POST" action="<?php echo e(route('productos.store')); ?>">
                <?php echo csrf_field(); ?>
                <label for="nombre">Nombre</label>
                <input id="nombre" name="nombre" type="text" value="<?php echo e(old('nombre')); ?>" required maxlength="255">

                <label for="precio">Precio en $UYU</label>
                <input id="precio" name="precio" type="number" value="<?php echo e(old('precio')); ?>" min="0" step="0.01" required>

                <label for="categoria">Categoría</label>
                <input id="categoria" name="categoria" type="text" value="<?php echo e(old('categoria')); ?>" required maxlength="100">

                <label for="descripcion">Descripción</label>
                <textarea id="descripcion" name="descripcion" required maxlength="1000"><?php echo e(old('descripcion')); ?></textarea>

                <label for="imagen_url">Imagen URL</label>
                <input id="imagen_url" name="imagen_url" type="url" value="<?php echo e(old('imagen_url')); ?>" required maxlength="2048">

                <label for="stock">Cantidad disponible</label>
                <div class="quantity-control">
                    <button type="button" class="quantity-button" data-quantity-decrease aria-label="Disminuir cantidad">-</button>
                    <input id="stock" name="stock" type="number" value="<?php echo e(old('stock', 1)); ?>" min="0" max="999999" step="1" required>
                    <button type="button" class="quantity-button" data-quantity-increase aria-label="Aumentar cantidad">+</button>
                </div>

                <label class="checkbox">
                    <input name="disponible" type="checkbox" value="1" <?php echo e(old('disponible', '1') ? 'checked' : ''); ?>>
                    Disponible para pedidos
                </label>
                <button type="submit">Guardar producto</button>
            </form>
        </div>
    </dialog>

    <script>
        const modal = document.getElementById('product-modal');
        document.getElementById('open-product-modal').addEventListener('click', () => modal.showModal());
        document.getElementById('close-product-modal').addEventListener('click', () => modal.close());
        modal.addEventListener('click', (event) => {
            if (event.target === modal) modal.close();
        });

        const stockInput = document.getElementById('stock');
        document.querySelector('[data-quantity-decrease]').addEventListener('click', () => {
            stockInput.value = Math.max(0, Number(stockInput.value || 0) - 1);
        });
        document.querySelector('[data-quantity-increase]').addEventListener('click', () => {
            stockInput.value = Math.min(999999, Number(stockInput.value || 0) + 1);
        });

        <?php if($errors->any()): ?>
            modal.showModal();
        <?php endif; ?>
    </script>
</body>
</html><?php /**PATH /opt/lampp/htdocs/Proyecto_KRYMS_2026_UTU/resources/views/Local/DashboardLocal.blade.php ENDPATH**/ ?>