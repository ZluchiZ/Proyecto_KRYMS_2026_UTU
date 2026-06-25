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

    <!-- Botón menú -->
    <label for="sidebar-toggle" class="menu-btn">☰ Menú</label>
    <input type="checkbox" id="sidebar-toggle">

    <!-- ICONOS DERECHA -->
    <div class="top-right-icons">
        <div class="icon-group">
            <span class="location-icon">📍</span>

            <div class="profile-container">
                <input type="checkbox" id="toggleProfile">
                <label for="toggleProfile" class="profile-icon">👤</label>

                <div class="profile-menu">
                    <a href="{{ route('login') }}">Iniciar Sesión / Registrarse</a>
                </div>
            </div>

        </div>
    </div>

    <!-- SIDEBAR -->
    <div class="sidebar">
        <a href="#">Farmacia</a>
        <a href="#">Supermercado</a>
        <a href="#">Ferretería</a>
        <a href="#">Rotisería</a>
    </div>

    <!-- CONTENIDO PRINCIPAL -->
    <div class="contenido-pagina">

        <h1>¡Bienvenidos a ElGauchoVa!</h1>

        <form method="GET">
            <label for="campo-busqueda">Buscar:</label>
            <input type="search" id="campo-busqueda" name="q" placeholder="¿Qué estás buscando?">
            <button type="submit">Buscar</button>
        </form>

        <div class="comidas-container">
            <div class="comida-card">
                <img src="{{ asset('img/comida1.jpg') }}" alt="Comida 1">
                <h3>Nombre del plato</h3>
                <p>Descripción del plato.</p>
            </div>

            <div class="comida-card">
                <img src="{{ asset('img/comida2.jpg') }}" alt="Comida 2">
                <h3>Nombre del plato</h3>
                <p>Descripción del plato.</p>
            </div>

            <div class="comida-card">
                <img src="{{ asset('img/comida3.jpg') }}" alt="Comida 3">
                <h3>Nombre del plato</h3>
                <p>Descripción del plato.</p>
            </div>
        </div>

    </div>

</main>

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