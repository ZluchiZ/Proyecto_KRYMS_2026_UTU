<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="{{ asset('css/styles.css') }}">
    <title>ElGauchoVa</title>
</head>

<body>

<main class="container">

    <!-- Sección: botón de menú hamburguesa para abrir/cerrar el sidebar -->
    <label for="sidebar-toggle" class="menu-btn">☰ Menú</label>
    <input type="checkbox" id="sidebar-toggle">

    <!-- Sección: barra superior con íconos de ubicación y perfil -->
    <div class="top-right-icons">
        <div class="icon-group">
            <span class="location-icon">📍</span>

            <div class="profile-container">
                <input type="checkbox" id="toggleProfile">
                <label for="toggleProfile" class="profile-icon">👤</label>

                <div class="profile-menu">
                    @if ($nombreUsuario)
                        <strong>{{ $nombreUsuario }}</strong>
                        <span>{{ $tipoUsuario }}</span>
                        <small>{{ $correoUsuario }}</small>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit">Cerrar sesión</button>
                        </form>
                    @else
                        <a href="{{ route('login') }}">Iniciar Sesión / Registrarse</a>
                    @endif
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
            @forelse ($productos as $producto)
                <article class="comida-card">
                    <img src="{{ $producto->Foto_Producto }}" alt="{{ $producto->Nombre_Producto }}">
                    <h3>{{ $producto->Nombre_Producto }}</h3>
                    <p>{{ $producto->Categoria }}</p>
                    <p>{{ $producto->Descripcion }}</p>
                    <strong>$U {{ number_format($producto->Precio, 2, ',', '.') }}</strong>
                </article>
            @empty
                <p>No hay productos disponibles en este momento.</p>
            @endforelse
        </div>

    </div>

</main>

<!-- Sección: pie de página con información de la empresa -->
<footer>
    <div class="nosotros">
        <p><strong>Sobre nosotros</strong></p>
        <p>
            Lorem ipsum dolor sit amet, consectetur adipiscing elit...
        </p>
    </div>
</footer>

</body>
</html>