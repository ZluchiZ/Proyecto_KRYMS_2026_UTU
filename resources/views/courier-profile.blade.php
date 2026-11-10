<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ trim(($repartidor->Nombre ?? '').' '.($repartidor->Apellido ?? '')) ?: 'Repartidor' }} | El Gaucho Va</title>
    <link rel="stylesheet" href="{{ asset('css/internal.css') }}">
    <link rel="stylesheet" href="{{ asset('css/order-history.css') }}">
    <link rel="stylesheet" href="{{ asset('css/visual-refresh.css') }}">
</head>
<body class="internal-page internal-cart order-history-page courier-profile-page">
    @include('partials.internal-header')
    <main class="order-history-main">
        <a class="profile-back-link" href="{{ route('cliente.pedidos') }}">&larr; Volver al historial</a>
        <header class="order-history-heading">
            <div>
                <p class="order-history-kicker">Perfil del repartidor</p>
                <h1>{{ trim(($repartidor->Nombre ?? '').' '.($repartidor->Apellido ?? '')) ?: 'Repartidor' }}</h1>
                <p>Información y calificaciones de tus entregas.</p>
            </div>
        </header>

        <section class="courier-profile-info" aria-label="Información del repartidor">
            <div>
                <span>Teléfono</span>
                @if ($repartidor->{'Teléfono'})
                    <a href="tel:{{ $repartidor->{'Teléfono'} }}">{{ $repartidor->{'Teléfono'} }}</a>
                @else
                    <strong>No informado</strong>
                @endif
            </div>
            <div>
                <span>Calificación promedio</span>
                @if ($totalCalificaciones > 0)
                    <strong>{{ number_format((float) $promedioCalificaciones, 1, ',', '.') }} / 5</strong>
                    <span class="courier-profile-stars" aria-label="Promedio de {{ number_format((float) $promedioCalificaciones, 1, ',', '.') }} sobre 5">{{ str_repeat('★', (int) round($promedioCalificaciones)) }}{{ str_repeat('☆', 5 - (int) round($promedioCalificaciones)) }}</span>
                @else
                    <strong>Sin calificaciones</strong>
                @endif
                <small>{{ $totalCalificaciones }} {{ $totalCalificaciones === 1 ? 'calificación' : 'calificaciones' }}</small>
            </div>
        </section>

        <section class="courier-profile-reviews" aria-labelledby="courier-reviews-title">
            <h2 id="courier-reviews-title">Opiniones de clientes</h2>
            @if ($resenas->isEmpty())
                <p class="courier-profile-empty">Todavía no hay calificaciones para este repartidor.</p>
            @else
                <div class="courier-profile-review-list">
                    @foreach ($resenas as $resena)
                        @php($puntuacion = max(0, min(5, (int) $resena->Puntuacion)))
                        <article class="courier-profile-review">
                            <header>
                                <strong>{{ $resena->Nombre_Cliente ?: 'Cliente' }}</strong>
                                <span aria-label="{{ $puntuacion }} de 5 estrellas">{{ str_repeat('★', $puntuacion) }}{{ str_repeat('☆', 5 - $puntuacion) }}</span>
                            </header>
                            <p>{{ $resena->Comentario ?: 'El cliente no dejó un comentario.' }}</p>
                        </article>
                    @endforeach
                </div>
                @if ($resenas->hasPages())
                    <nav class="history-pagination" aria-label="Paginación de calificaciones">
                        @if ($resenas->previousPageUrl())<a href="{{ $resenas->previousPageUrl() }}" rel="prev">&larr; Anteriores</a>@else<span></span>@endif
                        <span>Página {{ $resenas->currentPage() }} de {{ $resenas->lastPage() }}</span>
                        @if ($resenas->nextPageUrl())<a href="{{ $resenas->nextPageUrl() }}" rel="next">Siguientes &rarr;</a>@else<span></span>@endif
                    </nav>
                @endif
            @endif
        </section>
    </main>
</body>
</html>
