<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Descubrí todos los productos disponibles en KRYMS.">
    <link rel="stylesheet" href="{{ asset('css/styles.css') }}">
    <link rel="stylesheet" href="{{ asset('css/home.css') }}">
    <link rel="stylesheet" href="{{ asset('css/products.css') }}">
    <title>Productos | KRYMS</title>
</head>
<body class="home-page products-page">
    @if (session('success'))
        <p class="alert success-message toast-notification" role="status">{{ session('success') }}</p>
    @endif
    <header class="site-header">
        <div class="header-inner">
            <a class="brand" href="{{ route('home') }}" aria-label="Volver al inicio"><img src="{{ asset('img/Logo_GauchoVA.jpeg') }}" alt="El Gaucho Va" class="logo"></a>
            <a class="products-back-link" href="{{ route('home') }}">← Volver al inicio</a>
            <nav class="header-actions" aria-label="Acciones de cuenta">
                @if ($esClienteRegistrado)<a class="cart-link" href="{{ route('carrito') }}" aria-label="Ver carrito"><span>🛒</span><b>Carrito</b></a>@endif
                <div class="profile-container"><button type="button" class="profile-button" aria-label="Abrir perfil" data-profile-toggle><span>◉</span><b>{{ $esClienteRegistrado ? 'Mi cuenta' : 'Ingresar' }}</b></button><div class="profile-menu" data-profile-menu><a href="{{ route('login') }}">{{ $esClienteRegistrado ? 'Mi cuenta' : 'Iniciar sesión' }}</a></div></div>
            </nav>
        </div>
    </header>

    <main class="products-main">
        <section class="products-hero">
            <div>
                <p class="eyebrow">Todo para tu día</p>
                <h1>Descubrí todos<br><span>los productos.</span></h1>
                <p>Explorá la propuesta completa de los locales registrados en KRYMS.</p>
            </div>
            <div class="products-hero-mark" aria-hidden="true">+</div>
        </section>

        <section class="products-list-section" aria-labelledby="products-title">
            <div class="products-list-heading"><div><p class="eyebrow">Catálogo KRYMS</p><h2 id="products-title">Todos los productos</h2></div><span class="products-count">{{ $productos->count() }} {{ $productos->count() === 1 ? 'producto' : 'productos' }}</span></div>
            @if ($productos->isNotEmpty())
                <div class="product-grid">
                    @foreach ($productos as $producto)
                        @include('home-product-card', ['producto' => $producto])
                    @endforeach
                </div>
            @else
                <p class="empty-state">Todavía no hay productos publicados.</p>
            @endif
        </section>
    </main>

    @if ($esClienteRegistrado)
        <dialog id="purchase-modal" class="purchase-modal"><div class="purchase-content"><div class="purchase-header"><h2>Agregar al carrito</h2><button type="button" class="purchase-close" id="close-purchase-modal" aria-label="Cerrar">×</button></div><div class="purchase-details"><strong id="purchase-product-name"></strong><p id="purchase-product-local"></p><p id="purchase-product-price"></p></div><form method="POST" action="{{ route('carrito.add') }}" class="purchase-form">@csrf<input type="hidden" name="producto_id" id="purchase-product-id"><label for="purchase-quantity">Cantidad</label><input type="number" name="cantidad" id="purchase-quantity" min="1" value="1" required><button type="submit">Agregar al carrito</button></form></div></dialog>
    @endif

    <footer class="site-footer"><div class="footer-inner"><div class="footer-brand"><strong>KRYMS</strong><p>Lo que necesitás,<br>más cerca.</p></div><div><h3>Descubrí</h3><a href="{{ route('restaurants') }}">Restaurantes</a><a href="{{ route('products') }}">Productos</a><a href="{{ route('home') }}#cerca">Ofertas</a></div><div><h3>Ayuda</h3><a href="#">Preguntas frecuentes</a><a href="#">Contacto</a><a href="#">Términos y privacidad</a></div><div class="footer-social"><h3>Seguinos</h3><a href="#" aria-label="Instagram">◎</a><a href="#" aria-label="Facebook">f</a><a href="#" aria-label="TikTok">♪</a></div></div><div class="footer-bottom"><span>© {{ date('Y') }} KRYMS. Todos los derechos reservados.</span><span>Hecho para moverte mejor.</span></div></footer>

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
