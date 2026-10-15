<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="{{ asset('css/dashboard-repartidor.css') }}">
    <link rel="stylesheet" href="{{ asset('css/internal.css') }}">
    <title>Dashboard repartidor</title>
</head>
<body class="internal-page internal-dashboard">
    @include('partials.internal-header')
    <main>
        <section class="profile">
            <div class="profile-info">
                <strong>{{ $nombreUsuario }}</strong>
                <span>{{ $tipoUsuario }}</span>
            </div>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit">Cerrar sesión</button>
            </form>
        </section>

        @if (session('success'))
            <div class="success">{{ session('success') }}</div>
        @endif

        <section class="dashboard-panel">
            <div class="panel-header">
                <div>
                    <p class="eyebrow">Entrega</p>
                    <h1>Pedidos listos</h1>
                </div>
            </div>

            @if ($pedidosListos->isEmpty())
                <div class="empty">No hay pedidos listos para repartir.</div>
            @else
                <div class="order-list">
                    @foreach ($pedidosListos as $pedido)
                        <article class="order-card">
                            <div class="order-header">
                                <strong>Pedido #{{ $pedido->N_Pedido }}</strong>
                                <span class="status-pill ready">Listo</span>
                            </div>

                            <div class="order-grid">
                                <p><strong>Local:</strong> {{ $pedido->Nombre_Comercio }}</p>
                                <p><strong>Producto:</strong> {{ $pedido->Nombre_Producto }}</p>
                                <p><strong>Cliente:</strong> {{ trim(($pedido->Nombre_Cliente ?? '').' '.($pedido->Apellido_Cliente ?? '')) ?: ($pedido->Correo_Cliente ?? 'Cliente') }}</p>
                                <p><strong>Cantidad:</strong> {{ $pedido->Cantidad }}</p>
                                <p><strong>Dirección:</strong> {{ $pedido->Direccion_Envio }}</p>
                                <p><strong>Total:</strong> $U {{ number_format($pedido->Total ?? $pedido->Monto_Total, 2, ',', '.') }}</p>
                                @if ($pedido->Telefono_Cliente)
                                    <p><strong>Teléfono:</strong> {{ $pedido->Telefono_Cliente }}</p>
                                @endif
                            </div>

                            <div class="order-actions">
                                <form method="POST" action="{{ route('repartidor.pedidos.estado', $pedido->N_Pedido) }}">
                                    @csrf
                                    @method('PATCH')
                                    <input type="hidden" name="estado" value="aceptado">
                                    <button type="submit">Aceptar entrega</button>
                                </form>
                                <form method="POST" action="{{ route('repartidor.pedidos.estado', $pedido->N_Pedido) }}">
                                    @csrf
                                    @method('PATCH')
                                    <input type="hidden" name="estado" value="rechazado">
                                    <button type="submit" class="secondary">Rechazar</button>
                                </form>
                            </div>
                        </article>
                    @endforeach
                </div>
            @endif
        </section>

        <section class="dashboard-panel my-delivery">
            <div class="panel-header">
                <div>
                    <p class="eyebrow">Mi entrega</p>
                    <h2>Pedidos asignados</h2>
                </div>
            </div>

            @if ($miEntrega->isEmpty())
                <div class="empty">Todavía no aceptaste ninguna entrega.</div>
            @else
                <div class="order-list">
                    @foreach ($miEntrega as $pedido)
                        <article class="order-card assigned">
                            <div class="order-header">
                                <strong>Pedido #{{ $pedido->N_Pedido }}</strong>
                                <span class="status-pill assigned">Asignado</span>
                            </div>

                            <div class="order-grid">
                                <p><strong>Local:</strong> {{ $pedido->Nombre_Comercio }}</p>
                                <p><strong>Producto:</strong> {{ $pedido->Nombre_Producto }}</p>
                                <p><strong>Cliente:</strong> {{ trim(($pedido->Nombre_Cliente ?? '').' '.($pedido->Apellido_Cliente ?? '')) ?: ($pedido->Correo_Cliente ?? 'Cliente') }}</p>
                                <p><strong>Cantidad:</strong> {{ $pedido->Cantidad }}</p>
                                <p><strong>Dirección:</strong> {{ $pedido->Direccion_Envio }}</p>
                                <p><strong>Total:</strong> $U {{ number_format($pedido->Total ?? $pedido->Monto_Total, 2, ',', '.') }}</p>
                                @if ($pedido->Telefono_Cliente)
                                    <p><strong>Teléfono:</strong> {{ $pedido->Telefono_Cliente }}</p>
                                @endif
                            </div>
                        </article>
                    @endforeach
                </div>
            @endif
        </section>
    </main>
</body>
</html>