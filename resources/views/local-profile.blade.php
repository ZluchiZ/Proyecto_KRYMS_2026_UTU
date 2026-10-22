<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Perfil de {{ $comercio->Nombre_Comercio ?: 'local' }} en KRYMS.">
    <link rel="stylesheet" href="{{ asset('css/styles.css') }}">
    <link rel="stylesheet" href="{{ asset('css/home.css') }}">
    <link rel="stylesheet" href="{{ asset('css/local-profile.css') }}">
    <title>{{ $comercio->Nombre_Comercio ?: 'Local' }} | KRYMS</title>
</head>
<body class="home-page local-profile-page">
    @if (session('success'))
        <p class="alert success-message toast-notification" role="status">{{ session('success') }}</p>
    @endif

    <header class="site-header">
        <div class="header-inner">
            <a class="brand" href="{{ route('home') }}" aria-label="Volver al inicio"><img src="{{ asset('img/LogoGaucho_png.png') }}" alt="El Gaucho Va" class="logo"></a>
            <a class="profile-back-link" href="{{ route('home') }}">← Volver al inicio</a>
            <nav class="header-actions" aria-label="Acciones de cuenta">
                @if ($esClienteRegistrado)<a class="cart-link" href="{{ route('carrito') }}" aria-label="Ver carrito"><span>🛒</span><b>Carrito</b></a>@endif
                <div class="profile-container"><button type="button" class="profile-button" aria-label="Abrir perfil" data-profile-toggle><span>◉</span><b>Cuenta</b></button><div class="profile-menu" data-profile-menu><a href="{{ route('login') }}">{{ $esClienteRegistrado ? 'Mi cuenta' : 'Iniciar sesión' }}</a></div></div>
            </nav>
        </div>
    </header>

    <main class="local-profile-main">
        <section class="local-profile-hero">
            <div class="local-profile-logo-wrap">
                <img src="{{ $comercio->Logo ?: 'https://images.unsplash.com/photo-1517248135467-4c7edcad34c4?auto=format&fit=crop&w=500&q=85' }}" alt="Logo de {{ $comercio->Nombre_Comercio ?: 'local' }}" class="local-profile-logo">
            </div>
            <div class="local-profile-heading">
                <span class="profile-eyebrow">Perfil del local</span>
                <h1>{{ $comercio->Nombre_Comercio ?: 'Local sin nombre' }}</h1>
                <span class="profile-status {{ $comercio->Abierto ? 'is-open' : 'is-closed' }}">● {{ $comercio->Abierto ? 'Abierto' : 'Cerrado' }}</span>
            </div>
        </section>

        <section class="local-profile-info" aria-label="Información del local">
            <div><span>Horario</span><strong>{{ $comercio->Horario ?: 'Horario no informado' }}</strong></div>
            <div><span>Dirección</span><strong>{{ $comercio->{'Dirección'} ?: 'Dirección no informada' }}</strong></div>
            <div><span>Productos</span><strong>{{ $productos->count() }} publicados</strong></div>
        </section>

        <section class="local-products-section" aria-labelledby="products-title">
            <div class="section-heading"><div><p class="eyebrow">Del local</p><h2 id="products-title">Productos</h2></div></div>
            @if ($productos->isNotEmpty())
                <div class="product-grid">
                    @foreach ($productos as $producto)
                        @include('home-product-card', ['producto' => $producto])
                    @endforeach
                </div>
            @else
                <p class="empty-state">Este local todavía no tiene productos publicados.</p>
            @endif
        </section>
    </main>

    @if ($esClienteRegistrado)
        <dialog id="purchase-modal" class="purchase-modal"><div class="purchase-content"><div class="purchase-header"><h2>Agregar al carrito</h2><button type="button" class="purchase-close" id="close-purchase-modal" aria-label="Cerrar">×</button></div><div class="purchase-details"><strong id="purchase-product-name"></strong><p id="purchase-product-local"></p><p id="purchase-product-price"></p></div><form method="POST" action="{{ route('carrito.add') }}" class="purchase-form">@csrf<input type="hidden" name="producto_id" id="purchase-product-id"><label for="purchase-quantity">Cantidad</label><input type="number" name="cantidad" id="purchase-quantity" min="1" value="1" required><button type="submit">Agregar al carrito</button></form></div></dialog>
    @endif

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
