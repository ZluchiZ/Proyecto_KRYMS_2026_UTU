<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ trim(($repartidor->Nombre ?? '').' '.($repartidor->Apellido ?? '')) ?: 'Repartidor' }} | El Gaucho Va</title>
    <link rel="stylesheet" href="{{ asset('css/internal.css') }}">
    <link rel="stylesheet" href="{{ asset('css/order-history.css') }}">
    <link rel="stylesheet" href="{{ asset('css/visual-refresh.css') }}">
    <style>
        .courier-profile-page .order-history-main {
            max-width: 980px;
        }

        .courier-hero {
            display: grid;
            grid-template-columns: auto 1fr;
            gap: 1.2rem;
            align-items: center;
            background: linear-gradient(135deg, #fff5eb 0%, #fff 45%, #fef1e8 100%);
            border: 1px solid #f3d7bf;
            border-radius: 22px;
            padding: 1.4rem 1.5rem;
            box-shadow: 0 18px 42px rgba(146, 87, 28, 0.08);
            margin-bottom: 1.5rem;
        }

        .courier-avatar {
            width: 78px;
            height: 78px;
            border-radius: 50%;
            display: grid;
            place-items: center;
            background: linear-gradient(135deg, #f39a2d 0%, #d96f00 100%);
            color: #fff;
            font-size: 1.8rem;
            font-weight: 800;
            box-shadow: 0 10px 28px rgba(217, 111, 0, 0.28);
        }

        .courier-hero h1 {
            margin: 0;
            font-size: clamp(1.8rem, 2vw, 2.5rem);
            color: #2b1a17;
        }

        .courier-hero p {
            margin: .3rem 0 0;
            color: #6e5a52;
            font-size: .96rem;
        }

        .courier-profile-info {
            display: grid;
            grid-template-columns: repeat(2, minmax(220px, 1fr));
            gap: 1rem;
            margin-bottom: 2rem;
        }

        .courier-profile-info > div {
            background: #fff;
            border: 1px solid #f0d9c5;
            border-radius: 18px;
            padding: 1rem 1.2rem;
            box-shadow: 0 12px 30px rgba(64, 46, 38, 0.04);
        }

        .courier-profile-info > div > span {
            display: block;
            font-size: .72rem;
            letter-spacing: .08em;
            text-transform: uppercase;
            color: #8a6c5c;
            margin-bottom: .55rem;
        }

        .courier-profile-info > div strong,
        .courier-profile-info > div a {
            font-size: 1.12rem;
            color: #2d1d1a;
            text-decoration: none;
        }

        .courier-profile-stars {
            display: inline-block;
            color: #f4b13a;
            letter-spacing: .08em;
            margin-top: .2rem;
            font-size: 1rem;
        }

        .courier-profile-info small {
            display: block;
            color: #7a635d;
            margin-top: .3rem;
        }

        .courier-profile-reviews {
            background: #fff;
            border: 1px solid #f0d9c5;
            border-radius: 22px;
            padding: 1.2rem 1.3rem 1.1rem;
            box-shadow: 0 12px 30px rgba(64, 46, 38, 0.05);
        }

        .courier-profile-reviews h2 {
            margin: 0 0 1rem;
            color: #2b1a17;
            font-size: 1.35rem;
        }

        .courier-profile-review-list {
            display: grid;
            gap: .9rem;
        }

        .courier-profile-review {
            background: linear-gradient(180deg, #fffdfb 0%, #fff7f1 100%);
            border: 1px solid #f2dfcf;
            border-radius: 16px;
            padding: 1rem 1rem .85rem;
        }

        .courier-profile-review header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: .75rem;
            margin-bottom: .35rem;
        }

        .courier-profile-review strong {
            color: #2d1d1a;
            font-size: .96rem;
        }

        .courier-profile-review span {
            color: #f4b13a;
            letter-spacing: .08em;
            font-size: .82rem;
        }

        .courier-profile-review p {
            margin: 0;
            color: #57443e;
            line-height: 1.5;
        }

        .courier-profile-empty {
            margin: 0;
            background: #fff7f2;
            border: 1px dashed #e8c4a3;
            border-radius: 14px;
            padding: 1rem;
            color: #6a524a;
        }

        @media (max-width: 640px) {
            .courier-hero {
                grid-template-columns: 1fr;
                text-align: center;
            }

            .courier-profile-info {
                grid-template-columns: 1fr;
            }

            .courier-profile-review header {
                flex-direction: column;
                align-items: flex-start;
            }
        }
    </style>
</head>
<body class="internal-page internal-cart order-history-page courier-profile-page">
    @include('partials.internal-header')
    <main class="order-history-main">
        <a class="profile-back-link" href="{{ route('cliente.pedidos') }}">&larr; Volver al historial</a>
        <header class="courier-hero">
            <div class="courier-avatar" aria-hidden="true">
                {{ strtoupper(substr(trim(($repartidor->Nombre ?? '').' '.($repartidor->Apellido ?? '')) ?: 'R', 0, 1)) }}
            </div>
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
