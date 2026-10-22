<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="{{ asset('css/internal.css') }}">
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css">
    <title>Elegir ubicación | El Gaucho Va</title>
</head>
<body class="internal-page location-page">
    @include('partials.internal-header')

    <main class="location-page-main">
        <a class="location-back-link" href="{{ route('home') }}">← Volver al inicio</a>
        <section class="location-page-card">
            <div class="location-page-heading">
                <span class="location-modal-kicker">Zona de entrega</span>
                <h1>Elegí tu ubicación</h1>
                <p>Buscá tu dirección o mové el marcador hasta el punto exacto.</p>
            </div>

            <form method="POST" action="{{ route('cliente.location.update') }}" class="location-page-form">
                @csrf
                @method('PATCH')
                <label for="direccion-entrega">Dirección de entrega</label>
                <input type="text" name="direccion" id="direccion-entrega" value="{{ $direccionEntrega }}" maxlength="500" placeholder="Calle, número y ciudad" required>
                <div id="location-page-map" class="location-page-map"></div>
                <p class="location-status" id="location-page-status" role="status">Tocá el mapa o mové el marcador para ajustar la ubicación.</p>
                <input type="hidden" name="latitud" id="location-page-latitud" value="{{ $latitudUsuario }}">
                <input type="hidden" name="longitud" id="location-page-longitud" value="{{ $longitudUsuario }}">
                <div class="location-page-actions">
                    <button type="button" class="location-current-button" id="location-page-current">⌖ Usar mi ubicación actual</button>
                    <button type="submit">Guardar ubicación</button>
                </div>
            </form>
        </section>
    </main>

    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script>
        const map = L.map('location-page-map').setView([-32.3167, -58.0833], 13);
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', { maxZoom: 19, attribution: '&copy; OpenStreetMap contributors' }).addTo(map);
        const addressInput = document.getElementById('direccion-entrega');
        const latitudeInput = document.getElementById('location-page-latitud');
        const longitudeInput = document.getElementById('location-page-longitud');
        const status = document.getElementById('location-page-status');
        let marker;

        const setStatus = (message, error = false) => { status.textContent = message; status.classList.toggle('is-error', error); };
        const reverseGeocode = async (latitude, longitude) => {
            try {
                const response = await fetch(`https://nominatim.openstreetmap.org/reverse?format=jsonv2&lat=${latitude}&lon=${longitude}`);
                const result = await response.json();
                const address = result.display_name?.split(',').slice(0, 3).join(',');
                if (address) addressInput.value = address;
            } catch (error) {
                setStatus('Punto seleccionado. Escribí la dirección para guardarla.', true);
            }
        };
        const setLocation = (latitude, longitude, precision = null) => {
            latitudeInput.value = Number(latitude).toFixed(7);
            longitudeInput.value = Number(longitude).toFixed(7);
            if (!marker) {
                marker = L.marker([latitude, longitude], { draggable: true }).addTo(map);
                marker.on('dragend', () => { const point = marker.getLatLng(); setLocation(point.lat, point.lng); reverseGeocode(point.lat, point.lng); });
            } else {
                marker.setLatLng([latitude, longitude]);
            }
            map.setView([latitude, longitude], Math.max(map.getZoom(), 15));
            if (precision) setStatus(`Ubicación detectada con una precisión aproximada de ${Math.round(precision)} m.`);
        };

        if (latitudeInput.value && longitudeInput.value) setLocation(Number(latitudeInput.value), Number(longitudeInput.value));
        map.on('click', (event) => { setLocation(event.latlng.lat, event.latlng.lng); setStatus('Punto seleccionado. Buscando la dirección…'); reverseGeocode(event.latlng.lat, event.latlng.lng); });
        addressInput.addEventListener('input', () => setStatus(addressInput.value.trim() ? 'Dirección lista para guardar.' : 'Tocá el mapa o mové el marcador para ajustar la ubicación.'));
        document.getElementById('location-page-current').addEventListener('click', () => {
            if (!navigator.geolocation) { setStatus('Tu navegador no permite detectar la ubicación.', true); return; }
            setStatus('Buscando tu ubicación…');
            navigator.geolocation.getCurrentPosition((position) => { setLocation(position.coords.latitude, position.coords.longitude, position.coords.accuracy); reverseGeocode(position.coords.latitude, position.coords.longitude); }, () => setStatus('No pudimos detectar tu ubicación. Elegí el punto en el mapa.', true), { enableHighAccuracy: true, timeout: 10000, maximumAge: 0 });
        });
        window.setTimeout(() => map.invalidateSize(), 100);
    </script>
</body>
</html>