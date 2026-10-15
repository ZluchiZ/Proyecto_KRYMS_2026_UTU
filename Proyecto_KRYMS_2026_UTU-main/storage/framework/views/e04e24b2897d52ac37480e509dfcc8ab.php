<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Perfil de <?php echo e($comercio->Nombre_Comercio ?: 'local'); ?> en KRYMS.">
    <link rel="stylesheet" href="<?php echo e(asset('css/styles.css')); ?>">
    <link rel="stylesheet" href="<?php echo e(asset('css/home.css')); ?>">
    <link rel="stylesheet" href="<?php echo e(asset('css/local-profile.css')); ?>">
    <title><?php echo e($comercio->Nombre_Comercio ?: 'Local'); ?> | KRYMS</title>
</head>
<body class="home-page local-profile-page">
    <?php if(session('success')): ?>
        <p class="alert success-message toast-notification" role="status"><?php echo e(session('success')); ?></p>
    <?php endif; ?>

    <header class="site-header">
        <div class="header-inner">
            <a class="brand" href="<?php echo e(route('home')); ?>" aria-label="Volver al inicio"><img src="<?php echo e(asset('img/LogoGaucho_png.png')); ?>" alt="El Gaucho Va" class="logo"></a>
            <a class="profile-back-link" href="<?php echo e(route('home')); ?>">← Volver al inicio</a>
            <nav class="header-actions" aria-label="Acciones de cuenta">
                <?php if($esClienteRegistrado): ?><a class="cart-link" href="<?php echo e(route('carrito')); ?>" aria-label="Ver carrito"><span>🛒</span><b>Carrito</b></a><?php endif; ?>
                <div class="profile-container"><button type="button" class="profile-button" aria-label="Abrir perfil" data-profile-toggle><span>◉</span><b>Cuenta</b></button><div class="profile-menu" data-profile-menu><a href="<?php echo e(route('login')); ?>"><?php echo e($esClienteRegistrado ? 'Mi cuenta' : 'Iniciar sesión'); ?></a></div></div>
            </nav>
        </div>
    </header>

    <main class="local-profile-main">
        <section class="local-profile-hero">
            <div class="local-profile-logo-wrap">
                <img src="<?php echo e($comercio->Logo ?: 'https://images.unsplash.com/photo-1517248135467-4c7edcad34c4?auto=format&fit=crop&w=500&q=85'); ?>" alt="Logo de <?php echo e($comercio->Nombre_Comercio ?: 'local'); ?>" class="local-profile-logo">
            </div>
            <div class="local-profile-heading">
                <span class="profile-eyebrow">Perfil del local</span>
                <h1><?php echo e($comercio->Nombre_Comercio ?: 'Local sin nombre'); ?></h1>
                <span class="profile-status <?php echo e($comercio->Abierto ? 'is-open' : 'is-closed'); ?>">● <?php echo e($comercio->Abierto ? 'Abierto' : 'Cerrado'); ?></span>
            </div>
        </section>

        <section class="local-profile-info" aria-label="Información del local">
            <div><span>Horario</span><strong><?php echo e($comercio->Horario ?: 'Horario no informado'); ?></strong></div>
            <div><span>Dirección</span><strong><?php echo e($comercio->{'Dirección'} ?: 'Dirección no informada'); ?></strong></div>
            <div><span>Productos</span><strong><?php echo e($productos->count()); ?> publicados</strong></div>
        </section>

        <section class="local-products-section" aria-labelledby="products-title">
            <div class="section-heading"><div><p class="eyebrow">Del local</p><h2 id="products-title">Productos</h2></div></div>
            <?php if($productos->isNotEmpty()): ?>
                <div class="product-grid">
                    <?php $__currentLoopData = $productos; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $producto): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <?php echo $__env->make('home-product-card', ['producto' => $producto], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </div>
            <?php else: ?>
                <p class="empty-state">Este local todavía no tiene productos publicados.</p>
            <?php endif; ?>
        </section>
    </main>

    <?php if($esClienteRegistrado): ?>
        <dialog id="purchase-modal" class="purchase-modal"><div class="purchase-content"><div class="purchase-header"><h2>Agregar al carrito</h2><button type="button" class="purchase-close" id="close-purchase-modal" aria-label="Cerrar">×</button></div><div class="purchase-details"><strong id="purchase-product-name"></strong><p id="purchase-product-local"></p><p id="purchase-product-price"></p></div><form method="POST" action="<?php echo e(route('carrito.add')); ?>" class="purchase-form"><?php echo csrf_field(); ?><input type="hidden" name="producto_id" id="purchase-product-id"><label for="purchase-quantity">Cantidad</label><input type="number" name="cantidad" id="purchase-quantity" min="1" value="1" required><button type="submit">Agregar al carrito</button></form></div></dialog>
    <?php endif; ?>

    <script>
        const profileToggle = document.querySelector('[data-profile-toggle]');
        const profileMenu = document.querySelector('[data-profile-menu]');
        profileToggle?.addEventListener('click', () => profileMenu.classList.toggle('is-open'));
        document.addEventListener('click', (event) => { if (!event.target.closest('.profile-container')) profileMenu?.classList.remove('is-open'); });
        const purchaseModal = document.getElementById('purchase-modal');
        document.querySelectorAll('.purchase-trigger[data-product-id]').forEach((trigger) => trigger.addEventListener('click', () => { document.getElementById('purchase-product-id').value = trigger.dataset.productId; document.getElementById('purchase-product-name').textContent = trigger.dataset.productName; document.getElementById('purchase-product-local').textContent = `Local: ${trigger.dataset.productLocal}`; document.getElementById('purchase-product-price').textContent = trigger.dataset.productPrice; document.getElementById('purchase-quantity').value = 1; purchaseModal.showModal(); }));
        document.getElementById('close-purchase-modal')?.addEventListener('click', () => purchaseModal.close());
    </script>
</body>
</html>
<?php /**PATH /opt/lampp/htdocs/Proyecto_KRYMS_2026_UTU/resources/views/local-profile.blade.php ENDPATH**/ ?>