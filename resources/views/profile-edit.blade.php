<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="{{ asset('css/internal.css') }}">
    <title>Mi perfil | El Gaucho Va</title>
    <link rel="stylesheet" href="{{ asset('css/visual-refresh.css') }}">
</head>
<body class="internal-page customer-profile-page">
    @include('partials.internal-header')
    <main class="customer-profile-main">
        <a class="profile-back-link" href="{{ $backRoute ?? ($type === 'cliente' ? route('cliente.profile') : ($type === 'comercio' ? route('dashboard.local') : route('dashboard.repartidor'))) }}">&larr; {{ $backLabel ?? 'Volver' }}</a>
        <section class="customer-profile-card">
            <div class="customer-profile-cover">
                <span class="customer-avatar">{{ strtoupper(substr($fields[0]['value'] ?: 'P', 0, 1)) }}</span>
                <div>
                    <span class="profile-kicker">Mi cuenta</span>
                    <h1>Mi perfil</h1>
                    <p>{{ $email }}</p>
                </div>
            </div>

            @if (session('success'))
                <p class="profile-form-message success" role="status">{{ session('success') }}</p>
            @endif

            @if ($errors->any())
                <div class="profile-form-message error" role="alert">
                    @foreach ($errors->all() as $error)
                        <p>{{ $error }}</p>
                    @endforeach
                </div>
            @endif

            @if (($canEdit ?? true) === true)
                <form class="profile-edit-form" method="POST" action="{{ route('profile.update') }}" enctype="multipart/form-data">
                    @csrf
                    @method('PATCH')
                    @foreach ($fields as $field)
                        <div class="profile-edit-field {{ $field['type'] === 'textarea' ? 'profile-edit-field-wide' : '' }}">
                            <label for="{{ $field['name'] }}">{{ $field['label'] }}</label>
                            @if ($field['type'] === 'textarea')
                                <textarea id="{{ $field['name'] }}" name="{{ $field['name'] }}" rows="3">{{ old($field['name'], $field['value']) }}</textarea>
                            @else
                                <input id="{{ $field['name'] }}" name="{{ $field['name'] }}" type="text" value="{{ old($field['name'], $field['value']) }}" @if ($field['required']) required @endif>
                            @endif
                        </div>
                    @endforeach

                    @if ($type === 'comercio')
                        <div class="profile-edit-field profile-edit-field-wide">
                            <label for="logo_url">URL del logo</label>
                            <input id="logo_url" name="logo_url" type="url" value="{{ old('logo_url', $profile->Logo ?? '') }}" placeholder="https://...">
                        </div>

                        <div class="profile-edit-field profile-edit-field-wide">
                            <label for="logo">Archivo del logo</label>
                            <input id="logo" name="logo" type="file" accept="image/png,image/jpeg,image/webp">
                            @if (! empty($profile->Logo))
                                <small class="profile-edit-help">Logo actual: <a href="{{ $profile->Logo }}" target="_blank" rel="noopener">Ver logo</a></small>
                            @endif
                        </div>
                    @endif

                    <p class="profile-edit-note">El correo de acceso y los identificadores de la cuenta no se pueden modificar desde esta pantalla.</p>
                    <div class="profile-edit-actions">
                        <button type="submit">Guardar cambios</button>
                        <a href="{{ $type === 'cliente' ? route('cliente.profile') : ($type === 'comercio' ? route('dashboard.local') : route('dashboard.repartidor')) }}">Cancelar</a>
                    </div>
                </form>
            @else
                <div class="profile-view-readonly">
                    @foreach ($fields as $field)
                        <div class="profile-edit-field {{ $field['type'] === 'textarea' ? 'profile-edit-field-wide' : '' }}">
                            <label>{{ $field['label'] }}</label>
                            <div class="profile-readonly-value">{{ $field['value'] ?: 'No informado' }}</div>
                        </div>
                    @endforeach
                    <p class="profile-edit-note">Vista de consulta del administrador.</p>
                </div>
            @endif

            @if ($type === 'repartidor')
                <div class="profile-vehicle-box">
                    <h2>Vehículo</h2>
                    @if ($vehicle)
                        <ul class="profile-vehicle-list">
                            <li><strong>Tipo:</strong> {{ ucfirst(str_replace('_', ' / ', $vehicle->Tipo_Vehiculo ?? 'No definido')) }}</li>
                            @if ($vehicle->Matricula)
                                <li><strong>Matrícula:</strong> {{ $vehicle->Matricula }}</li>
                            @endif
                            @if ($vehicle->Modelo)
                                <li><strong>Modelo:</strong> {{ $vehicle->Modelo }}</li>
                            @endif
                            @if ($vehicle->Color)
                                <li><strong>Color:</strong> {{ $vehicle->Color }}</li>
                            @endif
                            @if ($vehicle->Libreta_de_propiedad)
                                <li><strong>Libreta de propiedad:</strong> <a href="{{ Storage::url($vehicle->Libreta_de_propiedad) }}" target="_blank" rel="noopener">Ver archivo</a></li>
                            @endif
                            @if ($vehicle->Seguro)
                                <li><strong>Seguro:</strong> <a href="{{ Storage::url($vehicle->Seguro) }}" target="_blank" rel="noopener">Ver archivo</a></li>
                            @endif
                            @if ($vehicle->Foto)
                                <li><strong>Foto:</strong> <a href="{{ Storage::url($vehicle->Foto) }}" target="_blank" rel="noopener">Ver archivo</a></li>
                            @endif
                        </ul>
                    @else
                        <p>Aún no registraste tu vehículo.</p>
                    @endif

                    @if ($driverLicenseFile)
                        <p class="profile-doc-status"><strong>Libreta de conducir:</strong> <a href="{{ Storage::url($driverLicenseFile) }}" target="_blank" rel="noopener">Ver archivo</a></p>
                    @endif
                    @if ($identityFile)
                        <p class="profile-doc-status"><strong>Cédula de identidad:</strong> <a href="{{ Storage::url($identityFile) }}" target="_blank" rel="noopener">Ver archivo</a></p>
                    @endif

                    @if ($vehicle)
                        <form method="POST" action="{{ route('repartidor.vehiculo.delete') }}" class="profile-vehicle-delete-form" onsubmit="return confirm('¿Seguro que quieres borrar el vehículo guardado?');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="profile-vehicle-delete-button">Eliminar vehículo</button>
                        </form>
                    @endif

                    <button type="button" class="profile-vehicle-trigger" id="open-vehicle-modal">{{ $vehicle ? 'Editar vehículo' : 'Ingresar vehículo' }}</button>
                </div>

                <dialog id="vehicle-modal" class="vehicle-modal">
                    <div class="modal-content vehicle-modal-content">
                        <div class="modal-header">
                            <h2>{{ $vehicle ? 'Editar vehículo' : 'Ingresar vehículo' }}</h2>
                            <button type="button" class="close" id="close-vehicle-modal" aria-label="Cerrar">&times;</button>
                        </div>

                        <form method="POST" action="{{ route('repartidor.vehiculo.store') }}" id="vehicle-form" enctype="multipart/form-data">
                            @csrf
                            <label for="vehiculo-tipo">Tipo de vehículo</label>
                            <select id="vehiculo-tipo" name="tipo_vehiculo" required>
                                <option value="">Seleccionar</option>
                                <option value="bicicleta" {{ old('tipo_vehiculo', $vehicle->Tipo_Vehiculo ?? '') === 'bicicleta' ? 'selected' : '' }}>Bicicleta</option>
                                <option value="moto" {{ old('tipo_vehiculo', $vehicle->Tipo_Vehiculo ?? '') === 'moto' ? 'selected' : '' }}>Moto</option>
                                <option value="auto_camioneta" {{ old('tipo_vehiculo', $vehicle->Tipo_Vehiculo ?? '') === 'auto_camioneta' ? 'selected' : '' }}>Auto / Camioneta</option>
                            </select>

                            <div id="vehicle-extra-fields" class="vehicle-extra-fields">
                                <label for="vehiculo-matricula">Matrícula</label>
                                <input id="vehiculo-matricula" name="matricula" type="text" value="{{ old('matricula', $vehicle->Matricula ?? '') }}" maxlength="20">

                                <label for="vehiculo-modelo">Modelo</label>
                                <input id="vehiculo-modelo" name="modelo" type="text" value="{{ old('modelo', $vehicle->Modelo ?? '') }}" maxlength="100">

                                <label for="vehiculo-color">Color</label>
                                <input id="vehiculo-color" name="color" type="text" value="{{ old('color', $vehicle->Color ?? '') }}" maxlength="50">

                                <label for="vehiculo-foto">Foto del vehículo</label>
                                <input id="vehiculo-foto" name="foto" type="file" accept="image/*,.pdf">

                                <label for="vehiculo-libreta">Libreta de propiedad</label>
                                <input id="vehiculo-libreta" name="libreta_de_propiedad" type="file" accept="image/*,.pdf">

                                <label for="vehiculo-seguro">Seguro</label>
                                <input id="vehiculo-seguro" name="seguro" type="file" accept="image/*,.pdf">

                                <label for="vehiculo-libreta-conducir">Libreta de conducir</label>
                                <input id="vehiculo-libreta-conducir" name="libreta_de_conducir" type="file" accept="image/*,.pdf">

                                <label for="vehiculo-cedula">Cédula de identidad</label>
                                <input id="vehiculo-cedula" name="cedula_identidad" type="file" accept="image/*,.pdf">
                            </div>

                            <button type="submit">Guardar vehículo</button>
                        </form>
                    </div>
                </dialog>
            @endif
        </section>
    </main>

    @if ($type === 'repartidor')
        <style>
            .profile-view-readonly {
                display: grid;
                gap: 0.9rem;
            }
            .profile-readonly-value {
                background: #f8fafc;
                border: 1px solid #e2e8f0;
                border-radius: 10px;
                padding: 0.75rem 0.8rem;
                color: #1f2937;
            }
            .profile-vehicle-box {
                margin-top: 2rem;
                border-top: 1px solid rgba(73, 94, 98, 0.15);
                padding-top: 1.5rem;
            }
            .profile-vehicle-box h2 {
                margin: 0 0 0.75rem;
                color: #1d2d2d;
                font-size: 1.1rem;
            }
            .profile-vehicle-list {
                list-style: none;
                padding: 0;
                margin: 0 0 1rem;
                display: grid;
                gap: 0.4rem;
                color: #2f4551;
            }
            .profile-doc-status {
                margin: .25rem 0 .8rem;
                color: #2f4551;
            }
            .profile-vehicle-trigger {
                background: #0d9488;
                color: #fff;
                border: 0;
                border-radius: 10px;
                padding: 0.7rem 1rem;
                font-weight: 700;
                cursor: pointer;
            }
            .profile-vehicle-delete-form {
                margin: 0 0 0.9rem;
            }
            .profile-vehicle-delete-button {
                background: #dc2626;
                color: #fff;
                border: 0;
                border-radius: 10px;
                padding: 0.7rem 1rem;
                font-weight: 700;
                cursor: pointer;
            }
            .vehicle-modal {
                width: min(92vw, 520px);
                border: 1px solid rgba(12, 74, 110, 0.16);
                border-radius: 16px;
                padding: 0;
                box-shadow: 0 18px 45px rgba(15, 23, 42, 0.18);
            }
            .vehicle-modal::backdrop {
                background: rgba(15, 23, 42, 0.5);
                backdrop-filter: blur(3px);
            }
            .vehicle-modal-content {
                padding: 1.5rem;
            }
            .vehicle-modal form {
                display: grid;
                gap: 0.75rem;
            }
            .vehicle-modal label {
                color: #334155;
                font-size: 0.8rem;
                font-weight: 700;
            }
            .vehicle-modal select,
            .vehicle-modal input {
                width: 100%;
                border: 1px solid #dfe7ea;
                border-radius: 10px;
                padding: 0.7rem 0.8rem;
                font: inherit;
                box-sizing: border-box;
            }
            .vehicle-extra-fields {
                display: grid;
                gap: 0.75rem;
            }
            .vehicle-extra-fields.hidden {
                display: none;
            }
            .vehicle-modal button[type="submit"] {
                margin-top: 0.5rem;
                background: #0d9488;
                color: #fff;
                border: 0;
                border-radius: 10px;
                padding: 0.8rem 1rem;
                font-weight: 700;
                cursor: pointer;
            }
            .modal-header {
                display: flex;
                align-items: center;
                justify-content: space-between;
                gap: 1rem;
                margin-bottom: 1rem;
            }
            .modal-header h2 {
                margin: 0;
                color: #1d2d2d;
            }
            .close {
                border: none;
                background: transparent;
                font-size: 1.7rem;
                cursor: pointer;
                line-height: 1;
                color: #334155;
            }
        </style>

        <script>
            const vehicleModal = document.getElementById('vehicle-modal');
            const openVehicleModalButton = document.getElementById('open-vehicle-modal');
            const closeVehicleModalButton = document.getElementById('close-vehicle-modal');
            const vehicleTypeSelect = document.getElementById('vehiculo-tipo');
            const vehicleExtraFields = document.getElementById('vehicle-extra-fields');

            function toggleVehicleFields() {
                const selectedType = vehicleTypeSelect?.value;
                const shouldShowDetails = selectedType === 'moto' || selectedType === 'auto_camioneta';

                if (!vehicleExtraFields) return;
                vehicleExtraFields.classList.toggle('hidden', !shouldShowDetails);

                vehicleExtraFields.querySelectorAll('input').forEach((input) => {
                    input.disabled = !shouldShowDetails;
                });
            }

            openVehicleModalButton?.addEventListener('click', () => {
                vehicleModal.showModal();
                toggleVehicleFields();
            });
            closeVehicleModalButton?.addEventListener('click', () => vehicleModal.close());
            vehicleModal?.addEventListener('click', (event) => {
                if (event.target === vehicleModal) {
                    vehicleModal.close();
                }
            });
            vehicleTypeSelect?.addEventListener('change', toggleVehicleFields);
            toggleVehicleFields();

            @if ($errors->hasAny(['tipo_vehiculo', 'matricula', 'modelo', 'color', 'libreta_de_propiedad', 'seguro', 'foto']))
                vehicleModal?.showModal();
                toggleVehicleFields();
            @endif
        </script>
    @endif
</body>
</html>
