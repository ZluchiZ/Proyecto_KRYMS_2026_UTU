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
                <a class="cart-back-link" href="{{ route('home') }}">&larr; Volver al inicio</a>
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
                    <input type="text" name="direccion_envio" id="direccion-envio" value="{{ old('direccion_envio') }}" maxlength="500" placeholder="Calle, numero y barrio" required>
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
        <div class="toast" role="status" aria-live="polite">
            Pedido enviado correctamente ({{ session('order_sent') }} producto(s)).
        </div>
    @endif
</body>
</html>
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
                <a class="cart-back-link" href="{{ route('home') }}">&larr; Volver al inicio</a>
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
                    <input type="text" name="direccion_envio" id="direccion-envio" value="{{ old('direccion_envio') }}" maxlength="500" placeholder="Calle, numero y barrio" required>
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
        <div class="toast" role="status" aria-live="polite">
            Pedido enviado correctamente ({{ session('order_sent') }} producto(s)).
        </div>
    @endif
</body>
</html>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="{{ asset('css/carrito.css') }}">
    <link rel="stylesheet" href="{{ asset('css/internal.css') }}">
    <title>Mi carrito</title>
</head>
<body class="internal-page internal-cart">
    @include('partials.internal-header')
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
                        <a class="cart-back-link" href="{{ route('home') }}">← Volver al inicio</a>
                    <article class="item">
                        <img src="{{ $item->Foto_Producto }}" alt="{{ $item->Nombre_Producto }}">
                    <strong class="cart-count">{{ $items->sum('Cantidad') }} producto(s)</strong>
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

                    <section class="empty-cart">
                        <span class="empty-cart-icon">🛒</span>
                        <p class="cart-eyebrow">Todavía no agregaste productos</p>
                        <h2>Tu carrito está vacío</h2>
                        <p>Explorá los locales y encontrá algo rico para pedir.</p>
                        <a class="empty-cart-action" href="{{ route('home') }}">Ver productos</a>
                    </section>
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
tion value="efectivo">Efectivo contra entrega</option>
                        <option value="tarjeta">Tarjeta contra entrega</option>
                    </select>

                    <button type="submit" class="confirm">Confirmar todos los productos</button>
                </form>
            </section>
        
    </main>

    @if (session('order_sent'))
        <div class="toast" role="status" aria-live="polite">
            Pedido enviado correctamente ({{ session('order_sent') }} producto(s)).
        </div>
    @endif

    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script>
        const mapa = L.map('mapa').setView([-32.3167, -58.0833], 13);
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', { maxZoom: 19, attribution: '&copy; OpenStreetMap contributors' }).addTo(mapa);
        const latitudInput = document.getElementById('latitud');
        const longitudInput = document.getElementById('longitud');
        let marker;
        const setLocation = (latitud, longitud) => {
            latitudInput.value = latitud.toFixed(7);
            longitudInput.value = longitud.toFixed(7);
            if (!marker) {
                marker = L.marker([latitud, longitud], { draggable: true }).addTo(mapa);
                marker.on('dragend', () => { const position = marker.getLatLng(); setLocation(position.lat, position.lng); });
            } else {
                marker.setLatLng([latitud, longitud]);
            }
            mapa.setView([latitud, longitud], Math.max(mapa.getZoom(), 15));
        };
        if (latitudInput.value && longitudInput.value) setLocation(Number(latitudInput.value), Number(longitudInput.value));
        else if (navigator.geolocation) navigator.geolocation.getCurrentPosition((position) => setLocation(position.coords.latitude, position.coords.longitude));
        mapa.on('click', (event) => setLocation(event.latlng.lat, event.latlng.lng));
    </script>
</body>
</html>
