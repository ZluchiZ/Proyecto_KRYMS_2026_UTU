<?php
    $categorias = [
        ['nombre' => 'Restaurantes', 'icono' => '🍔', 'filtro' => 'Restaurantes'],
        ['nombre' => 'Supermercados', 'icono' => '🛒', 'filtro' => 'Supermercados'],
        ['nombre' => 'Farmacia', 'icono' => '💊', 'filtro' => 'Farmacia'],
        ['nombre' => 'Kioscos', 'icono' => '🏪', 'filtro' => 'Kioscos'],
        ['nombre' => 'Bebidas', 'icono' => '🥤', 'filtro' => 'Bebidas'],
        ['nombre' => 'Mascotas', 'icono' => '🐶', 'filtro' => 'Mascotas'],
        ['nombre' => 'Otros', 'icono' => '✦', 'filtro' => 'Otros'],
        ['nombre' => 'Supermercado', 'icono' => '🛍️', 'filtro' => 'Supermercado'],
        ['nombre' => 'Ferretería', 'icono' => '🛠️', 'filtro' => 'Ferretería'],
        ['nombre' => 'Rotisería', 'icono' => '🍽️', 'filtro' => 'Rotisería'],
    ];
    $productosVisibles = $productos->where('Disponible', true);
    $ofertas = $productosVisibles->filter(fn ($producto) => (float) ($producto->Descuento_Porcentaje ?? 0) > 0)->take(4);
    $productosSinDescuento = $productosVisibles->filter(fn ($producto) => (float) ($producto->Descuento_Porcentaje ?? 0) <= 0);
    $populares = $productosSinDescuento->sortByDesc('ID_Producto')->take(6);
    $restaurantes = $productosVisibles->groupBy('RUT_Comercio')->take(8);
    $categoriasConProductos = $productosVisibles->pluck('Categoria')->filter(fn ($categoria) => trim((string) $categoria) !== '')->map(fn ($categoria) => trim((string) $categoria))->unique()->values();
    $productosPorCategoria = $categoriasConProductos->mapWithKeys(function ($categoria) use ($productosSinDescuento) {
        $productosCategoria = $productosSinDescuento->filter(fn ($producto) => strcasecmp(trim((string) $producto->Categoria), $categoria) === 0)->take(12);

        return [$categoria => $productosCategoria];
    });
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="KRYMS: pedí lo que necesitás y recibilo donde estés.">
    <link rel="stylesheet" href="<?php echo e(asset('css/styles.css')); ?>">
    <link rel="stylesheet" href="<?php echo e(asset('css/home.css')); ?>">
    <?php if($esClienteRegistrado): ?><link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"><?php endif; ?>
    <title>KRYMS | Pedí cerca, recibí fácil</title>
