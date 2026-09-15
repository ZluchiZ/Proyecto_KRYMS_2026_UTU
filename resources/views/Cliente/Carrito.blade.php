<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="{{ asset('css/carrito.css') }}">
    <title>Mi carrito</title>
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
