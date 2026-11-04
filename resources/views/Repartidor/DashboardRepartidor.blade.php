<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css">
    <link rel="stylesheet" href="{{ asset('css/dashboard-repartidor.css') }}">
    <link rel="stylesheet" href="{{ asset('css/internal.css') }}">
    <title>Dashboard repartidor</title>
    <link rel="stylesheet" href="{{ asset('css/visual-refresh.css') }}">
</head>
<body class="internal-page internal-dashboard">
    @include('partials.internal-header')
    <main>
        <section class="profile">
            <div class="profile-info">
                <strong>{{ $nombreUsuario }}</strong>
                <span>{{ $tipoUsuario }}</span>
                <a class="dashboard-profile-link" href="{{ route('profile.edit') }}">Mi perfil</a>
            </div>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit">Cerrar sesión</button>
            </form>
        </section>

        @if (session('success'))
            <div class="success">{{ session('success') }}</div>
        @endif

        <section class="dashboard-panel">
            <div class="panel-header location-header">
                <div>
                    <p class="eyebrow">Ubicación</p>
                    <h2>Compartir mi ubicación</h2>
                </div>
                <div class="location-actions">
                    <button type="button" id="start-location">Iniciar ubicación</button>
                    <button type="button" id="stop-location" class="secondary" disabled>Dejar de compartir</button>
                </div>
            </div>
            <p id="location-status" class="location-status" role="status" aria-live="polite">La ubicación está desactivada.</p>
            <div
                id="repartidor-map"
                class="repartidor-map"
                data-latitud="{{ $latitudRepartidor ?? '' }}"
                data-longitud="{{ $longitudRepartidor ?? '' }}"
                data-update-url="{{ route('repartidor.ubicacion') }}"
            ></div>
        </section>

        <section class="dashboard-panel">
            <div class="panel-header">
                <div>
                    <p class="eyebrow">Entrega</p>
                    <h1>Pedidos listos</h1>
                </div>
            </div>

            @if ($pedidosListos->isEmpty())
                <div class="empty">No hay pedidos listos para repartir.</div>
            @else
                <div class="order-list">
                    @foreach ($pedidosListos as $pedido)
                        <article class="order-card" id="pedido-{{ $pedido->N_Pedido }}">
                            <div class="order-header">
                                <strong>Pedido #{{ $pedido->N_Pedido }}</strong>
                                <span class="status-pill ready">Listo</span>
                            </div>

                            <div class="order-grid">
                                <p><strong>Cliente:</strong> {{ trim(($pedido->Nombre_Cliente ?? '').' '.($pedido->Apellido_Cliente ?? '')) ?: ($pedido->Correo_Cliente ?? 'Cliente') }}</p>
                                <p><strong>Dirección:</strong> {{ $pedido->Direccion_Envio }}</p>
                                <p><strong>Total:</strong> $U {{ number_format($pedido->Total ?? $pedido->Monto_Total, 2, ',', '.') }}</p>
                                @if ($pedido->Telefono_Cliente)
                                    <p><strong>Teléfono:</strong> {{ $pedido->Telefono_Cliente }}</p>
                                @endif
                            </div>
                            <div class="delivery-locals">
                                @foreach ($pedido->locales as $local)
                                    <section>
                                        <strong>{{ $local->Nombre_Comercio }}</strong>
                                        @foreach ($local->productos as $producto)
                                            <p>{{ $producto->Nombre_Producto }} · {{ $producto->Cantidad }} unidad(es)</p>
                                        @endforeach
                                    </section>
                                @endforeach
                            </div>

                            <div class="order-actions">
                                <form method="POST" action="{{ route('repartidor.pedidos.estado', $pedido->N_Pedido) }}">
                                    @csrf
                                    @method('PATCH')
                                    <input type="hidden" name="estado" value="aceptado">
                                    <button type="submit">Aceptar entrega</button>
                                </form>
                                <form method="POST" action="{{ route('repartidor.pedidos.estado', $pedido->N_Pedido) }}">
                                    @csrf
                                    @method('PATCH')
                                    <input type="hidden" name="estado" value="rechazado">
                                    <button type="submit" class="secondary">Rechazar</button>
                                </form>
                            </div>
                        </article>
                    @endforeach
                </div>
            @endif
        </section>

        <section class="dashboard-panel my-delivery">
            <div class="panel-header">
                <div>
                    <p class="eyebrow">Mi entrega</p>
                    <h2>Pedidos asignados</h2>
                </div>
            </div>

            @if ($miEntrega->isEmpty())
                <div class="empty">Todavía no aceptaste ninguna entrega.</div>
            @else
                <div class="order-list">
                    @foreach ($miEntrega as $pedido)
                        <article class="order-card assigned">
                            <div class="order-header">
                                <strong>Pedido #{{ $pedido->N_Pedido }}</strong>
                                <span class="status-pill assigned">{{ $pedido->Estado_Entrega ? ucfirst(str_replace('_', ' ', $pedido->Estado_Entrega)) : 'Asignado' }}</span>
                            </div>

                            <div class="order-grid">
                                <p><strong>Cliente:</strong> {{ trim(($pedido->Nombre_Cliente ?? '').' '.($pedido->Apellido_Cliente ?? '')) ?: ($pedido->Correo_Cliente ?? 'Cliente') }}</p>
                                <p><strong>Dirección:</strong> {{ $pedido->Direccion_Envio }}</p>
                                <p><strong>Total:</strong> $U {{ number_format($pedido->Total ?? $pedido->Monto_Total, 2, ',', '.') }}</p>
                                @if ($pedido->Telefono_Cliente)
                                    <p><strong>Teléfono:</strong> {{ $pedido->Telefono_Cliente }}</p>
                                @endif
                            </div>
                            <div class="delivery-locals">
                                @foreach ($pedido->locales as $local)
                                    <section>
                                        <strong>{{ $local->Nombre_Comercio }}</strong>
                                        @foreach ($local->productos as $producto)
                                            <p>{{ $producto->Nombre_Producto }} · {{ $producto->Cantidad }} unidad(es)</p>
                                        @endforeach
                                    </section>
                                @endforeach
                            </div>
                            @php
                                $etapasEntrega = [
                                    'productos_recolectados' => 'El gaucho recogió todos sus productos',
                                    'en_camino' => 'El gaucho va en camino',
                                    'en_puerta' => 'El gaucho está en tu puerta',
                                ];
                                $etapaActual = $pedido->Estado_Entrega;
                                $siguienteEtapa = match ($etapaActual) {
                                    null, '' => 'productos_recolectados',
                                    'productos_recolectados' => 'en_camino',
                                    'en_camino' => 'en_puerta',
                                    default => null,
                                };
                            @endphp
                            <ol class="delivery-progress" aria-label="Progreso de entrega">
                                @foreach ($etapasEntrega as $etapa => $etiqueta)
                                    <li class="{{ $etapaActual === $etapa ? 'current' : (array_search($etapa, array_keys($etapasEntrega), true) < array_search($etapaActual, array_keys($etapasEntrega), true) ? 'complete' : '') }}">
                                        {{ $etiqueta }}
                                    </li>
                                @endforeach
                            </ol>
                            @if ($siguienteEtapa)
                                <form method="POST" action="{{ route('repartidor.pedidos.progreso', $pedido->N_Pedido) }}">
                                    @csrf
                                    @method('PATCH')
                                    <input type="hidden" name="estado_entrega" value="{{ $siguienteEtapa }}">
                                    <button type="submit">{{ $etapasEntrega[$siguienteEtapa] }}</button>
                                </form>
                            @else
                                <p role="status">El pedido está en tu puerta.</p>
                                @if ($errors->has('codigo_secreto'))
                                    <p class="error" role="alert">{{ $errors->first('codigo_secreto') }}</p>
                                @endif
                                <form method="POST" action="{{ route('repartidor.pedidos.confirmar-entrega', $pedido->N_Pedido) }}">
                                    @csrf
                                    <label for="codigo-secreto-{{ $pedido->N_Pedido }}">Código secreto proporcionado por el cliente</label>
                                    <input
                                        type="text"
                                        id="codigo-secreto-{{ $pedido->N_Pedido }}"
                                        name="codigo_secreto"
                                        value="{{ old('codigo_secreto') }}"
                                        maxlength="16"
                                        autocomplete="off"
                                        required
                                    >
                                    <button type="submit">Confirmar pedido entregado</button>
                                </form>
                            @endif
                        </article>
                    @endforeach
                </div>
            @endif
        </section>
    </main>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script>
        (() => {
            const mapElement = document.getElementById('repartidor-map');
            const statusElement = document.getElementById('location-status');
            const startButton = document.getElementById('start-location');
            const stopButton = document.getElementById('stop-location');
            const csrfToken = document.querySelector('meta[name="csrf-token"]').content;
            const initialLatitude = Number(mapElement.dataset.latitud);
            const initialLongitude = Number(mapElement.dataset.longitud);
            const hasInitialLocation = Number.isFinite(initialLatitude) && Number.isFinite(initialLongitude)
                && mapElement.dataset.latitud !== '' && mapElement.dataset.longitud !== '';
            const defaultCenter = [-32.6833, -57.6333];
            const map = L.map(mapElement).setView(hasInitialLocation ? [initialLatitude, initialLongitude] : defaultCenter, hasInitialLocation ? 15 : 14);
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                maxZoom: 19,
                attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a>'
            }).addTo(map);

            let marker = hasInitialLocation ? L.marker([initialLatitude, initialLongitude]).addTo(map) : null;
            let watchId = null;
            let lastSentAt = 0;

            const showPosition = (latitude, longitude) => {
                if (!marker) marker = L.marker([latitude, longitude]).addTo(map);
                else marker.setLatLng([latitude, longitude]);
                map.setView([latitude, longitude], Math.max(map.getZoom(), 15));
            };

            const stopSharing = () => {
                if (watchId !== null) navigator.geolocation.clearWatch(watchId);
                watchId = null;
                startButton.disabled = false;
                stopButton.disabled = true;
            };

            startButton.addEventListener('click', () => {
                if (!('geolocation' in navigator)) {
                    statusElement.textContent = 'Este navegador no permite obtener la ubicación.';
                    return;
                }
                if (!window.isSecureContext) {
                    statusElement.textContent = 'La ubicación requiere HTTPS o abrir el sitio en localhost.';
                    return;
                }

                startButton.disabled = true;
                stopButton.disabled = false;
                statusElement.textContent = 'Solicitando permiso de ubicación…';
                watchId = navigator.geolocation.watchPosition(async (position) => {
                    const { latitude, longitude } = position.coords;
                    showPosition(latitude, longitude);
                    statusElement.textContent = 'Ubicación obtenida. Compartiendo con el sistema…';

                    if (Date.now() - lastSentAt < 5000) return;
                    lastSentAt = Date.now();

                    try {
                        const response = await fetch(mapElement.dataset.updateUrl, {
                            method: 'PATCH',
                            headers: {
                                'Content-Type': 'application/json',
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': csrfToken
                            },
                            body: JSON.stringify({ latitud: latitude, longitud: longitude })
                        });
                        if (!response.ok) throw new Error('No se pudo guardar la ubicación.');
                        statusElement.textContent = 'Ubicación compartida y actualizada.';
                    } catch (error) {
                        statusElement.textContent = 'No se pudo guardar la ubicación. Comprobá tu conexión e iniciá de nuevo.';
                    }
                }, (error) => {
                    const messages = {
                        1: 'Permiso denegado. Habilitá la ubicación en el navegador para compartirla.',
                        2: 'No se pudo determinar tu ubicación. Revisá el GPS o la conexión.',
                        3: 'La solicitud de ubicación agotó el tiempo. Intentá nuevamente.'
                    };
                    statusElement.textContent = messages[error.code] || 'No se pudo obtener la ubicación.';
                    stopSharing();
                }, { enableHighAccuracy: true, maximumAge: 5000, timeout: 15000 });
            });

            stopButton.addEventListener('click', () => {
                stopSharing();
                statusElement.textContent = 'Dejaste de compartir. Se conserva la última ubicación enviada.';
            });

            if (hasInitialLocation) statusElement.textContent = 'Se muestra la última ubicación guardada.';
        })();
    </script>
</body>
</html>