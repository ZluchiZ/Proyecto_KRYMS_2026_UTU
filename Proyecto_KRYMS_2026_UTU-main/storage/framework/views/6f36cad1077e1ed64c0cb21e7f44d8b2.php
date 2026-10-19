<article class="product-card">
    <div class="product-image-wrap">
        <img src="<?php echo e($producto->Foto_Producto ?: 'https://images.unsplash.com/photo-1546069901-ba9599a7e63c?auto=format&fit=crop&w=700&q=80'); ?>" alt="<?php echo e($producto->Nombre_Producto); ?>">
        <?php if((float) ($producto->Descuento_Porcentaje ?? 0) > 0): ?><span class="discount-badge"><?php echo e(number_format($producto->Descuento_Porcentaje, 0)); ?>% OFF</span><?php endif; ?>
    </div>
    <div class="product-card-body">
        <div>
            <span class="product-category"><?php echo e($producto->Categoria ?: 'Producto'); ?></span>
            <h3><?php echo e($producto->Nombre_Producto); ?></h3>
            <p class="product-store"><?php echo e($producto->Nombre_Comercio ?: 'Local adherido'); ?></p>
        </div>
        <div class="product-footer">
            <?php
                $descuentoProducto = (float) ($producto->Descuento_Porcentaje ?? 0);
                $precioFinalProducto = $descuentoProducto > 0 ? $producto->Precio * (1 - ($descuentoProducto / 100)) : $producto->Precio;
            ?>

            <?php if($descuentoProducto > 0): ?>
                <span class="product-price-discounted">$U <?php echo e(number_format($precioFinalProducto, 2, ',', '.')); ?></span>
                <span class="product-price-original">$U <?php echo e(number_format($producto->Precio, 2, ',', '.')); ?></span>
            <?php else: ?>
                <strong>$U <?php echo e(number_format($producto->Precio, 2, ',', '.')); ?></strong>
            <?php endif; ?>

            <?php if(!$producto->Abierto): ?>
                <span class="unavailable-label closed-status">Cerrado</span>
            <?php elseif($producto->Disponible && $esClienteRegistrado): ?>
                <button type="button" class="purchase-trigger add-button" data-product-id="<?php echo e($producto->ID_Producto); ?>" data-product-name="<?php echo e($producto->Nombre_Producto); ?>" data-product-price="$U <?php echo e(number_format($precioFinalProducto, 2, ',', '.')); ?>" data-product-local="<?php echo e($producto->Nombre_Comercio ?: 'Local adherido'); ?>" aria-label="Agregar <?php echo e($producto->Nombre_Producto); ?>">+</button>
            <?php elseif($producto->Disponible): ?>
                <a class="add-button" href="<?php echo e(route('login')); ?>" aria-label="Iniciar sesión para comprar">+</a>
            <?php else: ?>
                <span class="unavailable-label">No disponible</span>
            <?php endif; ?>
        </div>
    </div>
</article>
<?php /**PATH /opt/lampp/htdocs/Proyecto_KRYMS_2026_UTU/resources/views/home-product-card.blade.php ENDPATH**/ ?>