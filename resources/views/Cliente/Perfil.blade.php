<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="{{ asset('css/internal.css') }}">
    <title>Mi perfil | El Gaucho Va</title>
</head>
<body class="internal-page customer-profile-page">
    @include('partials.internal-header')
    <main class="customer-profile-main">
        <a class="profile-back-link" href="{{ route('home') }}">&larr; Volver al inicio</a>
        <section class="customer-profile-card">
            <div class="customer-profile-cover">
                <span class="customer-avatar">{{ strtoupper(substr($cliente->Nombre ?? 'C', 0, 1)) }}</span>
                <div>
                    <span class="profile-kicker">Cuenta personal</span>
                    <h1>{{ $cliente->Nombre ?? 'Mi perfil' }} {{ $cliente->Apellido ?? '' }}</h1>
                    <p>{{ $cliente->Email_Usuario ?? 'Cliente de El Gaucho Va' }}</p>
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

            <form class="profile-edit-form" method="POST" action="{{ route('profile.update') }}">
                @csrf
                @method('PATCH')
                <div class="profile-edit-field">
                    <label for="nombre">Nombre</label>
                    <input id="nombre" name="nombre" type="text" value="{{ old('nombre', $cliente->Nombre ?? '') }}" required>
                </div>
                <div class="profile-edit-field">
                    <label for="apellido">Apellido</label>
                    <input id="apellido" name="apellido" type="text" value="{{ old('apellido', $cliente->Apellido ?? '') }}" required>
                </div>
                <div class="profile-edit-field">
                    <label for="telefono">Teléfono</label>
                    <input id="telefono" name="telefono" type="text" value="{{ old('telefono', $cliente->{'Teléfono'} ?? '') }}" required>
                </div>
                <div class="profile-edit-field">
                    <label for="correo">Correo electrónico</label>
                    <input id="correo" type="email" value="{{ $cliente->Email_Usuario ?? '' }}" disabled>
                </div>
                <div class="profile-edit-field profile-edit-field-wide">
                    <label for="direccion">Dirección de entrega</label>
                    <textarea id="direccion" name="direccion" rows="3">{{ old('direccion', $cliente->direccion ?? $cliente->Direccion_Entrega ?? '') }}</textarea>
                </div>
                <p class="profile-edit-note">El correo de acceso y la cédula no se pueden modificar.</p>
                <div class="profile-edit-actions">
                    <button type="submit">Guardar cambios</button>
                </div>
            </form>

            <div class="customer-profile-actions">
                <a class="profile-primary-action" href="{{ route('cliente.location.form') }}">Editar ubicación</a>
                <a class="profile-secondary-action" href="{{ route('carrito') }}">Ver mi carrito</a>
                <a class="profile-secondary-action" href="{{ route('cliente.pedidos') }}">Historial de pedidos</a>
                <a class="profile-secondary-action" href="{{ route('cliente.reportes.index') }}">Mis reportes</a>
                <form method="POST" action="{{ route('logout') }}">@csrf<button type="submit">Cerrar sesión</button></form>
            </div>
        </section>
    </main>
</body>
</html>