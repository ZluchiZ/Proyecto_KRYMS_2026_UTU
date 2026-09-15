<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="{{ asset('css/styles.css') }}">
    <title>ElGauchoVa</title>
    <style>
        .purchase-trigger { width: calc(100% - 30px); margin: 0 15px 15px; }
        .purchase-modal { width: min(92vw, 520px); border: 0; border-radius: 8px; padding: 0; box-shadow: 0 12px 40px #0005; }
        .purchase-modal::backdrop { background: #0008; }
        .purchase-content { padding: 1.5rem; }
        .purchase-header { display: flex; justify-content: space-between; align-items: center; gap: 1rem; }
        .purchase-header h2 { margin: 0 0 1rem; }
        .purchase-close { width: auto !important; margin: 0 !important; padding: .25rem .5rem !important; background: transparent !important; color: #222 !important; font-size: 1.4rem !important; }
        .purchase-details { margin-bottom: 1rem; padding: .8rem; background: #f4f4f4; border-radius: 6px; }
        .purchase-details p { margin: .2rem 0; }
        .purchase-form { display: grid; gap: .75rem; }
        .purchase-form label { font-weight: bold; }
        .purchase-form input, .purchase-form textarea, .purchase-form select { width: 100%; box-sizing: border-box; padding: .7rem; border: 1px solid #ccc; border-radius: 5px; font: inherit; }
        .purchase-form textarea { min-height: 80px; resize: vertical; }
    </style>
</head>

<body>

<main class="container">

@if (session('success'))
    <p class="alert" style="color: #18733c;">{{ session('success') }}</p>
@endif

    <!-- Sección: botón de menú hamburguesa para abrir/cerrar el sidebar -->
    <label for="sidebar-toggle" class="menu-btn">☰ Menú</label>
    <input type="checkbox" id="sidebar-toggle">

    <!-- Sección: barra superior con íconos de ubicación y perfil -->
    <div class="top-right-icons">
        <div class="icon-group">
            <span class="location-icon">📍</span>

            @if ($esClienteRegistrado)
                <a class="cart-link" href="{{ route('carrito') }}" aria-label="Ver carrito">🛒 {{ $cantidadCarrito }}</a>
            @endif

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
                    <p>Stock: {{ $producto->Stock }}</p>
                    <p>Local: {{ $producto->Nombre_Comercio ?? 'Local no disponible' }}</p>
                    @if ($esClienteRegistrado)
                        <button
                            type="button"
                            class="purchase-trigger"
                            data-product-id="{{ $producto->ID_Producto }}"
                            data-product-name="{{ $producto->Nombre_Producto }}"
                            data-product-price="$U {{ number_format($producto->Precio, 2, ',', '.') }}"
                            data-product-local="{{ $producto->Nombre_Comercio ?? 'Local no disponible' }}"
                            data-product-stock="{{ $producto->Stock }}"
                            {{ $producto->Stock < 1 ? 'disabled' : '' }}
                        >
                            {{ $producto->Stock < 1 ? 'Sin stock' : 'Comprar' }}
                        </button>
                    @else
                        <a class="purchase-trigger" href="{{ route('login') }}">Inicia sesión para comprar</a>
                    @endif
                </article>
            @empty
                <p>No hay productos disponibles en este momento.</p>
            @endforelse
        </div>

    </div>

</main>

@if ($esClienteRegistrado)
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
                <p>Stock disponible: <span id="purchase-product-stock"></span></p>
            </div>

            <form method="POST" action="{{ route('carrito.add') }}" class="purchase-form">
                @csrf
                <input type="hidden" name="producto_id" id="purchase-product-id">

                <label for="purchase-quantity">Cantidad</label>
                <input type="number" name="cantidad" id="purchase-quantity" min="1" required>

                <button type="submit">Agregar al carrito</button>
            </form>
        </div>
    </dialog>
@endif

<!-- Sección: pie de página con información de la empresa -->
<footer>
    <div class="nosotros">
        <p><strong>Sobre nosotros</strong></p>
        <p>
            Lorem ipsum dolor sit amet, consectetur adipiscing elit...
        </p>
    </div>
</footer>

@if ($esClienteRegistrado)
    <script>
        const purchaseModal = document.getElementById('purchase-modal');
        const productIdInput = document.getElementById('purchase-product-id');
        const quantityInput = document.getElementById('purchase-quantity');

        document.querySelectorAll('.purchase-trigger[data-product-id]').forEach((trigger) => {
            trigger.addEventListener('click', () => {
                const stock = Number(trigger.dataset.productStock);
                productIdInput.value = trigger.dataset.productId;
                document.getElementById('purchase-product-name').textContent = trigger.dataset.productName;
                document.getElementById('purchase-product-local').textContent = `Local: ${trigger.dataset.productLocal}`;
                document.getElementById('purchase-product-price').textContent = trigger.dataset.productPrice;
                document.getElementById('purchase-product-stock').textContent = stock;
                quantityInput.max = stock;
                quantityInput.value = 1;
                purchaseModal.showModal();
            });
        });

        document.getElementById('close-purchase-modal').addEventListener('click', () => purchaseModal.close());
        purchaseModal.addEventListener('click', (event) => {
            if (event.target === purchaseModal) purchaseModal.close();
        });
    </script>
@endif

</body>
</html>