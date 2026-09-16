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

        @if (session('success'))
            <div class="success">{{ session('success') }}</div>
        @endif

        @foreach ([
            ['titulo' => 'Pedidos pendientes', 'items' => $pedidos->where('Estado_Subpedido', 'pendiente'), 'acciones' => true],
            ['titulo' => 'Pedidos aceptados', 'items' => $pedidos->where('Estado_Subpedido', 'aceptado'), 'acciones' => false],
        ] as $seccion)
            <section class="orders">
                <h2>{{ $seccion['titulo'] }}</h2>
                @if ($seccion['items']->isEmpty())
                    <div class="empty">No hay pedidos en esta sección.</div>
                @else
                    <div class="order-list">
                    @foreach ($seccion['items'] as $pedido)
                        <article class="order">
                            <div class="order-title">
                                <span>Pedido #{{ $pedido->ID_Pedido ?? $pedido->N_Pedido }}</span>
                                <span class="order-status {{ ($pedido->Estado_Subpedido ?? '') === 'rechazado' ? 'rejected' : '' }}">{{ $pedido->Estado_Subpedido ?? 'pendiente' }}</span>
                            </div>
                            <p><strong>Producto:</strong> {{ $pedido->Nombre_Producto }}</p>
                            <p><strong>Cliente:</strong> {{ trim(($pedido->Nombre_Cliente ?? '').' '.($pedido->Apellido_Cliente ?? '')) ?: ($pedido->Correo_Cliente ?? 'Cliente') }}</p>
                            <p><strong>Cantidad:</strong> {{ $pedido->Cantidad }}</p>
                            <p><strong>Envío:</strong> {{ $pedido->Direccion_Envio ?? $pedido->Ubicacion }}</p>
                            <p><strong>Total:</strong> $U {{ number_format($pedido->Total ?? $pedido->Monto_Total, 2, ',', '.') }}</p>
                            <p><strong>Pago:</strong> {{ ucfirst($pedido->Metodo_Pago ?? $pedido->Metodo_de_pago ?? 'No indicado') }}</p>
                            @if ($pedido->Telefono_Cliente)
                                <p><strong>Teléfono:</strong> {{ $pedido->Telefono_Cliente }}</p>
                            @endif
                            @if ($seccion['acciones'])
                                <div class="order-actions">
                                    <form method="POST" action="{{ route('pedidos.status', $pedido->N_Subpedido) }}" onsubmit="return confirm('¿Rechazar y eliminar este pedido?');">
                                        @csrf
                                        @method('PATCH')
                                        <input type="hidden" name="estado" value="aceptado">
                                        <button type="submit">Aceptar pedido</button>
                                    </form>
                                    <form method="POST" action="{{ route('pedidos.status', $pedido->N_Subpedido) }}">
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
        @endforeach

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
                            <p class="price">$U {{ number_format($producto->Precio, 2, ',', '.') }}</p>
                            <p class="status {{ $producto->Disponible ? '' : 'unavailable' }}">
                                {{ $producto->Disponible ? 'Disponible para pedidos' : 'No disponible para pedidos' }}
                            </p>
                            <div class="product-actions">
                                <button type="button" class="edit-product" data-edit-product="{{ $producto->ID_Producto }}">Editar producto</button>
                                <form method="POST" action="{{ route('productos.destroy', $producto->ID_Producto) }}" onsubmit="return confirm('¿Eliminar este producto?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit">Eliminar producto</button>
                                </form>
                            </div>

                            <div class="product-edit-panel" data-product-panel="{{ $producto->ID_Producto }}" hidden>
                                <form class="product-edit-form" data-product-form="{{ $producto->ID_Producto }}" method="POST" action="{{ route('productos.update', $producto->ID_Producto) }}">
                                    @csrf
                                    @method('PATCH')
                                    <label for="precio-{{ $producto->ID_Producto }}">Precio en $UYU</label>
                                    <input id="precio-{{ $producto->ID_Producto }}" name="precio" type="number" value="{{ $producto->Precio }}" min="0" max="99999999.99" step="0.01" required>
                                    <label class="checkbox">
                                        <input name="disponible" type="hidden" value="0">
                                        <input name="disponible" type="checkbox" value="1" {{ $producto->Disponible ? 'checked' : '' }}>
                                        Disponible para pedidos
                                    </label>
                                    <button type="submit">Guardar cambios</button>
                                </form>
                            </div>
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
                <input id="precio" name="precio" type="number" value="{{ old('precio') }}" min="0" max="99999999.99" step="0.01" required>

                <label for="categoria">Categoría</label>
                <input id="categoria" name="categoria" type="text" value="{{ old('categoria') }}" required maxlength="100">

                <label for="descripcion">Descripción</label>
                <textarea id="descripcion" name="descripcion" required maxlength="1000">{{ old('descripcion') }}</textarea>

                <label for="imagen_url">Imagen URL</label>
                <input id="imagen_url" name="imagen_url" type="url" value="{{ old('imagen_url') }}" required maxlength="2048">

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

        document.querySelectorAll('[data-edit-product]').forEach((button) => {
            button.addEventListener('click', () => {
                const panel = document.querySelector(`[data-product-panel="${button.dataset.editProduct}"]`);

                document.querySelectorAll('.product-edit-panel').forEach((item) => {
                    if (item !== panel) {
                        item.hidden = true;
                        item.classList.remove('visible');
                    }
                });

                const shouldShow = panel.hidden;
                panel.hidden = !shouldShow;
                panel.classList.toggle('visible', shouldShow);
                button.textContent = shouldShow ? 'Cancelar edición' : 'Editar producto';
            });
        });

        @if ($errors->any())
            modal.showModal();
        @endif
    </script>
</body>
</html>