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
    <p class="alert success-message toast-notification" role="status"><?php echo e(session('success')); ?></p>
<?php endif; ?>
    <input type="checkbox" id="sidebar-toggle">
    <header class="site-header">
        <img src="<?php echo e(asset('img/Logo_Gaucho_Va.png')); ?>" alt="Logo de ElGauchoVa" class="logo">
        <!-- Sección: botón de menú hamburguesa para abrir/cerrar el sidebar -->
        <label for="sidebar-toggle" class="menu-btn" aria-label="Abrir o cerrar menú">
            <span></span>
            <span></span>
            <span></span>
        </label>
        <!-- Barra de acciones del encabezado -->
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
    </header>

    <label for="sidebar-toggle" class="menu-backdrop" aria-label="Cerrar menú"></label>

    <!-- Sección: menú lateral con enlaces a categorías -->
    <div class="sidebar">
        <button type="button" class="sidebar-close" aria-label="Cerrar menú">&times;</button>
        <a href="#">Farmacia</a>
        <a href="#">Supermercado</a>
        <a href="#">Ferretería</a>
        <a href="#">Rotisería</a>
    </div>

    <!-- Sección: contenido principal de la página con bienvenida y búsqueda -->
    <div class="contenido-pagina">

        <div class="home-intro">
            <p class="home-eyebrow">Comprá cerca, recibí rápido</p>
            <h1>¿Qué te gustaría pedir hoy?</h1>
        </div>

        <!-- Formulario de búsqueda para consultar productos o categorías -->
        <form method="GET" action="<?php echo e(route('home')); ?>">
            <label for="campo-busqueda"></label>
            <input type="search" id="campo-busqueda" name="q" value="<?php echo e(request('q')); ?>" placeholder="¿Qué estás buscando?">
            <button type="submit">Buscar</button>
        </form>

        <nav class="category-strip" aria-label="Categorías de productos">
            <a href="#productos">Todos</a>
            <a href="#productos">Farmacia</a>
            <a href="#productos">Supermercado</a>
            <a href="#productos">Ferretería</a>
            <a href="#productos">Rotisería</a>
        </nav>

        <!-- Sección: productos disponibles de los locales -->
        <?php if(request()->filled('q')): ?>
            <div class="search-results" id="productos">
                <div class="search-results-heading">
                    <h2>Resultados para “<?php echo e(request('q')); ?>”</h2>
                    <span><?php echo e($productos->count()); ?> producto(s)</span>
                </div>
                <?php if($productos->isNotEmpty()): ?>
                    <div class="search-product-grid">
                        <?php $__currentLoopData = $productos; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $producto): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <article class="search-product-card">
                                <img src="<?php echo e($producto->Foto_Producto); ?>" alt="<?php echo e($producto->Nombre_Producto); ?>">
                                <div class="search-product-content">
                                    <h3><?php echo e($producto->Nombre_Producto); ?></h3>
                                    <p class="product-category"><?php echo e($producto->Categoria); ?></p>
                                    <div class="product-meta">
                                        <strong class="product-price">$U <?php echo e(number_format($producto->Precio, 2, ',', '.')); ?></strong>
                                        <p class="product-store"><span>Local</span> <?php echo e($producto->Nombre_Comercio ?? 'Local no disponible'); ?></p>
                                    </div>
                                    <?php if($producto->Disponible && $esClienteRegistrado): ?>
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
                                    <?php elseif(! $producto->Disponible): ?>
                                        <span class="search-unavailable">No disponible</span>
                                    <?php else: ?>
                                        <a class="purchase-trigger" href="<?php echo e(route('login')); ?>">Iniciar sesión</a>
                                    <?php endif; ?>
                                </div>
                            </article>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </div>
                <?php else: ?>
                    <p class="empty-search">No encontramos productos para esta búsqueda.</p>
                <?php endif; ?>
            </div>
        <?php else: ?>
        <div class="product-carousel" data-carousel>
            <button type="button" class="carousel-arrow carousel-arrow-prev" data-carousel-prev aria-label="Productos anteriores">&#8249;</button>
            <div class="comidas-container" id="productos">
            <?php $__empty_1 = true; $__currentLoopData = $productos; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $producto): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                <article class="comida-card">
                    <img src="<?php echo e($producto->Foto_Producto); ?>" alt="<?php echo e($producto->Nombre_Producto); ?>">
                    <div class="product-card-content">
                        <h3><?php echo e($producto->Nombre_Producto); ?></h3>
                        <p class="product-category"><?php echo e($producto->Categoria); ?></p>
                        <?php if($producto->Disponible): ?>
                            <div class="product-meta">
                                <strong class="product-price">$U <?php echo e(number_format($producto->Precio, 2, ',', '.')); ?></strong>
                                <p class="product-store"><span>Local</span> <?php echo e($producto->Nombre_Comercio ?? 'Local no disponible'); ?></p>
                            </div>
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
                        <?php else: ?>
                            <div class="product-meta">
                                <p class="product-price"><strong>Precio:</strong> $U <?php echo e(number_format($producto->Precio, 2, ',', '.')); ?></p>
                                <p class="product-store"><span>Local</span> <?php echo e($producto->Nombre_Comercio ?? 'Local no disponible'); ?></p>
                            </div>
                            <div class="unavailable-product">
                                <strong>No disponible</strong>
                                <span>Vuelve a intentarlo más tarde.</span>
                            </div>
                        <?php endif; ?>
                    </div>
                </article>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                <p>No hay productos disponibles en este momento.</p>
            <?php endif; ?>
            </div>
            <button type="button" class="carousel-arrow carousel-arrow-next" data-carousel-next aria-label="Más productos">&#8250;</button>
        </div>
        <?php endif; ?>

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

<script>
    const toastNotification = document.querySelector('.toast-notification');

    if (toastNotification) {
        window.setTimeout(() => {
            toastNotification.classList.add('is-hidden');
        }, 1000);
    }

    const sidebarToggle = document.getElementById('sidebar-toggle');
    const sidebarClose = document.querySelector('.sidebar-close');

    sidebarClose?.addEventListener('click', () => {
        sidebarToggle.checked = false;
    });

    document.querySelectorAll('[data-carousel]').forEach((carousel) => {
        const track = carousel.querySelector('.comidas-container');
        const amount = () => Math.max(track.clientWidth * 0.8, 280);

        carousel.querySelector('[data-carousel-prev]').addEventListener('click', () => {
            track.scrollTo({
                left: Math.max(track.scrollLeft - amount(), 0),
                behavior: 'smooth',
            });
        });

        carousel.querySelector('[data-carousel-next]').addEventListener('click', () => {
            track.scrollTo({
                left: Math.min(track.scrollLeft + amount(), track.scrollWidth - track.clientWidth),
                behavior: 'smooth',
            });
        });
    });
</script>

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
</html><?php /**PATH /opt/lampp/htdocs/Proyecto_KRYMS_2026_UTU/resources/views/home.blade.php ENDPATH**/ ?>