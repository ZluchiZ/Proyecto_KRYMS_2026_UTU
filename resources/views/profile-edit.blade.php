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
        <a class="profile-back-link" href="{{ $type === 'cliente' ? route('cliente.profile') : ($type === 'comercio' ? route('dashboard.local') : route('dashboard.repartidor')) }}">&larr; Volver</a>
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

            <form class="profile-edit-form" method="POST" action="{{ route('profile.update') }}">
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
                <p class="profile-edit-note">El correo de acceso y los identificadores de la cuenta no se pueden modificar desde esta pantalla.</p>
                <div class="profile-edit-actions">
                    <button type="submit">Guardar cambios</button>
                    <a href="{{ $type === 'cliente' ? route('cliente.profile') : ($type === 'comercio' ? route('dashboard.local') : route('dashboard.repartidor')) }}">Cancelar</a>
                </div>
            </form>
        </section>
    </main>
</body>
</html>
