<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css">
    <link rel="stylesheet" href="<?php echo e(asset('css/dashboard-repartidor.css')); ?>">
    <link rel="stylesheet" href="<?php echo e(asset('css/internal.css')); ?>">
    <title>Dashboard repartidor</title>
</head>
<body class="internal-page internal-dashboard">
    <?php echo $__env->make('partials.internal-header', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
    <main>
        <section class="profile">
            <div class="profile-info">
                <strong><?php echo e($nombreUsuario); ?></strong>
                <span><?php echo e($tipoUsuario); ?></span>
            </div>
            <form method="POST" action="<?php echo e(route('logout')); ?>">
                <?php echo csrf_field(); ?>
                <button type="submit">Cerrar sesión</button>
            </form>
        </section>

        <?php if(session('success')): ?>
            <div class="success"><?php echo e(session('success')); ?></div>
        <?php endif; ?>

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
                data-latitud="<?php echo e($latitudRepartidor ?? ''); ?>"
                data-longitud="<?php echo e($longitudRepartidor ?? ''); ?>"
                data-update-url="<?php echo e(route('repartidor.ubicacion')); ?>"
            ></div>
        </section>

        <section class="dashboard-panel">
            <div class="panel-header">
                <div>
                    <p class="eyebrow">Entrega</p>
                    <h1>Pedidos listos</h1>
                </div>
            </div>

            <?php if($pedidosListos->isEmpty()): ?>
                <div class="empty">No hay pedidos listos para repartir.</div>
            <?php else: ?>
                <div class="order-list">
                    <?php $__currentLoopData = $pedidosListos; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $pedido): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <article class="order-card">
                            <div class="order-header">
                                <strong>Pedido #<?php echo e($pedido->N_Pedido); ?></strong>
                                <span class="status-pill ready">Listo</span>
                            </div>

                            <div class="order-grid">
                                <p><strong>Cliente:</strong> <?php echo e(trim(($pedido->Nombre_Cliente ?? '').' '.($pedido->Apellido_Cliente ?? '')) ?: ($pedido->Correo_Cliente ?? 'Cliente')); ?></p>
                                <p><strong>Dirección:</strong> <?php echo e($pedido->Direccion_Envio); ?></p>
                                <p><strong>Total:</strong> $U <?php echo e(number_format($pedido->Total ?? $pedido->Monto_Total, 2, ',', '.')); ?></p>
                                <?php if($pedido->Telefono_Cliente): ?>
                                    <p><strong>Teléfono:</strong> <?php echo e($pedido->Telefono_Cliente); ?></p>
                                <?php endif; ?>
                            </div>
                            <div class="delivery-locals">
                                <?php $__currentLoopData = $pedido->locales; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $local): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <section>
                                        <strong><?php echo e($local->Nombre_Comercio); ?></strong>
                                        <?php $__currentLoopData = $local->productos; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $producto): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                            <p><?php echo e($producto->Nombre_Producto); ?> · <?php echo e($producto->Cantidad); ?> unidad(es)</p>
                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                    </section>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </div>

                            <div class="order-actions">
                                <form method="POST" action="<?php echo e(route('repartidor.pedidos.estado', $pedido->N_Pedido)); ?>">
                                    <?php echo csrf_field(); ?>
                                    <?php echo method_field('PATCH'); ?>
                                    <input type="hidden" name="estado" value="aceptado">
                                    <button type="submit">Aceptar entrega</button>
                                </form>
                                <form method="POST" action="<?php echo e(route('repartidor.pedidos.estado', $pedido->N_Pedido)); ?>">
                                    <?php echo csrf_field(); ?>
                                    <?php echo method_field('PATCH'); ?>
                                    <input type="hidden" name="estado" value="rechazado">
                                    <button type="submit" class="secondary">Rechazar</button>
                                </form>
                            </div>
                        </article>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </div>
            <?php endif; ?>
        </section>

        <section class="dashboard-panel my-delivery">
            <div class="panel-header">
                <div>
                    <p class="eyebrow">Mi entrega</p>
                    <h2>Pedidos asignados</h2>
                </div>
            </div>

            <?php if($miEntrega->isEmpty()): ?>
                <div class="empty">Todavía no aceptaste ninguna entrega.</div>
            <?php else: ?>
                <div class="order-list">
                    <?php $__currentLoopData = $miEntrega; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $pedido): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <article class="order-card assigned">
                            <div class="order-header">
                                <strong>Pedido #<?php echo e($pedido->N_Pedido); ?></strong>
                                <span class="status-pill assigned">Asignado</span>
                            </div>

                            <div class="order-grid">
                                <p><strong>Cliente:</strong> <?php echo e(trim(($pedido->Nombre_Cliente ?? '').' '.($pedido->Apellido_Cliente ?? '')) ?: ($pedido->Correo_Cliente ?? 'Cliente')); ?></p>
                                <p><strong>Dirección:</strong> <?php echo e($pedido->Direccion_Envio); ?></p>
                                <p><strong>Total:</strong> $U <?php echo e(number_format($pedido->Total ?? $pedido->Monto_Total, 2, ',', '.')); ?></p>
                                <?php if($pedido->Telefono_Cliente): ?>
                                    <p><strong>Teléfono:</strong> <?php echo e($pedido->Telefono_Cliente); ?></p>
                                <?php endif; ?>
                            </div>
                            <div class="delivery-locals">
                                <?php $__currentLoopData = $pedido->locales; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $local): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <section>
                                        <strong><?php echo e($local->Nombre_Comercio); ?></strong>
                                        <?php $__currentLoopData = $local->productos; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $producto): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                            <p><?php echo e($producto->Nombre_Producto); ?> · <?php echo e($producto->Cantidad); ?> unidad(es)</p>
                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                    </section>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </div>
                        </article>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </div>
            <?php endif; ?>
        </section>
    </main>
    <meta name="csrf-token" content="<?php echo e(csrf_token()); ?>">
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
</html><?php /**PATH /opt/lampp/htdocs/Proyecto_KRYMS_2026_UTU-main/resources/views/Repartidor/DashboardRepartidor.blade.php ENDPATH**/ ?>