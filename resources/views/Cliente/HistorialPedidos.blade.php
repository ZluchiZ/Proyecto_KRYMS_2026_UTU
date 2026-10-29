<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mis pedidos | El Gaucho Va</title>
    <link rel="stylesheet" href="{{ asset('css/internal.css') }}">
    <link rel="stylesheet" href="{{ asset('css/order-history.css') }}">
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
<<<<<<< HEAD
=======
                            'requiere_decision_cliente' => 'cancelled',
>>>>>>> 08917fc (Sistema de pedidos + Notificaciones + Ajustes en el repartidor (opciones para cambiar el estado del pedido e ingresar codigo de confirmacion de entrega))
                            'entregado', 'completado', 'listo' => 'completed',
                            'aceptado', 'en_reparto' => 'progress',
                            default => 'pending',
                        };
                    @endphp
<<<<<<< HEAD
                    <article class="history-card">
=======
                    <article class="history-card" id="pedido-{{ $pedido->N_Pedido }}">
>>>>>>> 08917fc (Sistema de pedidos + Notificaciones + Ajustes en el repartidor (opciones para cambiar el estado del pedido e ingresar codigo de confirmacion de entrega))
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

<<<<<<< HEAD
=======
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

>>>>>>> 08917fc (Sistema de pedidos + Notificaciones + Ajustes en el repartidor (opciones para cambiar el estado del pedido e ingresar codigo de confirmacion de entrega))
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
<<<<<<< HEAD
                                @if ($local->total !== null)
=======
                                @if ($local->total !== null && $local->estado !== 'omitido')
>>>>>>> 08917fc (Sistema de pedidos + Notificaciones + Ajustes en el repartidor (opciones para cambiar el estado del pedido e ingresar codigo de confirmacion de entrega))
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