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
            <div class="customer-profile-grid">
                <div class="profile-info-block"><span>Correo electrónico</span><strong>{{ $cliente->Email_Usuario ?? 'No informado' }}</strong></div>
                <div class="profile-info-block"><span>Teléfono</span><strong>{{ $cliente->{'Teléfono'} ?? 'No informado' }}</strong></div>
                <div class="profile-info-block profile-info-wide"><span>Dirección guardada</span><strong>{{ $cliente->direccion ?? $cliente->Direccion_Entrega ?? 'Todavía no agregaste una dirección' }}</strong></div>
            </div>
            <div class="customer-profile-actions">
                <a class="profile-primary-action" href="{{ route('cliente.location.form') }}">Editar ubicación</a>
                <a class="profile-secondary-action" href="{{ route('carrito') }}">Ver mi carrito</a>
                <form method="POST" action="{{ route('logout') }}">@csrf<button type="submit">Cerrar sesión</button></form>
            </div>
        </section>
    </main>
</body>
</html>