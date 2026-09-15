<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mi carrito</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 0; padding: 2rem; background: #f4f4f4; color: #222; }
        main { max-width: 900px; margin: 0 auto; }
        a { color: #222; }
        .header { display: flex; justify-content: space-between; align-items: center; gap: 1rem; margin-bottom: 1.5rem; }
        .items { display: grid; gap: 1rem; }
        .item, .checkout { background: #fff; border-radius: 8px; padding: 1rem; box-shadow: 0 3px 12px #0001; }
        .item { display: grid; grid-template-columns: 100px 1fr auto; gap: 1rem; align-items: center; }
        .item img { width: 100px; height: 80px; object-fit: cover; border-radius: 5px; background: #ddd; }
        .item h2, .item p { margin: .2rem 0; }
        .remove { border: 0; background: #a33; color: #fff; padding: .6rem .8rem; border-radius: 5px; cursor: pointer; }
        .checkout { margin-top: 1.5rem; }
        .checkout h2 { margin-top: 0; }
        form { display: grid; gap: .8rem; }
        label { font-weight: bold; }
        input, textarea, select, .confirm { width: 100%; box-sizing: border-box; padding: .7rem; border: 1px solid #ccc; border-radius: 5px; font: inherit; }
        textarea { min-height: 80px; resize: vertical; }
        .confirm { border: 0; background: #222; color: #fff; cursor: pointer; }
        .success { color: #18733c; }
        .error { color: #a00; }
        .empty { background: #fff; padding: 2rem; text-align: center; border-radius: 8px; }
        .toast { position: fixed; right: 1rem; bottom: 1rem; z-index: 10; max-width: min(90vw, 360px); padding: 1rem 1.2rem; background: #18733c; color: #fff; border-radius: 8px; box-shadow: 0 6px 20px #0004; animation: toast-in .25s ease-out, toast-out .4s ease-in 4.6s forwards; }
        @keyframes toast-in { from { opacity: 0; transform: translateY(1rem); } to { opacity: 1; transform: translateY(0); } }
        @keyframes toast-out { to { opacity: 0; transform: translateY(1rem); visibility: hidden; } }
        @media (max-width: 600px) { body { padding: 1rem; } .header { align-items: flex-start; flex-direction: column; } .item { grid-template-columns: 70px 1fr; } .item img { width: 70px; height: 70px; } .item form { grid-column: 1 / -1; } }
    </style>
</head>
<body>
    <main>
        <header class="header">
            <div>
                <a href="{{ route('home') }}">Volver al home</a>
                <h1>Mi carrito</h1>
            </div>
            <strong>{{ $items->sum('Cantidad') }} producto(s)</strong>
        </header>

        @if (session('success'))
            <p class="success">{{ session('success') }}</p>
        @endif
        @if ($errors->any())
            <div class="error">
                @foreach ($errors->all() as $error)
                    <p>{{ $error }}</p>
                @endforeach
            </div>
        @endif

        @if ($items->isEmpty())
            <div class="empty">
                <p>Tu carrito está vacío.</p>
                <a href="{{ route('home') }}">Ver productos</a>
            </div>
        @else
            <section class="items">
                @foreach ($items as $item)
                    <article class="item">
                        <img src="{{ $item->Foto_Producto }}" alt="{{ $item->Nombre_Producto }}">
                        <div>
                            <h2>{{ $item->Nombre_Producto }}</h2>
                            <p>Local: {{ $item->Nombre_Comercio ?? 'Local no disponible' }}</p>
                            <p>{{ $item->Cantidad }} x $U {{ number_format($item->Precio, 2, ',', '.') }}</p>
                            <strong>$U {{ number_format($item->Cantidad * $item->Precio, 2, ',', '.') }}</strong>
                        </div>
                        <form method="POST" action="{{ route('carrito.remove', $item->ID_Carrito) }}">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="remove">Quitar</button>
                        </form>
                    </article>
                @endforeach
            </section>

            <section class="checkout">
                <h2>Confirmar pedido</h2>
                <p>Los productos se enviarán a los locales recién después de confirmar.</p>
                <form method="POST" action="{{ route('carrito.confirm') }}">
                    @csrf
                    <label for="direccion-envio">Ubicación de envío</label>
                    <input type="text" name="direccion_envio" id="direccion-envio" value="{{ old('direccion_envio') }}" maxlength="500" placeholder="Calle, número y barrio" required>

                    <label for="telefono-contacto">Teléfono de contacto</label>
                    <input type="tel" name="telefono_contacto" id="telefono-contacto" value="{{ old('telefono_contacto', $telefonoUsuario) }}" maxlength="30" placeholder="Tu teléfono">

                    <label for="referencias">Referencias para la entrega</label>
                    <textarea name="referencias" id="referencias" maxlength="1000" placeholder="Apartamento, esquina u otra indicación">{{ old('referencias') }}</textarea>

                    <label for="metodo-pago">Método de pago</label>
                    <select name="metodo_pago" id="metodo-pago" required>
                        <option value="efectivo">Efectivo contra entrega</option>
                        <option value="tarjeta">Tarjeta contra entrega</option>
                    </select>

                    <button type="submit" class="confirm">Confirmar todos los productos</button>
                </form>
            </section>
        @endif
    </main>

    @if (session('order_sent'))
        <div class="toast" role="status" aria-live="polite">
            Pedido enviado correctamente ({{ session('order_sent') }} producto(s)).
        </div>
    @endif
</body>
</html>
