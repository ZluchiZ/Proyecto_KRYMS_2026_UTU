<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mis pedidos | El Gaucho Va</title>
    <link rel="stylesheet" href="{{ asset('css/internal.css') }}">
    <link rel="stylesheet" href="{{ asset('css/order-history.css') }}">
    <link rel="stylesheet" href="{{ asset('css/visual-refresh.css') }}">
</head>
<body class="internal-page internal-cart order-history-page">
    @include('partials.internal-header')
    <main class="order-history-main">
        <a class="profile-back-link" href="{{ route('cliente.profile') }}">&larr; Volver a mi perfil</a>
        <header class="order-history-heading">
            <div>
                <p class="order-history-kicker">Tu actividad</p>
                <h1>Historial de pedidos</h1>
                <p>Consultá el estado y los detalles de tus compras.</p>
            </div>
            <span class="order-history-count">{{ $pedidos->total() }} {{ $pedidos->total() === 1 ? 'pedido' : 'pedidos' }}</span>
        </header>

        @if (session('success'))
            <p class="history-feedback history-feedback-success" role="status">{{ session('success') }}</p>
        @endif
        @if ($errors->any())
            <p class="history-feedback history-feedback-error" role="alert">Revisá los datos de la reseña e intentá nuevamente.</p>
        @endif

        @if ($pedidos->isEmpty())
            <section class="history-empty">
                <span class="history-empty-mark" aria-hidden="true">↗</span>
                <h2>Todavía no tenés pedidos</h2>
                <p>Cuando hagas una compra, vas a poder seguirla desde acá.</p>
                <a class="history-primary-link" href="{{ route('home') }}">Explorar productos</a>
            </section>
        @else
            <section class="history-list" aria-label="Pedidos anteriores">
                @foreach ($pedidos as $pedido)
                    @php
                        $estadoPedido = strtolower($pedido->Estado ?? 'pendiente');
                        $claseEstado = match ($estadoPedido) {
                            'rechazado', 'cancelado' => 'cancelled',
                            'requiere_decision_cliente' => 'cancelled',
                            'entregado', 'completado', 'listo' => 'completed',
                            'aceptado', 'en_reparto' => 'progress',
                            default => 'pending',
                        };
                    @endphp
                    <article class="history-card" id="pedido-{{ $pedido->N_Pedido }}">
                        <header class="history-card-heading">
                            <div>
                                <span class="history-order-label">Pedido #{{ $pedido->N_Pedido }}</span>
                                <span class="history-order-date">{{ $pedido->Fecha ?: 'Fecha no disponible' }}@if ($pedido->Hora) · {{ substr($pedido->Hora, 0, 5) }}@endif</span>
                            </div>
                            <span class="history-status {{ $claseEstado }}">{{ ucfirst(str_replace('_', ' ', $pedido->Estado ?? 'pendiente')) }}</span>
                        </header>

                        <div class="history-summary">
                            <div><span>Entrega en</span><strong>{{ $pedido->Ubicacion ?: 'Dirección no disponible' }}</strong></div>
                            <div><span>Pago</span><strong>{{ ucfirst($pedido->Metodo_de_pago ?? 'No indicado') }}</strong></div>
                            <div><span>Total</span><strong>$U {{ number_format((float) ($pedido->Monto_Total ?? 0), 2, ',', '.') }}</strong></div>
                        </div>

                        @if ($estadoPedido === 'entregado' && $pedido->CI_Repartidor)
                            <section class="history-store courier-review" aria-label="Reseña del repartidor">
                                @if ($pedido->resenaRepartidor)
                                    <h2>Tu reseña al repartidor</h2>
                                    <p class="courier-review-rating" aria-label="{{ $pedido->resenaRepartidor->Puntuacion }} de 5 estrellas">
                                        {{ str_repeat('★', (int) $pedido->resenaRepartidor->Puntuacion) }}{{ str_repeat('☆', 5 - (int) $pedido->resenaRepartidor->Puntuacion) }}
                                    </p>
                                    @if ($pedido->resenaRepartidor->Comentario)
                                        <p>{{ $pedido->resenaRepartidor->Comentario }}</p>
                                    @endif
                                @else
                                    <h2>¿Cómo fue la entrega?</h2>
                                    <p>Dejá una reseña para el repartidor de este pedido.</p>
                                    <form method="POST" action="{{ route('pedidos.reviews.courier.store', $pedido->N_Pedido) }}">
                                        @csrf
                                        <label for="puntuacion-repartidor-{{ $pedido->N_Pedido }}">Puntuación (1 a 5 estrellas)</label>
                                        <select id="puntuacion-repartidor-{{ $pedido->N_Pedido }}" name="puntuacion" required>
                                            <option value="">Seleccionar puntuación</option>
                                            @for ($puntuacion = 5; $puntuacion >= 1; $puntuacion--)
                                                <option value="{{ $puntuacion }}" @selected(old('puntuacion') == $puntuacion)>{{ $puntuacion }} {{ $puntuacion === 1 ? 'estrella' : 'estrellas' }}</option>
                                            @endfor
                                        </select>
                                        <label for="comentario-repartidor-{{ $pedido->N_Pedido }}">Comentario (opcional, máximo 250 caracteres)</label>
                                        <textarea id="comentario-repartidor-{{ $pedido->N_Pedido }}" name="comentario" maxlength="250">{{ old('comentario') }}</textarea>
                                        @error('puntuacion')<small class="review-field-error">{{ $message }}</small>@enderror
                                        @error('comentario')<small class="review-field-error">{{ $message }}</small>@enderror
                                        <button type="submit">Enviar reseña</button>
                                    </form>
                                @endif
                            </section>
                        @endif

                        @if ($estadoPedido === 'en_reparto' && $pedido->Codigo_Secreto)
                            <section class="history-store" aria-label="Código secreto de entrega">
                                <h2>Código secreto de entrega</h2>
                                <p>Compartí este código con el repartidor cuando recibas tu pedido. Solo vos podés verlo.</p>
                                <strong>{{ $pedido->Codigo_Secreto }}</strong>
                            </section>
                        @endif

                        @if ($estadoPedido === 'en_reparto')
                            @php
                                $etapasEntrega = [
                                    'productos_recolectados' => 'El gaucho recogió todos sus productos',
                                    'en_camino' => 'El gaucho va en camino',
                                    'en_puerta' => 'El gaucho está en tu puerta',
                                ];
                                $etapaActual = $pedido->Estado_Entrega;
                                $etapaActualIndice = array_search($etapaActual, array_keys($etapasEntrega), true);
                            @endphp
                            <section class="history-store" aria-label="Progreso de entrega">
                                <h2>Seguimiento de entrega</h2>
                                @if (! $etapaActual)
                                    <p>Esperando que el repartidor recoja todos los productos.</p>
                                @endif
                                <ol class="delivery-progress">
                                    @foreach ($etapasEntrega as $etapa => $etiqueta)
                                        <li class="{{ $etapaActual === $etapa ? 'current' : ($etapaActualIndice !== false && array_search($etapa, array_keys($etapasEntrega), true) < $etapaActualIndice ? 'complete' : '') }}">
                                            {{ $etiqueta }}
                                        </li>
                                    @endforeach
                                </ol>
                            </section>
                        @endif

                        @if ($estadoPedido === 'requiere_decision_cliente')
                            <div class="history-store">
                                <strong>Un comercio rechazó su parte del pedido.</strong>
                                <p>Podés continuar sin los comercios que rechazaron o cancelar el pedido completo.</p>
                                @if ($pedido->locales->contains(fn ($local) => ! in_array($local->estado, ['rechazado', 'omitido', 'cancelado'], true)))
                                    <form method="POST" action="{{ route('pedidos.client-decision', $pedido->N_Pedido) }}">
                                        @csrf
                                        @method('PATCH')
                                        <input type="hidden" name="decision" value="continuar">
                                        <button type="submit">Continuar sin esos comercios</button>
                                    </form>
                                @endif
                                <form method="POST" action="{{ route('pedidos.client-decision', $pedido->N_Pedido) }}" onsubmit="return confirm('¿Cancelar el pedido completo?');">
                                    @csrf
                                    @method('PATCH')
                                    <input type="hidden" name="decision" value="cancelar">
                                    <button type="submit">Cancelar pedido completo</button>
                                </form>
                            </div>
                        @endif

                        @foreach ($pedido->locales as $local)
                            <section class="history-store">
                                <header class="history-store-heading">
                                    <h2>{{ $local->nombre }}</h2>
                                    <span>{{ ucfirst(str_replace('_', ' ', $local->estado)) }}</span>
                                </header>
                                @if ($local->productos->isNotEmpty())
                                    <ul class="history-products">
                                        @foreach ($local->productos as $producto)
                                            <li><span>{{ $producto->Nombre_Producto }}</span><strong>× {{ $producto->Cantidad }}</strong></li>
                                        @endforeach
                                    </ul>
                                @else
                                    <p class="history-no-products">No hay productos detallados para este local.</p>
                                @endif
                                @if ($local->total !== null && $local->estado !== 'omitido')
                                    <p class="history-store-total">Subtotal del local <strong>$U {{ number_format((float) $local->total, 2, ',', '.') }}</strong></p>
                                @endif
                            </section>
                        @endforeach
                    </article>
                @endforeach
            </section>

            @if ($pedidos->hasPages())
                <nav class="history-pagination" aria-label="Paginación de pedidos">
                    @if ($pedidos->previousPageUrl())
                        <a href="{{ $pedidos->previousPageUrl() }}" rel="prev">&larr; Más recientes</a>
                    @else
                        <span></span>
                    @endif
                    <span>Página {{ $pedidos->currentPage() }} de {{ $pedidos->lastPage() }}</span>
                    @if ($pedidos->nextPageUrl())
                        <a href="{{ $pedidos->nextPageUrl() }}" rel="next">Anteriores &rarr;</a>
                    @else
                        <span></span>
                    @endif
                </nav>
            @endif
        @endif
    </main>
</body>
</html>