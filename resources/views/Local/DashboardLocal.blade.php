<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Productos del local</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 0; padding: 2rem; background: #f4f4f4; color: #222; }
        main { max-width: 1100px; margin: 0 auto; }
        .dashboard-header { display: flex; align-items: center; justify-content: space-between; gap: 1rem; margin-bottom: 2rem; }
        button { border: 0; border-radius: 6px; padding: .8rem 1rem; font: inherit; cursor: pointer; background: #222; color: #fff; }
        .products { display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 1rem; }
        .product { background: #fff; border-radius: 8px; overflow: hidden; box-shadow: 0 3px 12px #0001; }
        .product img { width: 100%; height: 150px; object-fit: cover; background: #ddd; }
        .product-content { padding: 1rem; }
        .product h2 { margin: 0 0 .4rem; font-size: 1.15rem; }
        .price { font-weight: bold; }
        .status { color: #18733c; font-size: .9rem; }
        .status.unavailable { color: #a33; }
        .empty { background: #fff; padding: 2rem; text-align: center; border-radius: 8px; }
        dialog { width: min(92vw, 520px); border: 0; border-radius: 8px; padding: 0; box-shadow: 0 12px 40px #0005; }
        dialog::backdrop { background: #0008; }
        .modal-content { padding: 1.5rem; }
        .modal-header { display: flex; justify-content: space-between; align-items: center; }
        .modal-header h2 { margin: 0 0 1rem; }
        .close { background: transparent; color: #222; padding: .25rem .5rem; font-size: 1.4rem; }
        form { display: grid; gap: .8rem; }
        label { font-weight: bold; }
        input, textarea { width: 100%; box-sizing: border-box; padding: .7rem; border: 1px solid #ccc; border-radius: 5px; font: inherit; }
        textarea { min-height: 90px; resize: vertical; }
        .checkbox { display: flex; align-items: center; gap: .5rem; font-weight: normal; }
        .checkbox input { width: auto; }
        .error { color: #a00; margin-bottom: 1rem; }
        @media (max-width: 540px) { body { padding: 1rem; } .dashboard-header { align-items: flex-start; flex-direction: column; } }
    </style>
</head>
<body>
    <main>
        <header class="dashboard-header">
            <div>
                <h1>Productos del local</h1>
                <p>Administra los productos disponibles para pedidos.</p>
            </div>
            <button type="button" id="open-product-modal">Añadir producto</button>
        </header>

        @if (session('success'))
            <p>{{ session('success') }}</p>
        @endif

        @if ($errors->any())
            <div class="error">
                @foreach ($errors->all() as $error)
                    <div>{{ $error }}</div>
                @endforeach
            </div>
        @endif

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
                            <p class="status {{ $producto->Disponible ? '' : 'unavailable' }}">
                                {{ $producto->Disponible ? 'Disponible para pedidos' : 'No disponible para pedidos' }}
                            </p>
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
        @if ($errors->any())
            modal.showModal();
        @endif
    </script>
</body>
</html>