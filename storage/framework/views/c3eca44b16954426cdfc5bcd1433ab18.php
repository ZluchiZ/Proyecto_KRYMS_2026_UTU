<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="<?php echo e(asset('css/styles.css')); ?>">
    <link rel="stylesheet" href="<?php echo e(asset('css/home.css')); ?>">
    <title>ElGauchoVa</title>
</head>

<body>

<main class="container">

<?php if(session('success')): ?>
    <p class="alert success-message"><?php echo e(session('success')); ?></p>
<?php endif; ?>

    <!-- Sección: botón de menú hamburguesa para abrir/cerrar el sidebar -->
    <label for="sidebar-toggle" class="menu-btn">☰ Menú</label>
    <input type="checkbox" id="sidebar-toggle">

    <!-- Sección: barra superior con íconos de ubicación y perfil -->
    <div class="top-right-icons">
        <div class="icon-group">
            <span class="location-icon">📍</span>

            <?php if($esClienteRegistrado): ?>
                <a class="cart-link" href="<?php echo e(route('carrito')); ?>" aria-label="Ver carrito">🛒 <?php echo e($cantidadCarrito); ?></a>
            <?php endif; ?>

            <div class="profile-container">
                <input type="checkbox" id="toggleProfile">
                <label for="toggleProfile" class="profile-icon">👤</label>

                <div class="profile-menu">
                    <?php if($nombreUsuario): ?>
                        <strong><?php echo e($nombreUsuario); ?></strong>
                        <span><?php echo e($tipoUsuario); ?></span>
                        <small><?php echo e($correoUsuario); ?></small>
                        <form method="POST" action="<?php echo e(route('logout')); ?>">
                            <?php echo csrf_field(); ?>
                            <button type="submit">Cerrar sesión</button>
                        </form>
                    <?php else: ?>
                        <a href="<?php echo e(route('login')); ?>">Iniciar Sesión / Registrarse</a>
                    <?php endif; ?>
                </div>
            </div>

        </div>
    </div>

    <!-- Sección: menú lateral con enlaces a categorías -->
    <div class="sidebar">
        <a href="#">Farmacia</a>
        <a href="#">Supermercado</a>
        <a href="#">Ferretería</a>
        <a href="#">Rotisería</a>
    </div>

    <!-- Sección: contenido principal de la página con bienvenida y búsqueda -->
    <div class="contenido-pagina">

        <h1>¡Bienvenidos a ElGauchoVa!</h1>

        <!-- Formulario de búsqueda para consultar productos o categorías -->
        <form method="GET">
            <label for="campo-busqueda"></label>
            <input type="search" id="campo-busqueda" name="q" placeholder="¿Qué estás buscando?">
            <button type="submit">Buscar</button>
        </form>

        <!-- Sección: productos disponibles de los locales -->
        <div class="comidas-container">
            <?php $__empty_1 = true; $__currentLoopData = $productos; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $producto): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                <article class="comida-card">
                    <img src="<?php echo e($producto->Foto_Producto); ?>" alt="<?php echo e($producto->Nombre_Producto); ?>">
                    <h3><?php echo e($producto->Nombre_Producto); ?></h3>
                    <p><?php echo e($producto->Categoria); ?></p>
                    <strong>$U <?php echo e(number_format($producto->Precio, 2, ',', '.')); ?></strong>
                    <p>Local: <?php echo e($producto->Nombre_Comercio ?? 'Local no disponible'); ?></p>
                    <?php if($esClienteRegistrado): ?>
                        <button
                            type="button"
                            class="purchase-trigger"
                            data-product-id="<?php echo e($producto->ID_Producto); ?>"
                            data-product-name="<?php echo e($producto->Nombre_Producto); ?>"
                            data-product-price="$U <?php echo e(number_format($producto->Precio, 2, ',', '.')); ?>"
                            data-product-local="<?php echo e($producto->Nombre_Comercio ?? 'Local no disponible'); ?>"
                            data-product-stock="1"
                        >
                            Comprar
                        </button>
                    <?php else: ?>
                        <a class="purchase-trigger" href="<?php echo e(route('login')); ?>">Inicia sesión para comprar</a>
                    <?php endif; ?>
                </article>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                <p>No hay productos disponibles en este momento.</p>
            <?php endif; ?>
        </div>

    </div>

</main>

<?php if($esClienteRegistrado): ?>
    <dialog id="purchase-modal" class="purchase-modal">
        <div class="purchase-content">
            <div class="purchase-header">
                <h2>Comprar producto</h2>
                <button type="button" class="purchase-close" id="close-purchase-modal" aria-label="Cerrar">&times;</button>
            </div>

            <div class="purchase-details">
                <strong id="purchase-product-name"></strong>
                <p id="purchase-product-local"></p>
                <p id="purchase-product-price"></p>
            </div>

            <form method="POST" action="<?php echo e(route('carrito.add')); ?>" class="purchase-form">
                <?php echo csrf_field(); ?>
                <input type="hidden" name="producto_id" id="purchase-product-id">

                <label for="purchase-quantity">Cantidad</label>
                <input type="number" name="cantidad" id="purchase-quantity" min="1" required>

                <button type="submit">Agregar al carrito</button>
            </form>
        </div>
    </dialog>
<?php endif; ?>

<!-- Sección: pie de página con información de la empresa -->
<footer>
    <div class="nosotros">
        <p><strong>Sobre nosotros</strong></p>
        <p>
            Lorem ipsum dolor sit amet, consectetur adipiscing elit...
        </p>
    </div>
</footer>

<?php if($esClienteRegistrado): ?>
    <script>
        const purchaseModal = document.getElementById('purchase-modal');
        const productIdInput = document.getElementById('purchase-product-id');
        const quantityInput = document.getElementById('purchase-quantity');

        document.querySelectorAll('.purchase-trigger[data-product-id]').forEach((trigger) => {
            trigger.addEventListener('click', () => {
                productIdInput.value = trigger.dataset.productId;
                document.getElementById('purchase-product-name').textContent = trigger.dataset.productName;
                document.getElementById('purchase-product-local').textContent = `Local: ${trigger.dataset.productLocal}`;
                document.getElementById('purchase-product-price').textContent = trigger.dataset.productPrice;
                quantityInput.removeAttribute('max');
                quantityInput.value = 1;
                purchaseModal.showModal();
            });
        });

        document.getElementById('close-purchase-modal').addEventListener('click', () => purchaseModal.close());
        purchaseModal.addEventListener('click', (event) => {
            if (event.target === purchaseModal) purchaseModal.close();
        });
    </script>
<?php endif; ?>

</body>
</html><?php /**PATH C:\xampp\htdocs\Proyecto_KRYMS_2026_UTU-main\resources\views/home.blade.php ENDPATH**/ ?>