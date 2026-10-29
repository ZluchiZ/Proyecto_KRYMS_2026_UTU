<header class="internal-header">
    <a class="internal-brand" href="{{ route('home') }}" aria-label="El Gaucho Va, volver al inicio">
        <img src="{{ asset('img/LogoGaucho_png.png') }}" alt="El Gaucho Va">
    </a>
<<<<<<< HEAD
    <a class="internal-home-link" href="{{ route('home') }}">Volver al inicio</a>
=======
    <div class="internal-header-actions">
        @include('partials.notifications-menu')
        <a class="internal-home-link" href="{{ route('home') }}">Volver al inicio</a>
    </div>
>>>>>>> 08917fc (Sistema de pedidos + Notificaciones + Ajustes en el repartidor (opciones para cambiar el estado del pedido e ingresar codigo de confirmacion de entrega))
</header>