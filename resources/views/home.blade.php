@php
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
@endphp
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="KRYMS: pedí lo que necesitás y recibilo donde estés.">
    <link rel="stylesheet" href="{{ asset('css/styles.css') }}">
    <link rel="stylesheet" href="{{ asset('css/home.css') }}">
    @if ($esClienteRegistrado)<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css">@endif
    <title>KRYMS | Pedí cerca, recibí fácil</title>
</head>
<body class="home-page">
    @if (session('success'))
        <p class="alert success-message toast-notification" role="status">{{ session('success') }}</p>
    @endif
    <header class="site-header">
        <div class="header-inner">
            <a class="brand" href="{{ route('home') }}" aria-label="El Gaucho Va inicio"><img src="{{ asset('img/LogoGaucho_png.png') }}" alt="El Gaucho Va" class="logo"></a>
            @if ($esClienteRegistrado)<button type="button" class="delivery-location delivery-location-button" id="open-location-modal"><span class="location-pin">⌖</span><span class="delivery-location-copy"><small>Entregar en</small><strong id="delivery-location-value">{{ $direccionEntrega ?: 'Seleccionar dirección' }}</strong></span><span class="location-chevron">⌄</span></button>@endif
            <nav class="header-actions" aria-label="Acciones de cuenta">
                @if ($esClienteRegistrado)<a class="cart-link" href="{{ route('carrito') }}" aria-label="Ver carrito"><span>🛒</span><b>Carrito</b><em>{{ $cantidadCarrito }}</em></a>@endif
                <div class="profile-container"><button type="button" class="profile-button" aria-label="Abrir perfil" data-profile-toggle><span>◉</span><b>{{ $nombreUsuario ? 'Mi cuenta' : 'Ingresar' }}</b></button><div class="profile-menu" data-profile-menu>@if ($nombreUsuario)<strong>{{ $nombreUsuario }}</strong><span>{{ $tipoUsuario }}</span><small>{{ $correoUsuario }}</small>@if ($tipoUsuario === 'Cliente')<a href="{{ route('cliente.profile') }}">Ver mi perfil</a>@endif<form method="POST" action="{{ route('logout') }}">@csrf<button type="submit">Cerrar sesión</button></form>@else<a href="{{ route('login') }}">Iniciar sesión</a>@endif</div></div>
            </nav>
        </div>
    </header>

    <main class="home-main">
        <section class="hero-section" aria-labelledby="hero-title"><div class="hero-copy"><p class="eyebrow">Tu día, resuelto</p><h1 id="hero-title">Todo lo que querés,<br><span>más cerca.</span></h1><p class="hero-description">Comida, compras y antojos de tus locales favoritos en un solo lugar.</p><form class="search-form" method="GET" action="{{ route('home') }}"><label class="sr-only" for="campo-busqueda">Buscar productos o locales</label><span class="search-icon">⌕</span><input type="search" id="campo-busqueda" name="q" value="{{ request('q') }}" placeholder="¿Qué estás buscando hoy?"><button type="submit">Buscar</button></form></div><div class="hero-art" aria-hidden="true"><video autoplay muted loop playsinline><source src="{{ asset('img/Video_corto.mp4') }}" type="video/mp4"></video></div></section>

        <section class="category-section" aria-labelledby="category-title"><div class="section-heading"><div><p class="eyebrow">Explorá</p><h2 id="category-title">¿Qué necesitás?</h2></div><a href="#cerca">Ver todo <span>→</span></a></div><div class="carousel-row category-carousel" data-carousel><div class="carousel-track">@foreach ($categorias as $categoria)<a class="category-item {{ request('categoria') === $categoria['filtro'] ? 'is-active' : '' }}" href="{{ $categoria['filtro'] ? route('home', ['categoria' => $categoria['filtro']]) : '#cerca' }}"><span class="category-icon">{{ $categoria['icono'] }}</span><span>{{ $categoria['nombre'] }}</span></a>@endforeach</div><div class="carousel-controls"><button type="button" data-carousel-prev aria-label="Ver categorías anteriores">←</button><button type="button" data-carousel-next aria-label="Ver más categorías">→</button></div></div></section>

        <section class="promo-banner" aria-label="Promoción destacada"><div><p class="eyebrow">KRYMS recomienda</p><h2>Tu próximo antojo<br>está a un click.</h2><p>Descubrí propuestas cerca tuyo.</p><a class="primary-button" href="#cerca">Explorar locales <span>→</span></a></div><img src="https://images.unsplash.com/photo-1504674900247-0877df9cc836?auto=format&fit=crop&w=1000&q=85" alt="Mesa con comida lista para compartir"></section>

        @if (request()->filled('q') || request()->filled('categoria'))
            <section class="results-section" id="cerca"><div class="section-heading"><div><p class="eyebrow">Encontramos</p><h2>Resultados {{ request('q') ? 'para “'.request('q').'”' : 'de '.request('categoria') }}</h2></div><span class="result-count">{{ $productos->count() }} productos</span></div>@if ($productos->isNotEmpty())<div class="product-grid">@foreach ($productos as $producto) @include('home-product-card', ['producto' => $producto]) @endforeach</div>@else<p class="empty-state">No encontramos productos con esos filtros.</p>@endif</section>
        @else
            <section class="content-section" id="cerca"><div class="section-heading"><div><p class="eyebrow">Todos los comercios</p><h2>Explora los locales disponibles</h2></div><a href="{{ route('restaurants') }}">Ver restaurantes <span>→</span></a></div><div class="carousel-row local-carousel" data-carousel><div class="carousel-track">@forelse ($locales as $local)<a class="local-card" href="{{ route('local.profile', $local->RUT) }}"><div class="local-image"><img src="{{ $local->Logo ?: 'https://images.unsplash.com/photo-1517248135467-4c7edcad34c4?auto=format&fit=crop&w=700&q=80' }}" alt="{{ $local->Nombre_Comercio ?: 'Local' }}"><span class="status-pill {{ $local->Abierto ? '' : 'closed-status' }}">● {{ $local->Abierto ? 'Abierto' : 'Cerrado' }}</span></div><div class="local-card-body"><h3>{{ $local->Nombre_Comercio ?: 'Local sin nombre' }}</h3><p>{{ $local->Horario ?: 'Horario no informado' }}</p><div class="card-meta"><span>Ver perfil</span><span>⌁ 25-35 min</span><span>Envío $U 80</span></div></div></a>@empty<p class="empty-state">Todavía no hay locales registrados.</p>@endforelse</div><div class="carousel-controls"><button type="button" data-carousel-prev aria-label="Ver locales anteriores">←</button><button type="button" data-carousel-next aria-label="Ver más locales">→</button></div></div></section>

            <section class="content-section offers-section"><div class="section-heading"><div><p class="eyebrow">Aprovechá hoy</p><h2>Ofertas para vos</h2></div></div><div class="carousel-row offer-carousel" data-carousel><div class="carousel-track">@forelse ($ofertas as $oferta)<article class="offer-card"><div class="offer-copy"><span class="offer-tag">{{ number_format($oferta->Descuento_Porcentaje, 0) }}% OFF</span><h3>{{ $oferta->Nombre_Producto }}</h3><p>{{ $oferta->Nombre_Comercio ?: 'Local adherido' }}</p>@if ($esClienteRegistrado)<button type="button" class="purchase-trigger offer-buy-button" data-product-id="{{ $oferta->ID_Producto }}" data-product-name="{{ $oferta->Nombre_Producto }}" data-product-price="$U {{ number_format($oferta->Precio, 2, ',', '.') }}" data-product-local="{{ $oferta->Nombre_Comercio ?: 'Local adherido' }}">Comprar oferta</button>@else<a class="offer-buy-button" href="{{ route('login') }}">Iniciar sesión para comprar</a>@endif</div><img src="{{ $oferta->Foto_Producto ?: 'https://images.unsplash.com/photo-1546069901-ba9599a7e63c?auto=format&fit=crop&w=500&q=80' }}" alt="{{ $oferta->Nombre_Producto }}"></article>@empty<article class="offer-card offer-placeholder"><div class="offer-copy"><span class="offer-tag">NUEVO</span><h3>Las mejores promos<br>llegan pronto</h3><p>Prepará tu próxima compra.</p></div><span class="offer-mark">%</span></article>@endforelse</div><div class="carousel-controls"><button type="button" data-carousel-prev aria-label="Ver ofertas anteriores">←</button><button type="button" data-carousel-next aria-label="Ver más ofertas">→</button></div></div></section>

            <section class="content-section popular-section"><div class="section-heading"><div><p class="eyebrow">Lo que más sale</p><h2>Más pedidos</h2></div><a href="{{ route('products') }}">Ver todos <span>→</span></a></div><div class="carousel-row" data-carousel><div class="carousel-track">@forelse ($populares as $producto) @include('home-product-card', ['producto' => $producto]) @empty<p class="empty-state">Aún no hay productos publicados.</p>@endforelse</div><div class="carousel-controls"><button type="button" data-carousel-prev aria-label="Ver productos anteriores">←</button><button type="button" data-carousel-next aria-label="Ver más productos">→</button></div></div></section>

            @foreach ($productosPorCategoria as $nombreCategoria => $productosCategoria)
                @if ($productosCategoria->isNotEmpty())
                    <section class="content-section category-products-section">
                        <div class="section-heading"><div><p class="eyebrow">Elegí por categoría</p><h2>{{ $nombreCategoria }}</h2></div></div>
                        <div class="carousel-row" data-carousel><div class="carousel-track">@foreach ($productosCategoria as $producto) @include('home-product-card', ['producto' => $producto]) @endforeach</div></div><div class="carousel-controls category-products-controls" data-carousel-controls><button type="button" data-carousel-prev aria-label="Ver productos anteriores de {{ $nombreCategoria }}">←</button><button type="button" data-carousel-next aria-label="Ver más productos de {{ $nombreCategoria }}">→</button></div>
                    </section>
                @endif
            @endforeach

            <section class="content-section restaurants-section" id="restaurantes"><div class="section-heading"><div><p class="eyebrow">Para elegir sin vueltas</p><h2>Restaurantes destacados</h2></div><a href="{{ route('restaurants') }}">Ver todos <span>→</span></a></div><div class="restaurant-grid">@forelse ($restaurantes as $productosLocal) @php($restaurante = $productosLocal->first())<a class="restaurant-card" href="{{ route('local.profile', $restaurante->RUT_Comercio) }}"><img src="{{ $restaurante->Logo ?: ($restaurante->Foto_Producto ?: 'https://images.unsplash.com/photo-1552566626-52f8b828add9?auto=format&fit=crop&w=700&q=80') }}" alt="{{ $restaurante->Nombre_Comercio ?: 'Restaurante' }}"><div><span class="restaurant-type">{{ $restaurante->Categoria ?: 'Cocina local' }}</span><h3>{{ $restaurante->Nombre_Comercio ?: 'Restaurante destacado' }}</h3><p>★ 4.7 · 30-40 min · Envío $U 80</p></div></a>@empty<p class="empty-state">Pronto vas a ver restaurantes destacados.</p>@endforelse</div></section>
        @endif
    </main>

    @if ($esClienteRegistrado)<dialog id="purchase-modal" class="purchase-modal"><div class="purchase-content"><div class="purchase-header"><h2>Agregar al carrito</h2><button type="button" class="purchase-close" id="close-purchase-modal" aria-label="Cerrar">×</button></div><div class="purchase-details"><strong id="purchase-product-name"></strong><p id="purchase-product-local"></p><p id="purchase-product-price"></p></div><form method="POST" action="{{ route('carrito.add') }}" class="purchase-form">@csrf<input type="hidden" name="producto_id" id="purchase-product-id"><label for="purchase-quantity">Cantidad</label><input type="number" name="cantidad" id="purchase-quantity" min="1" value="1" required><button type="submit">Agregar al carrito</button></form></div></dialog><dialog id="location-modal" class="location-modal"><div class="location-modal-content"><div class="purchase-header"><div><span class="location-modal-kicker">Zona de entrega</span><h2>¿Dónde te entregamos?</h2></div><button type="button" class="purchase-close" id="close-location-modal" aria-label="Cerrar">×</button></div><p class="location-modal-help">Escribí una dirección o usá tu ubicación actual.</p><form method="POST" action="{{ route('cliente.location.update') }}" class="location-form">@csrf @method('PATCH')<label for="direccion-entrega">Dirección de entrega</label><input type="text" name="direccion" id="direccion-entrega" value="{{ $direccionEntrega }}" maxlength="500" placeholder="Calle, número y ciudad" required><div class="location-map-heading"><span class="location-map-icon">⌖</span><span><strong>Seleccioná el punto en el mapa</strong><small>Tocá el mapa o mové el marcador para ajustar tu dirección.</small></span><a href="{{ route('cliente.location.form') }}" class="location-map-link">Abrir mapa completo</a></div><div id="header-map" class="header-map"></div><p class="location-status" id="location-status" role="status">Podés mover el marcador o tocar el mapa para corregirlo.</p><input type="hidden" name="latitud" id="header-latitud" value="{{ $latitudUsuario }}"><input type="hidden" name="longitud" id="header-longitud" value="{{ $longitudUsuario }}"><button type="button" class="location-current-button" id="use-current-location">⌖ Usar mi ubicación actual</button><button type="submit">Guardar ubicación</button></form></div></dialog>@endif

    <footer class="site-footer"><div class="footer-inner"><div class="footer-brand"><strong>KRYMS</strong><p>Lo que necesitás,<br>más cerca.</p></div><div><h3>Descubrí</h3><a href="#cerca">Locales</a><a href="#restaurantes">Restaurantes</a><a href="#">Ofertas</a></div><div><h3>Ayuda</h3><a href="#">Preguntas frecuentes</a><a href="#">Contacto</a><a href="#">Términos y privacidad</a></div><div class="footer-social"><h3>Seguinos</h3><a href="#" aria-label="Instagram">◎</a><a href="#" aria-label="Facebook">f</a><a href="#" aria-label="TikTok">♪</a></div></div><div class="footer-bottom"><span>© {{ date('Y') }} KRYMS. Todos los derechos reservados.</span><span>Hecho para moverte mejor.</span></div></footer>

    @if ($esClienteRegistrado)<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>@endif
    <script>
        const profileToggle = document.querySelector('[data-profile-toggle]');
        const profileMenu = document.querySelector('[data-profile-menu]');
        profileToggle?.addEventListener('click', () => profileMenu.classList.toggle('is-open'));
        document.addEventListener('click', (event) => { if (!event.target.closest('.profile-container')) profileMenu?.classList.remove('is-open'); });
        const toast = document.querySelector('.toast-notification');
        if (toast) window.setTimeout(() => toast.classList.add('is-hidden'), 3000);
        const purchaseModal = document.getElementById('purchase-modal');
        document.querySelectorAll('.purchase-trigger[data-product-id]').forEach((trigger) => trigger.addEventListener('click', () => { document.getElementById('purchase-product-id').value = trigger.dataset.productId; document.getElementById('purchase-product-name').textContent = trigger.dataset.productName; document.getElementById('purchase-product-local').textContent = `Local: ${trigger.dataset.productLocal}`; document.getElementById('purchase-product-price').textContent = trigger.dataset.productPrice; document.getElementById('purchase-quantity').value = 1; purchaseModal.showModal(); }));
        document.getElementById('close-purchase-modal')?.addEventListener('click', () => purchaseModal.close());
        purchaseModal?.addEventListener('click', (event) => { if (event.target === purchaseModal) purchaseModal.close(); });
        @if ($esClienteRegistrado)
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
        @endif
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
