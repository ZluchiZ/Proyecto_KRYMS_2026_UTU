<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="<?php echo e(asset('css/dashboard-local.css')); ?>">
    <link rel="stylesheet" href="<?php echo e(asset('css/internal.css')); ?>">
    <title>Productos del local</title>
</head>
<body class="internal-page internal-dashboard">
    <?php echo $__env->make('partials.internal-header', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
    <main>
        <section class="profile">
            <div class="profile-info">
                <strong><?php echo e($nombreUsuario); ?></strong>
                <span><?php echo e($tipoUsuario); ?></span>
                <small><?php echo e($correoUsuario); ?></small>
                <a class="dashboard-profile-link" href="<?php echo e(route('profile.edit')); ?>">Mi perfil</a>
                <a class="dashboard-profile-link" href="<?php echo e(route('local.sales-history')); ?>">Historial de ventas</a>
            </div>
            <div class="status-toggle">
                <span class="status-badge <?php echo e($comercioAbierto ? 'open' : 'closed'); ?>">
                    <?php echo e($comercioAbierto ? 'Abierto' : 'Cerrado'); ?>

                </span>
                <form method="POST" action="<?php echo e(route('comercio.status')); ?>">
                    <?php echo csrf_field(); ?>
                    <?php echo method_field('PATCH'); ?>
                    <input type="hidden" name="abierto" value="<?php echo e($comercioAbierto ? 0 : 1); ?>">
                    <button type="submit"><?php echo e($comercioAbierto ? 'Cerrar local' : 'Abrir local'); ?></button>
                </form>
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

        <?php if(session('success')): ?>
            <div class="success"><?php echo e(session('success')); ?></div>
        <?php endif; ?>

        <?php $__currentLoopData = [
            ['titulo' => 'Pedidos pendientes', 'items' => $pedidos->where('Estado_Subpedido', 'pendiente')],
            ['titulo' => 'Pedidos aceptados', 'items' => $pedidos->where('Estado_Subpedido', 'aceptado')],
        ]; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $seccion): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <section class="orders">
                <h2><?php echo e($seccion['titulo']); ?></h2>
                <?php if($seccion['items']->isEmpty()): ?>
                    <div class="empty">No hay pedidos en esta sección.</div>
                <?php else: ?>
                    <div class="order-list">
                    <?php $__currentLoopData = $seccion['items']->groupBy('N_Subpedido'); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $lineasPedido): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <?php ($pedido = $lineasPedido->first()); ?>
                        <article class="order" id="pedido-<?php echo e($pedido->N_Pedido); ?>">
                            <div class="order-title">
                                <span>Pedido #<?php echo e($pedido->ID_Pedido ?? $pedido->N_Pedido); ?></span>
                                <span class="order-status <?php echo e(($pedido->Estado_Subpedido ?? '') === 'rechazado' ? 'rejected' : ''); ?>"><?php echo e($pedido->Estado_Subpedido ?? 'pendiente'); ?></span>
                            </div>
                            <p><strong>Cliente:</strong> <?php echo e(trim(($pedido->Nombre_Cliente ?? '').' '.($pedido->Apellido_Cliente ?? '')) ?: ($pedido->Correo_Cliente ?? 'Cliente')); ?></p>
                            <?php $__currentLoopData = $lineasPedido; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $linea): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <p><strong>Producto:</strong> <?php echo e($linea->Nombre_Producto); ?> · <strong>Cantidad:</strong> <?php echo e($linea->Cantidad); ?></p>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            <p><strong>Envío:</strong> <?php echo e($pedido->Direccion_Envio ?? $pedido->Ubicacion); ?></p>
                            <p><strong>Total:</strong> $U <?php echo e(number_format($pedido->Total_Local ?? $pedido->Monto_Total, 2, ',', '.')); ?></p>
                            <p><strong>Pago:</strong> <?php echo e(ucfirst($pedido->Metodo_Pago ?? $pedido->Metodo_de_pago ?? 'No indicado')); ?></p>
                            <?php if($pedido->Telefono_Cliente): ?>
                                <p><strong>Teléfono:</strong> <?php echo e($pedido->Telefono_Cliente); ?></p>
                            <?php endif; ?>
                            <?php if(($pedido->Estado_Subpedido ?? 'pendiente') === 'pendiente'): ?>
                                <div class="order-actions">
                                    <form method="POST" action="<?php echo e(route('pedidos.status', $pedido->N_Subpedido)); ?>" onsubmit="return confirm('¿Rechazar y eliminar este pedido?');">
                                        <?php echo csrf_field(); ?>
                                        <?php echo method_field('PATCH'); ?>
                                        <input type="hidden" name="estado" value="aceptado">
                                        <button type="submit">Aceptar pedido</button>
                                    </form>
                                    <form method="POST" action="<?php echo e(route('pedidos.status', $pedido->N_Subpedido)); ?>">
                                        <?php echo csrf_field(); ?>
                                        <?php echo method_field('PATCH'); ?>
                                        <input type="hidden" name="estado" value="rechazado">
                                        <button type="submit" class="reject">Rechazar pedido</button>
                                    </form>
                                </div>
                            <?php elseif(($pedido->Estado_Subpedido ?? '') === 'aceptado'): ?>
                                <div class="order-actions">
                                    <form method="POST" action="<?php echo e(route('pedidos.status', $pedido->N_Subpedido)); ?>">
                                        <?php echo csrf_field(); ?>
                                        <?php echo method_field('PATCH'); ?>
                                        <input type="hidden" name="estado" value="listo">
                                        <button type="submit">Pedido listo</button>
                                    </form>
                                </div>
                            <?php endif; ?>
                        </article>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </div>
                <?php endif; ?>
            </section>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

        <section class="local-reviews">
            <header class="local-reviews-heading">
                <div>
                    <h2>Reseñas de clientes</h2>
                    <p><?php echo e($totalResenas); ?> <?php echo e($totalResenas === 1 ? 'reseña' : 'reseñas'); ?> visibles</p>
                </div>
                <?php if($totalResenas > 0): ?>
                    <strong class="local-reviews-average"><?php echo e(number_format((float) $promedioResenas, 1, ',', '.')); ?> / 5 <span aria-hidden="true">★</span></strong>
                <?php endif; ?>
            </header>
            <?php if($resenas->isEmpty()): ?>
                <div class="empty">Todavía no hay reseñas para este local.</div>
            <?php else: ?>
                <div class="local-review-list">
                    <?php $__currentLoopData = $resenas; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $resena): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <?php ($puntuacionResena = max(0, min(5, (int) $resena->Puntuacion))); ?>
                        <article class="local-review-item">
                            <header>
                                <strong><?php echo e($resena->Nombre_Cliente ?: 'Cliente'); ?></strong>
                                <span class="review-stars" aria-label="<?php echo e($puntuacionResena); ?> de 5 estrellas"><?php echo e(str_repeat('★', $puntuacionResena)); ?><?php echo e(str_repeat('☆', 5 - $puntuacionResena)); ?></span>
                            </header>
                            <p><?php echo e($resena->Comentario ?: 'El cliente no dejó un comentario.'); ?></p>
                        </article>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </div>
                <?php if($resenas->hasPages()): ?>
                    <nav class="review-pagination" aria-label="Paginación de reseñas">
                        <?php if($resenas->previousPageUrl()): ?><a href="<?php echo e($resenas->previousPageUrl()); ?>" rel="prev">&larr; Anteriores</a><?php else: ?><span></span><?php endif; ?>
                        <span>Página <?php echo e($resenas->currentPage()); ?> de <?php echo e($resenas->lastPage()); ?></span>
                        <?php if($resenas->nextPageUrl()): ?><a href="<?php echo e($resenas->nextPageUrl()); ?>" rel="next">Siguientes &rarr;</a><?php else: ?><span></span><?php endif; ?>
                    </nav>
                <?php endif; ?>
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
                            <div class="product-actions">
                                <button type="button" class="edit-product" data-edit-product="<?php echo e($producto->ID_Producto); ?>">Editar producto</button>
                                <form method="POST" action="<?php echo e(route('productos.destroy', $producto->ID_Producto)); ?>" onsubmit="return confirm('¿Eliminar este producto?');">
                                    <?php echo csrf_field(); ?>
                                    <?php echo method_field('DELETE'); ?>
                                    <button type="submit">Eliminar producto</button>
                                </form>
                            </div>

                            <div class="product-edit-panel" data-product-panel="<?php echo e($producto->ID_Producto); ?>" hidden>
                                <form class="product-edit-form" data-product-form="<?php echo e($producto->ID_Producto); ?>" method="POST" action="<?php echo e(route('productos.update', $producto->ID_Producto)); ?>">
                                    <?php echo csrf_field(); ?>
                                    <?php echo method_field('PATCH'); ?>
                                    <label for="precio-<?php echo e($producto->ID_Producto); ?>">Precio en $UYU</label>
                                    <input id="precio-<?php echo e($producto->ID_Producto); ?>" name="precio" type="number" value="<?php echo e($producto->Precio); ?>" min="0" max="99999999.99" step="0.01" required>

                                    <label for="descuento-<?php echo e($producto->ID_Producto); ?>">Porcentaje de descuento</label>
                                    <input id="descuento-<?php echo e($producto->ID_Producto); ?>" name="descuento" type="number" value="<?php echo e((float) ($producto->Descuento_Porcentaje ?? 0)); ?>" min="0" max="100" step="0.01" required>

                                    <label class="checkbox">
                                        <input name="disponible" type="hidden" value="0">
                                        <input name="disponible" type="checkbox" value="1" <?php echo e($producto->Disponible ? 'checked' : ''); ?>>
                                        Disponible para pedidos
                                    </label>
                                    <button type="submit">Guardar cambios</button>
                                </form>
                            </div>
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
            <form method="POST" action="<?php echo e(route('productos.store')); ?>" enctype="multipart/form-data">
                <?php echo csrf_field(); ?>
                <label for="nombre">Nombre</label>
                <input id="nombre" name="nombre" type="text" value="<?php echo e(old('nombre')); ?>" required maxlength="255">

                <label for="precio">Precio en $UYU</label>
                <input id="precio" name="precio" type="number" value="<?php echo e(old('precio')); ?>" min="0" max="99999999.99" step="0.01" required>

                <label for="categoria">Categoría</label>
                <select id="categoria" name="categoria" required>
                    <option value="">Seleccionar categoría</option>
                    <?php $__currentLoopData = ['Restaurantes', 'Supermercados', 'Farmacia', 'Kioscos', 'Bebidas', 'Mascotas', 'Entrega rápida', 'Otros', 'Supermercado', 'Ferretería', 'Rotisería']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $categoria): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <option value="<?php echo e($categoria); ?>" <?php if(old('categoria') === $categoria): echo 'selected'; endif; ?>><?php echo e($categoria); ?></option>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </select>

                <label for="descripcion">Descripción</label>
                <textarea id="descripcion" name="descripcion" required maxlength="1000"><?php echo e(old('descripcion')); ?></textarea>

                <label for="imagen">Imagen del producto</label>
                <input id="imagen" name="imagen" type="file" accept="image/*" required>

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

        document.querySelectorAll('[data-edit-product]').forEach((button) => {
            button.addEventListener('click', () => {
                const panel = document.querySelector(`[data-product-panel="${button.dataset.editProduct}"]`);

                document.querySelectorAll('.product-edit-panel').forEach((item) => {
                    if (item !== panel) {
                        item.hidden = true;
                        item.classList.remove('visible');
                    }
                });

                if (!panel) return;

                const shouldShow = panel.hidden;
                panel.hidden = !shouldShow;
                panel.classList.toggle('visible', shouldShow);
                button.textContent = shouldShow ? 'Cancelar edición' : 'Editar producto';
            });
        });

        <?php if($errors->any()): ?>
            modal.showModal();
        <?php endif; ?>
    </script>
</body>
</html><?php /**PATH /home/gsivoplas/htdocs/Proyecto_KRYMS_2026_UTU-main/resources/views/Local/DashboardLocal.blade.php ENDPATH**/ ?>