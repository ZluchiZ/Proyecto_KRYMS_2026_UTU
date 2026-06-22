<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="{{ asset('css/styles.css') }}">
   
    <title>ElGauchoVa</title>
</head>
<body>

<label for="sidebar-toggle" class="menu-btn">☰ Menú</label>
<input type="checkbox" id="sidebar-toggle">

<div class="top-right-icons">
    <div class="icon-group">
        <span class="location-icon">📍</span>
        <div class="profile-container">
            <input type="checkbox" id="toggleProfile">
            <label for="toggleProfile" class="profile-icon">
                👤
            </label>
            <div class="profile-menu">
                <a href="{{ route('login') }}">Iniciar Sesión / Registrarse</a>
            </div>
        </div>
    </div>
</div>

<div class="sidebar">
    <a href="#">Farmacia</a>
    <a href="#">Supermercado</a>
    <a href="#">Ferreteria</a>
    <a href="#">Rotiseria</a>

    </div>
</div>

<div class="contenido-pagina">
<h1>¡Bienvenidos a ElGauchoVa!</h1>
    <form for="/buscar" method="GET">
    <label for="campo-busqueda"Buscar:></label>
    <input type="search" id="campo-busqueda" name="q" placeholder="¿Qué estás buscando?">
    <button type="submit">buscar</button>
    </form>

    <div class="comidas-container">
        <div class="comida-card">
            <img src="img/comida1.jpg" alt="Comida 1">
            <h3>Nombre del plato</h3>
            <p>Descripción del plato.</p>
        </div>
        <div class="comida-card">
            <img src="img/comida2.jpg" alt="Comida 2">
            <h3>Nombre del plato</h3>
            <p>Descripción del plato.</p>
        </div>
        <div class="comida-card">
            <img src="img/comida3.jpg" alt="Comida 3">
            <h3>Nombre del plato</h3>
            <p>Descripción del plato.</p>
        </div>
       
    </div>
</div>

<div class="Nosotros">
    <div class="Sobre-Nosotros">
        <p><strong>Sobre nosotros</strong></p>
        <h4>Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.
             Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat.
              Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur.
               Excepteur sint occaecat cupidatat non proident, sunt in culpa qui officia deserunt mollit anim id est laborum.</h3>
    </div>

</div>

    
</body>
</html>