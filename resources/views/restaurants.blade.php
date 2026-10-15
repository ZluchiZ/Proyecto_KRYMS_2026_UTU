<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Descubrí todos los restaurantes y locales disponibles en KRYMS.">
    <link rel="stylesheet" href="{{ asset('css/styles.css') }}">
    <link rel="stylesheet" href="{{ asset('css/home.css') }}">
    <link rel="stylesheet" href="{{ asset('css/restaurants.css') }}">
    <title>Restaurantes | KRYMS</title>
</head>
<body class="home-page restaurants-page">
    <header class="site-header">
        <div class="header-inner">
            <a class="brand" href="{{ route('home') }}" aria-label="Volver al inicio"><img src="{{ asset('img/Logo_GauchoVA.jpeg') }}" alt="El Gaucho Va" class="logo"></a>
            <a class="restaurants-back-link" href="{{ route('home') }}">← Volver al inicio</a>
            <nav class="header-actions" aria-label="Acciones de cuenta">
                @if ($esClienteRegistrado)<a class="cart-link" href="{{ route('carrito') }}" aria-label="Ver carrito"><span>🛒</span><b>Carrito</b></a>@endif
                <div class="profile-container"><button type="button" class="profile-button" aria-label="Abrir perfil" data-profile-toggle><span>◉</span><b>{{ $esClienteRegistrado ? 'Mi cuenta' : 'Ingresar' }}</b></button><div class="profile-menu" data-profile-menu><a href="{{ route('login') }}">{{ $esClienteRegistrado ? 'Mi cuenta' : 'Iniciar sesión' }}</a></div></div>
            </nav>
        </div>
    </header>

    <main class="restaurants-main">
        <section class="restaurants-hero">
            <div>
                <p class="eyebrow">Tu próximo lugar favorito</p>
                <h1>Explora todos<br><span>los restaurantes.</span></h1>
                <p>Encontrá locales abiertos, descubrí sus propuestas y conocé cada perfil antes de pedir.</p>
            </div>
            <div class="restaurants-hero-mark" aria-hidden="true">✦</div>
        </section>

        <section class="restaurants-list-section" aria-labelledby="restaurants-title">
            <div class="restaurants-list-heading">
                <div><p class="eyebrow">Directorio KRYMS</p><h2 id="restaurants-title">Todos los locales</h2></div>
                <span class="restaurants-count">{{ $locales->count() }} {{ $locales->count() === 1 ? 'local' : 'locales' }}</span>
            </div>
            @if ($locales->isNotEmpty())
                <div class="restaurants-grid">
                    @foreach ($locales as $local)
                        <a class="restaurant-directory-card" href="{{ route('local.profile', $local->RUT) }}">
                            <div class="restaurant-directory-image">
                                <img src="{{ $local->Logo ?: 'https://images.unsplash.com/photo-1517248135467-4c7edcad34c4?auto=format&fit=crop&w=800&q=85' }}" alt="{{ $local->Nombre_Comercio ?: 'Local' }}">
                                <span class="status-pill {{ $local->Abierto ? '' : 'closed-status' }}">● {{ $local->Abierto ? 'Abierto' : 'Cerrado' }}</span>
                            </div>
                            <div class="restaurant-directory-body">
                                <span class="restaurant-type">Local KRYMS</span>
                                <h3>{{ $local->Nombre_Comercio ?: 'Local sin nombre' }}</h3>
                                <p>{{ $local->Horario ?: 'Horario no informado' }}</p>
                                <span class="directory-link">Ver perfil <span>→</span></span>
                            </div>
                        </a>
                    @endforeach
                </div>
            @else
                <p class="empty-state">Todavía no hay restaurantes registrados.</p>
            @endif
        </section>
    </main>

    <footer class="site-footer"><div class="footer-inner"><div class="footer-brand"><strong>KRYMS</strong><p>Lo que necesitás,<br>más cerca.</p></div><div><h3>Descubrí</h3><a href="{{ route('home') }}#cerca">Locales</a><a href="{{ route('restaurants') }}">Restaurantes</a><a href="{{ route('home') }}#cerca">Ofertas</a></div><div><h3>Ayuda</h3><a href="#">Preguntas frecuentes</a><a href="#">Contacto</a><a href="#">Términos y privacidad</a></div><div class="footer-social"><h3>Seguinos</h3><a href="#" aria-label="Instagram">◎</a><a href="#" aria-label="Facebook">f</a><a href="#" aria-label="TikTok">♪</a></div></div><div class="footer-bottom"><span>© {{ date('Y') }} KRYMS. Todos los derechos reservados.</span><span>Hecho para moverte mejor.</span></div></footer>

    <script>
        const profileToggle = document.querySelector('[data-profile-toggle]');
        const profileMenu = document.querySelector('[data-profile-menu]');
        profileToggle?.addEventListener('click', () => profileMenu.classList.toggle('is-open'));
        document.addEventListener('click', (event) => { if (!event.target.closest('.profile-container')) profileMenu?.classList.remove('is-open'); });
    </script>
</body>
</html>