</head>
<body class="home-page">
    <?php if(session('success')): ?>
        <p class="alert success-message toast-notification" role="status"><?php echo e(session('success')); ?></p>
    <?php endif; ?>
    <header class="site-header">
        <div class="header-inner">
            <a class="brand" href="<?php echo e(route('home')); ?>" aria-label="El Gaucho Va inicio"><img src="<?php echo e(asset('img/LogoGaucho_png.png')); ?>" alt="El Gaucho Va" class="logo"></a>
            <?php if($esClienteRegistrado): ?><button type="button" class="delivery-location delivery-location-button" id="open-location-modal"><span class="location-pin">⌖</span><span class="delivery-location-copy"><small>Entregar en</small><strong id="delivery-location-value"><?php echo e($direccionEntrega ?: 'Seleccionar dirección'); ?></strong></span><span class="location-chevron">⌄</span></button><?php endif; ?>
            <nav class="header-actions" aria-label="Acciones de cuenta">
                <?php if($esClienteRegistrado): ?><a class="cart-link" href="<?php echo e(route('carrito')); ?>" aria-label="Ver carrito"><span>🛒</span><b>Carrito</b><em><?php echo e($cantidadCarrito); ?></em></a><?php endif; ?>
                <div class="profile-container"><button type="button" class="profile-button" aria-label="Abrir perfil" data-profile-toggle><span>◉</span><b><?php echo e($nombreUsuario ? 'Mi cuenta' : 'Ingresar'); ?></b></button><div class="profile-menu" data-profile-menu><?php if($nombreUsuario): ?><strong><?php echo e($nombreUsuario); ?></strong><span><?php echo e($tipoUsuario); ?></span><small><?php echo e($correoUsuario); ?></small><?php if($tipoUsuario === 'Cliente'): ?><a href="<?php echo e(route('cliente.profile')); ?>">Ver mi perfil</a><?php endif; ?><form method="POST" action="<?php echo e(route('logout')); ?>"><?php echo csrf_field(); ?><button type="submit">Cerrar sesión</button></form><?php else: ?><a href="<?php echo e(route('login')); ?>">Iniciar sesión</a><?php endif; ?></div></div>
            </nav>
        </div>
    </header>

    <main class="home-main">
        <section class="hero-section" aria-labelledby="hero-title"><div class="hero-copy"><p class="eyebrow">Tu día, resuelto</p><h1 id="hero-title">Todo lo que querés,<br><span>más cerca.</span></h1><p class="hero-description">Comida, compras y antojos de tus locales favoritos en un solo lugar.</p><form class="search-form" method="GET" action="<?php echo e(route('home')); ?>"><label class="sr-only" for="campo-busqueda">Buscar productos o locales</label><span class="search-icon">⌕</span><input type="search" id="campo-busqueda" name="q" value="<?php echo e(request('q')); ?>" placeholder="¿Qué estás buscando hoy?"><button type="submit">Buscar</button></form></div><div class="hero-art" aria-hidden="true"><video autoplay muted loop playsinline><source src="<?php echo e(asset('img/Video_corto.mp4')); ?>" type="video/mp4"></video></div></section>

        <section class="category-section" aria-labelledby="category-title"><div class="section-heading"><div><p class="eyebrow">Explorá</p><h2 id="category-title">¿Qué necesitás?</h2></div><a href="#cerca">Ver todo <span>→</span></a></div><div class="carousel-row category-carousel" data-carousel><div class="carousel-track"><?php $__currentLoopData = $categorias; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $categoria): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><a class="category-item <?php echo e(request('categoria') === $categoria['filtro'] ? 'is-active' : ''); ?>" href="<?php echo e($categoria['filtro'] ? route('home', ['categoria' => $categoria['filtro']]) : '#cerca'); ?>"><span class="category-icon"><?php echo e($categoria['icono']); ?></span><span><?php echo e($categoria['nombre']); ?></span></a><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?></div><div class="carousel-controls"><button type="button" data-carousel-prev aria-label="Ver categorías anteriores">←</button><button type="button" data-carousel-next aria-label="Ver más categorías">→</button></div></div></section>

        <section class="promo-banner" aria-label="Promoción destacada"><div><p class="eyebrow">KRYMS recomienda</p><h2>Tu próximo antojo<br>está a un click.</h2><p>Descubrí propuestas cerca tuyo.</p><a class="primary-button" href="#cerca">Explorar locales <span>→</span></a></div><img src="https://images.unsplash.com/photo-1504674900247-0877df9cc836?auto=format&fit=crop&w=1000&q=85" alt="Mesa con comida lista para compartir"></section>

        <?php if(request()->filled('q') || request()->filled('categoria')): ?>
            <section class="results-section" id="cerca"><div class="section-heading"><div><p class="eyebrow">Encontramos</p><h2>Resultados <?php echo e(request('q') ? 'para “'.request('q').'”' : 'de '.request('categoria')); ?></h2></div><span class="result-count"><?php echo e($productos->count()); ?> productos</span></div><?php if($productos->isNotEmpty()): ?><div class="product-grid"><?php $__currentLoopData = $productos; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $producto): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?> <?php echo $__env->make('home-product-card', ['producto' => $producto], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?> <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?></div><?php else: ?><p class="empty-state">No encontramos productos con esos filtros.</p><?php endif; ?></section>
        <?php else: ?>
            <section class="content-section" id="cerca"><div class="section-heading"><div><p class="eyebrow">Todos los comercios</p><h2>Explora los locales disponibles</h2></div><a href="<?php echo e(route('restaurants')); ?>">Ver todos <span>→</span></a></div><div class="carousel-row local-carousel" data-carousel><div class="carousel-track"><?php $__empty_1 = true; $__currentLoopData = $locales; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $local): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?><a class="local-card" href="<?php echo e(route('local.profile', $local->RUT)); ?>"><div class="local-image"><img src="<?php echo e($local->Logo ?: 'https://images.unsplash.com/photo-1517248135467-4c7edcad34c4?auto=format&fit=crop&w=700&q=80'); ?>" alt="<?php echo e($local->Nombre_Comercio ?: 'Local'); ?>"><span class="status-pill <?php echo e($local->Abierto ? '' : 'closed-status'); ?>">● <?php echo e($local->Abierto ? 'Abierto' : 'Cerrado'); ?></span></div><div class="local-card-body"><h3><?php echo e($local->Nombre_Comercio ?: 'Local sin nombre'); ?></h3><p><?php echo e($local->Horario ?: 'Horario no informado'); ?></p><div class="card-meta"><span>Ver perfil</span><span>⌁ 25-35 min</span><span>Envío $U 80</span></div></div></a><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?><p class="empty-state">Todavía no hay locales registrados.</p><?php endif; ?></div><div class="carousel-controls"><button type="button" data-carousel-prev aria-label="Ver locales anteriores">←</button><button type="button" data-carousel-next aria-label="Ver más locales">→</button></div></div></section>

            <section class="content-section offers-section"><div class="section-heading"><div><p class="eyebrow">Aprovechá hoy</p><h2>Ofertas para vos</h2></div></div><div class="carousel-row offer-carousel" data-carousel><div class="carousel-track"><?php $__empty_1 = true; $__currentLoopData = $ofertas; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $oferta): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?><article class="offer-card"><div class="offer-copy"><span class="offer-tag"><?php echo e(number_format($oferta->Descuento_Porcentaje, 0)); ?>% OFF</span><h3><?php echo e($oferta->Nombre_Producto); ?></h3><p><?php echo e($oferta->Nombre_Comercio ?: 'Local adherido'); ?></p><?php if(!$oferta->Abierto): ?><span class="unavailable-label closed-status">Cerrado</span><?php elseif($esClienteRegistrado): ?><button type="button" class="purchase-trigger offer-buy-button" data-product-id="<?php echo e($oferta->ID_Producto); ?>" data-product-name="<?php echo e($oferta->Nombre_Producto); ?>" data-product-price="$U <?php echo e(number_format($oferta->Precio, 2, ',', '.')); ?>" data-product-local="<?php echo e($oferta->Nombre_Comercio ?: 'Local adherido'); ?>">Comprar oferta</button><?php else: ?><a class="offer-buy-button" href="<?php echo e(route('login')); ?>">Iniciar sesión para comprar</a><?php endif; ?></div><img src="<?php echo e($oferta->Foto_Producto ?: 'https://images.unsplash.com/photo-1546069901-ba9599a7e63c?auto=format&fit=crop&w=500&q=80'); ?>" alt="<?php echo e($oferta->Nombre_Producto); ?>"></article><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?><article class="offer-card offer-placeholder"><div class="offer-copy"><span class="offer-tag">NUEVO</span><h3>Las mejores promos<br>llegan pronto</h3><p>Prepará tu próxima compra.</p></div><span class="offer-mark">%</span></article><?php endif; ?></div><div class="carousel-controls"><button type="button" data-carousel-prev aria-label="Ver ofertas anteriores">←</button><button type="button" data-carousel-next aria-label="Ver más ofertas">→</button></div></div></section>

            <section class="content-section popular-section"><div class="section-heading"><div><p class="eyebrow">Lo que más sale</p><h2>Más pedidos</h2></div><a href="<?php echo e(route('products')); ?>">Ver todos <span>→</span></a></div><div class="carousel-row" data-carousel><div class="carousel-track"><?php $__empty_1 = true; $__currentLoopData = $populares; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $producto): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?> <?php echo $__env->make('home-product-card', ['producto' => $producto], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?> <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?><p class="empty-state">Aún no hay productos publicados.</p><?php endif; ?></div><div class="carousel-controls"><button type="button" data-carousel-prev aria-label="Ver productos anteriores">←</button><button type="button" data-carousel-next aria-label="Ver más productos">→</button></div></div></section>

            <?php $__currentLoopData = $productosPorCategoria; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $nombreCategoria => $productosCategoria): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <?php if($productosCategoria->isNotEmpty()): ?>
                    <section class="content-section category-products-section">
                        <div class="section-heading"><div><p class="eyebrow">Elegí por categoría</p><h2><?php echo e($nombreCategoria); ?></h2></div></div>
                        <div class="carousel-row" data-carousel><div class="carousel-track"><?php $__currentLoopData = $productosCategoria; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $producto): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?> <?php echo $__env->make('home-product-card', ['producto' => $producto], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?> <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?></div></div><div class="carousel-controls category-products-controls" data-carousel-controls><button type="button" data-carousel-prev aria-label="Ver productos anteriores de <?php echo e($nombreCategoria); ?>">←</button><button type="button" data-carousel-next aria-label="Ver más productos de <?php echo e($nombreCategoria); ?>">→</button></div>
                    </section>
                <?php endif; ?>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

            <section class="content-section restaurants-section" id="restaurantes"><div class="section-heading"><div><p class="eyebrow">Para elegir sin vueltas</p><h2>Restaurantes destacados</h2></div><a href="<?php echo e(route('restaurants')); ?>">Ver todos <span>→</span></a></div><div class="restaurant-grid"><?php $__empty_1 = true; $__currentLoopData = $restaurantes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $productosLocal): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?> <?php ($restaurante = $productosLocal->first()); ?><a class="restaurant-card" href="<?php echo e(route('local.profile', $restaurante->RUT_Comercio)); ?>"><img src="<?php echo e($restaurante->Logo ?: ($restaurante->Foto_Producto ?: 'https://images.unsplash.com/photo-1552566626-52f8b828add9?auto=format&fit=crop&w=700&q=80')); ?>" alt="<?php echo e($restaurante->Nombre_Comercio ?: 'Restaurante'); ?>"><div><span class="restaurant-type"><?php echo e($restaurante->Categoria ?: 'Cocina local'); ?></span><h3><?php echo e($restaurante->Nombre_Comercio ?: 'Restaurante destacado'); ?></h3><p>★ 4.7 · 30-40 min · Envío $U 80</p></div></a><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?><p class="empty-state">Pronto vas a ver restaurantes destacados.</p><?php endif; ?></div></section>
        <?php endif; ?>
    </main>

    <?php if($esClienteRegistrado): ?><dialog id="purchase-modal" class="purchase-modal"><div class="purchase-content"><div class="purchase-header"><h2>Agregar al carrito</h2><button type="button" class="purchase-close" id="close-purchase-modal" aria-label="Cerrar">×</button></div><div class="purchase-details"><strong id="purchase-product-name"></strong><p id="purchase-product-local"></p><p id="purchase-product-price"></p></div><form method="POST" action="<?php echo e(route('carrito.add')); ?>" class="purchase-form"><?php echo csrf_field(); ?><input type="hidden" name="producto_id" id="purchase-product-id"><label for="purchase-quantity">Cantidad</label><input type="number" name="cantidad" id="purchase-quantity" min="1" value="1" required><button type="submit">Agregar al carrito</button></form></div></dialog><dialog id="location-modal" class="location-modal"><div class="location-modal-content"><div class="purchase-header"><div><span class="location-modal-kicker">Zona de entrega</span><h2>¿Dónde te entregamos?</h2></div><button type="button" class="purchase-close" id="close-location-modal" aria-label="Cerrar">×</button></div><p class="location-modal-help">Escribí una dirección o usá tu ubicación actual.</p><form method="POST" action="<?php echo e(route('cliente.location.update')); ?>" class="location-form"><?php echo csrf_field(); ?> <?php echo method_field('PATCH'); ?><label for="direccion-entrega">Dirección de entrega</label><input type="text" name="direccion" id="direccion-entrega" value="<?php echo e($direccionEntrega); ?>" maxlength="500" placeholder="Calle, número y ciudad" required><div class="location-map-heading"><span class="location-map-icon">⌖</span><span><strong>Seleccioná el punto en el mapa</strong><small>Tocá el mapa o mové el marcador para ajustar tu dirección.</small></span><a href="<?php echo e(route('cliente.location.form')); ?>" class="location-map-link">Abrir mapa completo</a></div><div id="header-map" class="header-map"></div><p class="location-status" id="location-status" role="status">Podés mover el marcador o tocar el mapa para corregirlo.</p><input type="hidden" name="latitud" id="header-latitud" value="<?php echo e($latitudUsuario); ?>"><input type="hidden" name="longitud" id="header-longitud" value="<?php echo e($longitudUsuario); ?>"><button type="button" class="location-current-button" id="use-current-location">⌖ Usar mi ubicación actual</button><button type="submit">Guardar ubicación</button></form></div></dialog><?php endif; ?>

    <footer class="site-footer"><div class="footer-inner"><div class="footer-brand"><strong>KRYMS</strong><p>Lo que necesitás,<br>más cerca.</p></div><div><h3>Descubrí</h3><a href="#cerca">Locales</a><a href="#restaurantes">Restaurantes</a><a href="#">Ofertas</a></div><div><h3>Ayuda</h3><a href="#">Preguntas frecuentes</a><a href="#">Contacto</a><a href="#">Términos y privacidad</a></div><div class="footer-social"><h3>Seguinos</h3><a href="#" aria-label="Instagram">◎</a><a href="#" aria-label="Facebook">f</a><a href="#" aria-label="TikTok">♪</a></div></div><div class="footer-bottom"><span>© <?php echo e(date('Y')); ?> KRYMS. Todos los derechos reservados.</span><span>Hecho para moverte mejor.</span></div></footer>

    <?php if($esClienteRegistrado): ?><script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script><?php endif; ?>
    <script>
        const profileToggle = document.querySelector('[data-profile-toggle]');
        const profileMenu = document.querySelector('[data-profile-menu]');
        profileToggle?.addEventListener('click', () => profileMenu.classList.toggle('is-open'));
        document.addEventListener('click', (event) => { if (!event.target.closest('.profile-container')) profileMenu?.classList.remove('is-open'); });
        const toast = document.querySelector('.toast-notification');
        if (toast) window.setTimeout(() => toast.classList.add('is-hidden'), 3000);
        const purchaseModal = document.getElementById('purchase-modal');
        document.querySelectorAll('.purchase-trigger[data-product-id]').forEach((trigger) => trigger.addEventListener('click', () => {
            if (trigger.disabled) return;
            document.getElementById('purchase-product-id').value = trigger.dataset.productId;
            document.getElementById('purchase-product-name').textContent = trigger.dataset.productName;
            document.getElementById('purchase-product-local').textContent = `Local: ${trigger.dataset.productLocal}`;
            document.getElementById('purchase-product-price').textContent = trigger.dataset.productPrice;
            document.getElementById('purchase-quantity').value = 1;
            purchaseModal.showModal();
        }));
        document.getElementById('close-purchase-modal')?.addEventListener('click', () => purchaseModal.close());
        purchaseModal?.addEventListener('click', (event) => { if (event.target === purchaseModal) purchaseModal.close(); });
        <?php if($esClienteRegistrado): ?>
            const locationModal = document.getElementById('location-modal');
            const openLocationModal = document.getElementById('open-location-modal');
            const closeLocationModal = document.getElementById('close-location-modal');
            const headerMap = L.map('header-map').setView([-32.3167, -58.0833], 13);
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', { maxZoom: 19, attribution: '&copy; OpenStreetMap contributors' }).addTo(headerMap);
            const headerLatitud = document.getElementById('header-latitud');
            const headerLongitud = document.getElementById('header-longitud');
            const deliveryLocationValue = document.getElementById('delivery-location-value');
            const deliveryAddressInput = document.getElementById('direccion-entrega');
            const locationStatus = document.getElementById('location-status');
            let headerMarker;
            let accuracyCircle;
            const setDeliveryLabel = (label) => { if (deliveryLocationValue && label) deliveryLocationValue.textContent = label; };
            const setLocationStatus = (message, isError = false) => { if (locationStatus) { locationStatus.textContent = message; locationStatus.classList.toggle('is-error', isError); } };
            const setHeaderLocation = (latitud, longitud, label = deliveryAddressInput.value || 'Seleccionar dirección', precision = null) => { headerLatitud.value = latitud.toFixed(7); headerLongitud.value = longitud.toFixed(7); setDeliveryLabel(label); if (!headerMarker) { headerMarker = L.marker([latitud, longitud], { draggable: true }).addTo(headerMap); headerMarker.on('dragend', () => { const position = headerMarker.getLatLng(); setHeaderLocation(position.lat, position.lng, 'Buscando ubicación…'); fillAddressFromCoordinates(position.lat, position.lng); }); } else { headerMarker.setLatLng([latitud, longitud]); } if (precision) { if (!accuracyCircle) accuracyCircle = L.circle([latitud, longitud], { radius: precision, color: '#e28000', fillColor: '#f9d7a0', fillOpacity: .22, weight: 1 }).addTo(headerMap); else accuracyCircle.setLatLng([latitud, longitud]).setRadius(precision); } headerMap.setView([latitud, longitud], Math.max(headerMap.getZoom(), 15)); };
            if (headerLatitud.value && headerLongitud.value) setHeaderLocation(Number(headerLatitud.value), Number(headerLongitud.value), deliveryAddressInput.value || 'Seleccionar dirección');
            const fillAddressFromCoordinates = async (latitud, longitud) => { try { const response = await fetch(`https://nominatim.openstreetmap.org/reverse?format=jsonv2&lat=${latitud}&lon=${longitud}`); const result = await response.json(); const address = result.display_name?.split(',').slice(0, 3).join(','); if (address) { deliveryAddressInput.value = address; setDeliveryLabel(address); setLocationStatus('Punto seleccionado: dirección encontrada.'); } } catch (error) { setLocationStatus('Punto seleccionado. Escribí la dirección para guardarla.', true); } };
            headerMap.on('click', (event) => { setHeaderLocation(event.latlng.lat, event.latlng.lng, 'Buscando ubicación…'); fillAddressFromCoordinates(event.latlng.lat, event.latlng.lng); });
            openLocationModal?.addEventListener('click', () => { locationModal.showModal(); window.setTimeout(() => headerMap.invalidateSize(), 50); });
            closeLocationModal?.addEventListener('click', () => locationModal.close());
            locationModal?.addEventListener('click', (event) => { if (event.target === locationModal) locationModal.close(); });
            deliveryAddressInput?.addEventListener('input', () => setDeliveryLabel(deliveryAddressInput.value.trim() || 'Seleccionar dirección'));
            document.getElementById('use-current-location')?.addEventListener('click', () => { if (!navigator.geolocation) { setLocationStatus('Tu navegador no permite detectar la ubicación.', true); return; } setLocationStatus('Buscando tu ubicación…'); navigator.geolocation.getCurrentPosition(async (position) => { const { latitude, longitude, accuracy } = position.coords; setHeaderLocation(latitude, longitude, 'Ubicación actual', accuracy); setLocationStatus(`Ubicación detectada con una precisión aproximada de ${Math.round(accuracy)} m. Podés corregirla en el mapa.`); try { const response = await fetch(`https://nominatim.openstreetmap.org/reverse?format=jsonv2&lat=${latitude}&lon=${longitude}`); const result = await response.json(); const address = result.display_name?.split(',').slice(0, 3).join(','); if (address) { deliveryAddressInput.value = address; setDeliveryLabel(address); } } catch (error) { /* Keep the detected coordinates when reverse geocoding is unavailable. */ } }, (error) => { const message = error.code === 1 ? 'Permiso de ubicación denegado. Podés elegir el punto directamente en el mapa.' : 'No pudimos detectar tu ubicación. Elegí el punto directamente en el mapa.'; setLocationStatus(message, true); }, { enableHighAccuracy: true, timeout: 10000, maximumAge: 0 }); });
        <?php endif; ?>
        document.querySelectorAll('[data-carousel]').forEach((carousel) => {
            const track = carousel.querySelector('.carousel-track');
            const controls = carousel.querySelector('[data-carousel-controls]') || carousel.closest('.category-products-section')?.querySelector('[data-carousel-controls]');
            const previous = carousel.querySelector('[data-carousel-prev]') || controls?.querySelector('[data-carousel-prev]');
            const next = carousel.querySelector('[data-carousel-next]') || controls?.querySelector('[data-carousel-next]');

            if (!track || !previous || !next) return;

            const updateControls = () => {
                previous.disabled = track.scrollLeft <= 2;
                next.disabled = track.scrollLeft + track.clientWidth >= track.scrollWidth - 2;
            };
            const scrollAmount = () => Math.max(track.clientWidth * .72, 220);

            previous.addEventListener('click', () => track.scrollBy({ left: -scrollAmount(), behavior: 'smooth' }));
            next.addEventListener('click', () => track.scrollBy({ left: scrollAmount(), behavior: 'smooth' }));
            track.addEventListener('scroll', updateControls, { passive: true });
            window.addEventListener('resize', updateControls);
            updateControls();
        });
    </script>
</body>
</html>
<?php /**PATH /opt/lampp/htdocs/Proyecto_KRYMS_2026_UTU/resources/views/home.blade.php ENDPATH**/ ?>