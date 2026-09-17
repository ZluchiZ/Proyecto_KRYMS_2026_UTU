<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="{{ asset('css/carrito.css') }}">
    <link rel="stylesheet" href="{{ asset('css/internal.css') }}">
    <title>Mi carrito | El Gaucho Va</title>
</head>
<body class="internal-page internal-cart">
    @include('partials.internal-header')
    <main>
        <header class="header">
            <div>
                <p class="cart-eyebrow">Tu pedido</p>
                <h1>Mi carrito</h1>
            </div>
            <strong class="cart-count">{{ $items->sum('Cantidad') }} producto(s)</strong>
        </header>

        @if (session('success'))
            <p class="success" role="status">{{ session('success') }}</p>
        @endif
        @if ($errors->any())
            <div class="error" role="alert">
                @foreach ($errors->all() as $error)
                    <p>{{ $error }}</p>
                @endforeach
            </div>
        @endif

        @if ($items->isEmpty())
            <section class="empty-cart">
                <span class="empty-cart-icon">&#128722;</span>
                <p class="cart-eyebrow">Todavia no agregaste productos</p>
                <h2>Tu carrito esta vacio</h2>
                <p>Explora los locales y encontra algo rico para pedir.</p>
                <a class="empty-cart-action" href="{{ route('home') }}">Ver productos</a>
            </section>
        @else
            <section class="items" aria-label="Productos en el carrito">
                @foreach ($items as $item)
                    <article class="item">
                        <img src="{{ $item->Foto_Producto }}" alt="{{ $item->Nombre_Producto }}">
                        <div class="item-details">
                            <span class="item-store">{{ $item->Nombre_Comercio ?? 'Local no disponible' }}</span>
                            <h2>{{ $item->Nombre_Producto }}</h2>
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
                <div class="checkout-heading">
                    <span class="cart-eyebrow">Ultimo paso</span>
                    <h2>Confirmar pedido</h2>
                    <p>Completa los datos para recibir tu pedido.</p>
                </div>
                <form method="POST" action="{{ route('carrito.confirm') }}">
                    @csrf
                    <label for="direccion-envio">Ubicacion de envio</label>
                    <input type="text" name="direccion_envio" id="direccion-envio" value="{{ old('direccion_envio', $direccionEntrega) }}" maxlength="500" placeholder="Calle, numero y barrio" required>
                    <label for="telefono-contacto">Telefono de contacto</label>
                    <input type="tel" name="telefono_contacto" id="telefono-contacto" value="{{ old('telefono_contacto', $telefonoUsuario) }}" maxlength="30" placeholder="Tu telefono">
                    <label for="referencias">Referencias para la entrega</label>
                    <textarea name="referencias" id="referencias" maxlength="1000" placeholder="Apartamento, esquina u otra indicacion">{{ old('referencias') }}</textarea>
                    <label for="metodo-pago">Metodo de pago</label>
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
        <div class="toast" role="status" aria-live="polite">Pedido enviado correctamente ({{ session('order_sent') }} producto(s)).</div>
    @endif
</body>
</html>