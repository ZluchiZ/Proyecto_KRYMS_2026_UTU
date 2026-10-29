<header class="internal-header">
    <a class="internal-brand" href="{{ session('tipo_usuario') === 'repartidor' ? route('dashboard.repartidor') : route('home') }}" aria-label="El Gaucho Va">
        <img src="{{ asset('img/LogoGaucho_png.png') }}" alt="El Gaucho Va">
    </a>
    <div class="internal-header-actions">
        @include('partials.notifications-menu')
        @if (session('tipo_usuario') !== 'repartidor')
            <a class="internal-home-link" href="{{ route('home') }}">Volver al inicio</a>
        @endif
    </div>
</header>