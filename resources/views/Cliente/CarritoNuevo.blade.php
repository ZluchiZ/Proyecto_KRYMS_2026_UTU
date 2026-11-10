<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="{{ asset('css/carrito.css') }}">
    <link rel="stylesheet" href="{{ asset('css/internal.css') }}">
    <title>Mi carrito | El Gaucho Va</title>
    <link rel="stylesheet" href="{{ asset('css/visual-refresh.css') }}">
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
                            @php
                                $descuentoItem = (float) ($item->Descuento_Porcentaje ?? 0);
                            @endphp
                            <p>
                                {{ $item->Cantidad }} x
                                @if ($descuentoItem > 0)
                                    <span class="old-price">$U {{ number_format($item->Precio, 2, ',', '.') }}</span>
                                    <span class="new-price">$U {{ number_format($item->Precio_Con_Descuento, 2, ',', '.') }}</span>
                                    <span class="discount-tag">-{{ number_format($descuentoItem, 0) }}%</span>
                                @else
                                    <span>$U {{ number_format($item->Precio, 2, ',', '.') }}</span>
                                @endif
                            </p>
                            <strong>$U {{ number_format($item->Subtotal, 2, ',', '.') }}</strong>
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
                <div class="cart-total-box" aria-label="Desglose del pedido">
                    <div class="cart-total-row">
                        <span>Subtotal de productos</span>
                        <strong>$U {{ number_format($subtotalCarrito, 2, ',', '.') }}</strong>
                    </div>
                    <div class="cart-total-row">
                        <span>Comisión por servicio</span>
                        <strong>$U {{ number_format($comisionServicio, 2, ',', '.') }}</strong>
                    </div>
                    <div class="cart-total-row cart-total-final">
                        <span>Total a pagar</span>
                        <strong>$U {{ number_format($totalCarrito, 2, ',', '.') }}</strong>
                    </div>
                </div>
                <div class="checkout-heading">
                    <span class="cart-eyebrow">Ultimo paso</span>
                    <h2>Confirmar pedido</h2>
                    <p>Completa los datos para recibir tu pedido.</p>
                    <p
                        class="courier-availability"
                        role="status"
                        data-live-fragment="courier-count"
                        data-live-url="{{ route('carrito.repartidores-activos') }}"
                    >
                        <strong data-courier-count>{{ $repartidoresActivos }}</strong>
                        <span data-courier-label>{{ $repartidoresActivos === 1 ? 'repartidor conectado' : 'repartidores conectados' }} ahora.</span>
                    </p>
                </div>
                <form method="POST" action="{{ route('carrito.confirm') }}">
                    @csrf
                    <label for="direccion-envio">Ubicacion de envio</label>
                    <input type="text" name="direccion_envio" id="direccion-envio" value="{{ old('direccion_envio', $direccionEntrega) }}" maxlength="500" placeholder="Calle, numero y barrio" @error('direccion_envio') aria-describedby="direccion-envio-error" @enderror required>
                    @error('direccion_envio')
                        <small class="error" id="direccion-envio-error" role="alert">{{ $message }}</small>
                    @enderror
                    <label for="telefono-contacto">Telefono de contacto</label>
                    <input type="tel" name="telefono_contacto" id="telefono-contacto" value="{{ old('telefono_contacto', $telefonoUsuario) }}" maxlength="30" placeholder="Tu telefono">
                    <label for="referencias">Referencias para la entrega</label>
                    <textarea name="referencias" id="referencias" maxlength="1000" placeholder="Apartamento, esquina u otra indicacion">{{ old('referencias') }}</textarea>
                    <label for="metodo-pago">Metodo de pago</label>
                    <select name="metodo_pago" id="metodo-pago" required>
                        <option value="efectivo" @selected(old('metodo_pago', 'efectivo') === 'efectivo')>Efectivo contra entrega</option>
                        <option value="tarjeta" @selected(old('metodo_pago') === 'tarjeta')>Tarjeta</option>
                        @if ($metodosPagoGuardados->isNotEmpty())
                            <option value="tarjeta_guardada" @selected(old('metodo_pago') === 'tarjeta_guardada')>Usar tarjeta guardada</option>
                        @endif
                    </select>
                    <fieldset id="tarjeta-simulada" class="simulated-card" hidden disabled>
                        <legend>Datos de tarjeta</legend>
                        <label for="numero-tarjeta">Numero de tarjeta</label>
                        <input type="text" name="numero_tarjeta" id="numero-tarjeta" inputmode="numeric" autocomplete="off" maxlength="19" placeholder="0000 0000 0000 0000" value="0000 0000 0000 0000">
                        @error('numero_tarjeta')<small role="alert">{{ $message }}</small>@enderror
                        <label for="vencimiento-tarjeta">Vencimiento</label>
                        <input type="text" name="vencimiento_tarjeta" id="vencimiento-tarjeta" autocomplete="off" maxlength="5" placeholder="12/30" value="12/30">
                        @error('vencimiento_tarjeta')<small role="alert">{{ $message }}</small>@enderror
                        <label for="cvv-tarjeta">Codigo de seguridad</label>
                        <input type="text" name="cvv_tarjeta" id="cvv-tarjeta" inputmode="numeric" autocomplete="off" maxlength="3" placeholder="000" value="000">
                        @error('cvv_tarjeta')<small role="alert">{{ $message }}</small>@enderror
                        <label for="guardar-metodo-pago" class="save-payment-option">
                            <input type="checkbox" name="guardar_metodo_pago" id="guardar-metodo-pago" value="1" @checked(old('guardar_metodo_pago'))>
                            Guardar método de pago
                        </label>
                    </fieldset>
                    @if ($metodosPagoGuardados->isNotEmpty())
                        <fieldset id="tarjetas-guardadas" class="simulated-card" hidden disabled>
                            <legend>Tarjetas guardadas</legend>
                            <label for="tarjeta-guardada-id">Selecciona una tarjeta</label>
                            <select name="tarjeta_guardada_id" id="tarjeta-guardada-id">
                                @foreach ($metodosPagoGuardados as $metodoGuardado)
                                    <option value="{{ $metodoGuardado->ID }}" @selected((string) old('tarjeta_guardada_id') === (string) $metodoGuardado->ID)>
                                        @if ($metodoGuardado->Ultimos_4)
                                            **** **** **** {{ $metodoGuardado->Ultimos_4 }}
                                        @else
                                            Tarjeta guardada #{{ $metodoGuardado->ID }}
                                        @endif
                                    </option>
                                @endforeach
                            </select>
                            @error('tarjeta_guardada_id')<small role="alert">{{ $message }}</small>@enderror
                        </fieldset>
                    @endif
                    <button type="submit" class="confirm">Confirmar todos los productos</button>
                </form>
            </section>
        @endif
    </main>
    @if (session('order_sent'))
    <div class="toast" role="status" aria-live="polite">Pedido enviado a {{ session('order_sent') }} local(es). Cuando todos lo finalicen, aparecerá para el repartidor como una sola entrega. <a href="{{ route('cliente.pedidos') }}">Ver historial</a></div>
    @endif
    <script>
        const metodoPago = document.getElementById('metodo-pago');
        const tarjetaSimulada = document.getElementById('tarjeta-simulada');
        const tarjetasGuardadas = document.getElementById('tarjetas-guardadas');

        function actualizarFormularioTarjeta() {
            const usarTarjeta = metodoPago.value === 'tarjeta';
            tarjetaSimulada.hidden = !usarTarjeta;
            tarjetaSimulada.disabled = !usarTarjeta;
            if (tarjetasGuardadas) {
                const usarTarjetaGuardada = metodoPago.value === 'tarjeta_guardada';
                tarjetasGuardadas.hidden = !usarTarjetaGuardada;
                tarjetasGuardadas.disabled = !usarTarjetaGuardada;
            }
        }

        if (metodoPago) {
            metodoPago.addEventListener('change', actualizarFormularioTarjeta);
            actualizarFormularioTarjeta();
        }
    </script>
</body>
</html>