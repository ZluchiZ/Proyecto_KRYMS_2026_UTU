<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Historial de ventas | {{ $comercio->Nombre_Comercio }}</title>
    <link rel="stylesheet" href="{{ asset('css/internal.css') }}">
    <link rel="stylesheet" href="{{ asset('css/order-history.css') }}">
</head>
<body class="internal-page order-history-page local-sales-history-page">
    @include('partials.internal-header')
    <main class="order-history-main">
        <a class="profile-back-link" href="{{ route('dashboard.local') }}">&larr; Volver al panel</a>
        <header class="order-history-heading">
            <div>
                <p class="order-history-kicker">{{ $comercio->Nombre_Comercio }}</p>
                <h1>Historial de ventas</h1>
                <p>Consultá los pedidos recibidos, sus productos y sus clientes.</p>
            </div>
            <span class="order-history-count">{{ $ventas->total() }} {{ $ventas->total() === 1 ? 'venta' : 'ventas' }}</span>
        </header>

        @if ($ventas->isEmpty())
            <section class="history-empty">
                <span class="history-empty-mark" aria-hidden="true">↗</span>
                <h2>Todavía no hay ventas</h2>
                <p>Cuando recibas pedidos, van a aparecer en este historial.</p>
            </section>
        @else
            <section class="history-list" aria-label="Ventas anteriores">
                @foreach ($ventas as $venta)
                    @php
                        $estadoVenta = strtolower($venta->Estado ?? 'pendiente');
                        $claseEstado = match ($estadoVenta) {
                            'rechazado', 'cancelado' => 'cancelled',
                            'entregado', 'completado', 'listo' => 'completed',
                            'aceptado', 'en_reparto' => 'progress',
                            default => 'pending',
                        };
                        $nombreCliente = trim(($venta->Nombre_Cliente ?? '').' '.($venta->Apellido_Cliente ?? ''));
                    @endphp
                    <article class="history-card">
                        <header class="history-card-heading">
                            <div>
                                <span class="history-order-label">Pedido #{{ $venta->N_Pedido }}</span>
                                <span class="history-order-date">{{ $venta->Fecha ?: 'Fecha no disponible' }}@if ($venta->Hora) · {{ substr($venta->Hora, 0, 5) }}@endif</span>
                            </div>
                            <span class="history-status {{ $claseEstado }}">{{ ucfirst(str_replace('_', ' ', $venta->Estado ?? 'pendiente')) }}</span>
                        </header>

                        <div class="history-summary">
                            <div>
                                <span>Cliente</span>
                                <strong>{{ $nombreCliente ?: ($venta->Correo_Cliente ?? 'Cliente') }}</strong>
                                @if ($nombreCliente && $venta->Correo_Cliente)
                                    <strong>{{ $venta->Correo_Cliente }}</strong>
                                @endif
                            </div>
                            <div><span>Entrega</span><strong>{{ $venta->Ubicacion ?: 'No indicada' }}</strong></div>
                            <div><span>Pago</span><strong>{{ ucfirst($venta->Metodo_de_pago ?? 'No indicado') }}</strong></div>
                            <div><span>Total</span><strong>$U {{ number_format((float) ($venta->Total_Local ?? 0), 2, ',', '.') }}</strong></div>
                        </div>

                        <section class="history-store">
                            <header class="history-store-heading">
                                <h2>Productos</h2>
                                <span>Subpedido #{{ $venta->N_Subpedido }}</span>
                            </header>
                            @php($productos = $productosPorSubpedido->get($venta->N_Subpedido, collect()))
                            @if ($productos->isNotEmpty())
                                <ul class="history-products">
                                    @foreach ($productos as $producto)
                                        <li><span>{{ $producto->Nombre_Producto }}</span><strong>× {{ $producto->Cantidad }}</strong></li>
                                    @endforeach
                                </ul>
                            @else
                                <p class="history-no-products">No hay productos detallados para este pedido.</p>
                            @endif
                        </section>
                    </article>
                @endforeach
            </section>

            @if ($ventas->hasPages())
                <nav class="history-pagination" aria-label="Paginación de ventas">
                    @if ($ventas->previousPageUrl())
                        <a href="{{ $ventas->previousPageUrl() }}" rel="prev">&larr; Más recientes</a>
                    @else
                        <span></span>
                    @endif
                    <span>Página {{ $ventas->currentPage() }} de {{ $ventas->lastPage() }}</span>
                    @if ($ventas->nextPageUrl())
                        <a href="{{ $ventas->nextPageUrl() }}" rel="next">Anteriores &rarr;</a>
                    @else
                        <span></span>
                    @endif
                </nav>
            @endif
        @endif
    </main>
</body>
</html>