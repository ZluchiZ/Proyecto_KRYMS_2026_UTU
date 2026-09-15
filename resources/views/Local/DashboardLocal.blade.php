<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="{{ asset('css/dashboard-local.css') }}">
    <title>Productos del local</title>
</head>
<body>
    <main>
        <section class="profile">
            <div class="profile-info">
                <strong>{{ $nombreUsuario }}</strong>
                <span>{{ $tipoUsuario }}</span>
                <small>{{ $correoUsuario }}</small>
            </div>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit">Cerrar sesión</button>
            </form>
        </section>

        <header class="dashboard-header">
            <div>
                <h1>Productos del local</h1>
                <p>Administra los productos disponibles para pedidos.</p>
            </div>
            <button type="button" id="open-product-modal">Añadir producto</button>
        </header>

        @if ($errors->any())
            <div class="error">
                @foreach ($errors->all() as $error)
                    <div>{{ $error }}</div>
                @endforeach
            </div>
        @endif

        <section class="orders">
            <h2>Pedidos recibidos</h2>
            @if ($pedidos->isEmpty())
                <div class="empty">Todavía no hay pedidos para tus productos.</div>
            @else
                <div class="order-list">
                    @foreach ($pedidos as $pedido)
                        <article class="order">
                            <div class="order-title">
                                <span>Pedido #{{ $pedido->ID_Pedido ?? $pedido->N_Pedido }}</span>
                                <span class="order-status {{ ($pedido->Estado ?? '') === 'rechazado' ? 'rejected' : '' }}">{{ $pedido->Estado ?? 'pendiente' }}</span>
                            </div>
                            <p><strong>Producto:</strong> {{ $pedido->Nombre_Producto }}</p>
                            <p><strong>Cliente:</strong> {{ trim(($pedido->Nombre_Cliente ?? '').' '.($pedido->Apellido_Cliente ?? '')) ?: ($pedido->Correo_Cliente ?? 'Cliente') }}</p>
                            <p><strong>Cantidad:</strong> {{ $pedido->Cantidad }}</p>
                            <p><strong>Envío:</strong> {{ $pedido->Direccion_Envio ?? $pedido->Ubicacion }}</p>
                            <p><strong>Total:</strong> $U {{ number_format($pedido->Total ?? $pedido->Monto_Total, 2, ',', '.') }}</p>
                            <p><strong>Pago:</strong> {{ ucfirst($pedido->Metodo_Pago ?? $pedido->Metodo_de_pago ?? 'No indicado') }}</p>
                            @if ($pedido->Telefono_Contacto)
                                <p><strong>Teléfono:</strong> {{ $pedido->Telefono_Contacto }}</p>
                            @endif
                            @if ($pedido->Referencias)
                                <p><strong>Referencias:</strong> {{ $pedido->Referencias }}</p>
                            @endif
                            @if (($pedido->Estado ?? 'pendiente') === 'pendiente')
                                <div class="order-actions">
                                    <form method="POST" action="{{ route('pedidos.status', $pedido->ID_Pedido ?? $pedido->N_Pedido) }}" onsubmit="return confirm('¿Rechazar y eliminar este pedido?');">
                                        @csrf
                                        @method('PATCH')
                                        <input type="hidden" name="estado" value="aceptado">
                                        <button type="submit">Aceptar pedido</button>
                                    </form>
                                    <form method="POST" action="{{ route('pedidos.status', $pedido->ID_Pedido ?? $pedido->N_Pedido) }}">
                                        @csrf
                                        @method('PATCH')
                                        <input type="hidden" name="estado" value="rechazado">
                                        <button type="submit" class="reject">Rechazar pedido</button>
                                    </form>
                                </div>
                            @endif
                        </article>
                    @endforeach
                </div>
            @endif
        </section>

        @if ($productos->isEmpty())
            <div class="empty">Todavía no hay productos cargados.</div>
        @else
            <section class="products">
                @foreach ($productos as $producto)
                    <article class="product">
                        <img src="{{ $producto->Foto_Producto }}" alt="{{ $producto->Nombre_Producto }}">
                        <div class="product-content">
                            <h2>{{ $producto->Nombre_Producto }}</h2>
                            <p>{{ $producto->Categoria }}</p>
                            <p>{{ $producto->Descripcion }}</p>
                            <p class="price">$U {{ number_format($producto->Precio, 2, ',', '.') }}</p>
                            <p class="stock">Stock: {{ $producto->Stock }}</p>
                            <p class="status {{ $producto->Disponible ? '' : 'unavailable' }}">
                                {{ $producto->Disponible ? 'Disponible para pedidos' : 'No disponible para pedidos' }}
                            </p>
                            <form method="POST" action="{{ route('productos.destroy', $producto->ID_Producto) }}" onsubmit="return confirm('¿Eliminar este producto?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit">Eliminar producto</button>
                            </form>
                        </div>
                    </article>
                @endforeach
            </section>
        @endif
    </main>

    <dialog id="product-modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2>Añadir producto</h2>
                <button type="button" class="close" id="close-product-modal" aria-label="Cerrar">&times;</button>
            </div>
            <form method="POST" action="{{ route('productos.store') }}">
                @csrf
                <label for="nombre">Nombre</label>
                <input id="nombre" name="nombre" type="text" value="{{ old('nombre') }}" required maxlength="255">

                <label for="precio">Precio en $UYU</label>
                <input id="precio" name="precio" type="number" value="{{ old('precio') }}" min="0" step="0.01" required>

                <label for="categoria">Categoría</label>
                <input id="categoria" name="categoria" type="text" value="{{ old('categoria') }}" required maxlength="100">

                <label for="descripcion">Descripción</label>
                <textarea id="descripcion" name="descripcion" required maxlength="1000">{{ old('descripcion') }}</textarea>

                <label for="imagen_url">Imagen URL</label>
                <input id="imagen_url" name="imagen_url" type="url" value="{{ old('imagen_url') }}" required maxlength="2048">

                <label for="stock">Cantidad disponible</label>
                <div class="quantity-control">
                    <button type="button" class="quantity-button" data-quantity-decrease aria-label="Disminuir cantidad">-</button>
                    <input id="stock" name="stock" type="number" value="{{ old('stock', 1) }}" min="0" max="999999" step="1" required>
                    <button type="button" class="quantity-button" data-quantity-increase aria-label="Aumentar cantidad">+</button>
                </div>

                <label class="checkbox">
                    <input name="disponible" type="checkbox" value="1" {{ old('disponible', '1') ? 'checked' : '' }}>
                    Disponible para pedidos
                </label>
                <button type="submit">Guardar producto</button>
            </form>
        </div>
    </dialog>

    <script>
        const modal = document.getElementById('product-modal');
        document.getElementById('open-product-modal').addEventListener('click', () => modal.showModal());
        document.getElementById('close-product-modal').addEventListener('click', () => modal.close());
        modal.addEventListener('click', (event) => {
            if (event.target === modal) modal.close();
        });

        const stockInput = document.getElementById('stock');
        document.querySelector('[data-quantity-decrease]').addEventListener('click', () => {
            stockInput.value = Math.max(0, Number(stockInput.value || 0) - 1);
        });
        document.querySelector('[data-quantity-increase]').addEventListener('click', () => {
            stockInput.value = Math.min(999999, Number(stockInput.value || 0) + 1);
        });

        @if ($errors->any())
            modal.showModal();
        @endif
    </script>
</body>
</html>