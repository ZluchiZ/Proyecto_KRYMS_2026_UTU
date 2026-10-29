<?php

namespace App\Http\Controllers;

<<<<<<< HEAD
=======
use App\Services\InAppNotificationService;
>>>>>>> 08917fc (Sistema de pedidos + Notificaciones + Ajustes en el repartidor (opciones para cambiar el estado del pedido e ingresar codigo de confirmacion de entrega))
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Schema;

class PedidoController extends Controller
{
    private function geocode(?string $address): array
    {
        if (! $address) {
            return ['latitud' => null, 'longitud' => null];
        }

        try {
            $result = Http::withHeaders([
                'User-Agent' => 'KRYMS Proyecto 2026 / Laravel',
            ])->timeout(5)->get('https://nominatim.openstreetmap.org/search', [
                'q' => $address.', Uruguay',
                'format' => 'json',
                'limit' => 1,
            ])->json();

            return [
                'latitud' => isset($result[0]['lat']) ? (float) $result[0]['lat'] : null,
                'longitud' => isset($result[0]['lon']) ? (float) $result[0]['lon'] : null,
            ];
        } catch (\Throwable) {
            return ['latitud' => null, 'longitud' => null];
        }
    }

    private function cartClientColumn(): string
    {
        return Schema::hasColumn('carrito', 'ID_Cliente') ? 'ID_Cliente' : 'CI_Cliente';
    }

    private function cartClientValue(): string|int
    {
        if ($this->cartClientColumn() === 'CI_Cliente') {
            return session('usuario_id');
        }

        return DB::table('cliente')
            ->where('CI', session('usuario_id'))
            ->value('id');
    }

    private function productUnitPriceWithDiscount(float|int $precio, float|int $descuentoPorcentaje): float
    {
        $precio = (float) $precio;
        $descuento = max(0, min(100, (float) $descuentoPorcentaje));

        return round($precio * (1 - ($descuento / 100)), 2);
    }

    private function requireClient(): void
    {
        abort_unless(session('tipo_usuario') === 'cliente' && session('usuario_id'), 403);
    }

    public function clientLocationForm()
    {
        $this->requireClient();

        $locationColumns = ['Direccion_Entrega', 'latitud', 'longitud'];
        if (Schema::hasColumn('cliente', 'direccion')) {
            $locationColumns[] = 'direccion';
        }

        $cliente = DB::table('cliente')
            ->where('CI', session('usuario_id'))
            ->first($locationColumns);

        return view('Cliente.Ubicacion', [
            'direccionEntrega' => $cliente?->direccion ?? $cliente?->Direccion_Entrega,
            'latitudUsuario' => $cliente?->latitud,
            'longitudUsuario' => $cliente?->longitud,
        ]);
    }

    public function updateClientLocation(Request $request)
    {
        $this->requireClient();

        $validated = $request->validate([
            'direccion' => 'required|string|max:500',
            'latitud' => 'required|numeric|between:-90,90',
            'longitud' => 'required|numeric|between:-180,180',
        ]);

        $locationUpdate = [
            'Direccion_Entrega' => $validated['direccion'],
            'latitud' => $validated['latitud'],
            'longitud' => $validated['longitud'],
        ];

        if (Schema::hasColumn('cliente', 'direccion')) {
            $locationUpdate['direccion'] = $validated['direccion'];
        }

        DB::table('cliente')
            ->where('CI', session('usuario_id'))
            ->update($locationUpdate);

        return redirect()->route('home')->with('success', 'Dirección de entrega actualizada.');
    }

    public function addToCart(Request $request)
    {
        $this->requireClient();

        $validated = $request->validate([
            'producto_id' => 'required|integer|exists:producto,ID_Producto',
            'cantidad' => 'required|integer|min:1|max:999999',
        ]);

        $producto = DB::table('producto')
            ->join('comercio', 'producto.RUT_Comercio', '=', 'comercio.RUT')
            ->where('ID_Producto', $validated['producto_id'])
            ->where('Disponible', true)
            ->where('comercio.Abierto', true)
            ->first(['producto.ID_Producto']);

        abort_unless($producto, 404, 'El producto ya no está disponible o el local está cerrado.');

        $columnaCliente = $this->cartClientColumn();
        $valorCliente = $this->cartClientValue();
        $carrito = DB::table('carrito')
            ->where($columnaCliente, $valorCliente)
            ->where('ID_Producto', $producto->ID_Producto)
            ->first();
        $cantidad = ($carrito->Cantidad ?? 0) + $validated['cantidad'];

        DB::table('carrito')->updateOrInsert(
            [
                $columnaCliente => $valorCliente,
                'ID_Producto' => $producto->ID_Producto,
            ],
            [
                'Cantidad' => $cantidad,
                'updated_at' => now(),
                'created_at' => $carrito->created_at ?? now(),
            ]
        );

        return redirect()->route('home')->with('success', 'Producto agregado al carrito.');
    }

    public function cart()
    {
        $this->requireClient();

        $columnaCliente = $this->cartClientColumn();
        $valorCliente = $this->cartClientValue();
        $items = DB::table('carrito')
            ->join('producto', 'carrito.ID_Producto', '=', 'producto.ID_Producto')
            ->leftJoin('comercio', 'producto.RUT_Comercio', '=', 'comercio.RUT')
            ->where("carrito.{$columnaCliente}", $valorCliente)
            ->select('carrito.*', 'producto.Nombre_Producto', 'producto.Precio', 'producto.Descuento_Porcentaje', 'producto.Foto_Producto', 'comercio.Nombre_Comercio')
            ->orderBy('carrito.ID_Carrito')
            ->get();

        $items->each(function ($item): void {
            $item->Precio_Con_Descuento = $this->productUnitPriceWithDiscount(
                $item->Precio,
                $item->Descuento_Porcentaje ?? 0,
            );
            $item->Subtotal = round(($item->Precio_Con_Descuento * ($item->Cantidad ?? 0)), 2);
        });

        $cliente = DB::table('cliente')->where('CI', session('usuario_id'))->first();

        return view('Cliente.CarritoNuevo', [
            'items' => $items,
            'totalCarrito' => round($items->sum('Subtotal'), 2),
            'telefonoUsuario' => $cliente?->{'Teléfono'},
            'direccionEntrega' => $cliente?->direccion ?? $cliente?->Direccion_Entrega,
            'latitudUsuario' => $cliente?->latitud,
            'longitudUsuario' => $cliente?->longitud,
        ]);
    }

    public function orderHistory()
    {
        $this->requireClient();

        $pedidos = DB::table('pedido')
            ->where('CI_Cliente', session('usuario_id'))
<<<<<<< HEAD
            ->select('N_Pedido', 'Fecha', 'Hora', 'Estado', 'Ubicacion', 'Monto_Total', 'Costo_de_envio', 'Metodo_de_pago')
=======
            ->select('N_Pedido', 'Fecha', 'Hora', 'Estado', 'Estado_Entrega', 'Codigo_Secreto', 'Ubicacion', 'Monto_Total', 'Costo_de_envio', 'Metodo_de_pago')
>>>>>>> 08917fc (Sistema de pedidos + Notificaciones + Ajustes en el repartidor (opciones para cambiar el estado del pedido e ingresar codigo de confirmacion de entrega))
            ->orderByDesc('N_Pedido')
            ->paginate(10);

        $idsPedido = $pedidos->getCollection()->pluck('N_Pedido');
        $localesPorPedido = collect();

        if ($idsPedido->isNotEmpty()) {
            $localesPorPedido = DB::table('subpedido')
                ->leftJoin('comercio', 'subpedido.RUT_Comercio', '=', 'comercio.RUT')
                ->leftJoin('detalle_de_pedido', 'subpedido.N_Subpedido', '=', 'detalle_de_pedido.N_Subpedido')
                ->leftJoin('producto', 'detalle_de_pedido.ID_Producto', '=', 'producto.ID_Producto')
                ->whereIn('subpedido.N_Pedido', $idsPedido)
                ->select(
                    'subpedido.N_Pedido',
                    'subpedido.N_Subpedido',
                    'subpedido.Estado as Estado_Subpedido',
                    'subpedido.Monto_Total as Total_Local',
                    'comercio.Nombre_Comercio',
                    'producto.Nombre_Producto',
                    'detalle_de_pedido.Cantidad'
                )
                ->orderBy('subpedido.N_Subpedido')
                ->get()
                ->groupBy('N_Pedido');
        }

        $pedidos->getCollection()->transform(function ($pedido) use ($localesPorPedido) {
            $pedido->locales = $localesPorPedido->get($pedido->N_Pedido, collect())
                ->groupBy('N_Subpedido')
                ->map(function ($lineas) {
                    $local = $lineas->first();

                    return (object) [
                        'nombre' => $local->Nombre_Comercio ?? 'Local',
                        'estado' => $local->Estado_Subpedido ?? 'pendiente',
                        'total' => $local->Total_Local,
                        'productos' => $lineas
                            ->filter(fn ($linea) => $linea->Nombre_Producto !== null)
                            ->values(),
                    ];
                })
                ->values();

            return $pedido;
        });

        return view('Cliente.HistorialPedidos', ['pedidos' => $pedidos]);
    }

    public function removeFromCart(int $item)
    {
        $this->requireClient();

        $columnaCliente = $this->cartClientColumn();
        $valorCliente = $this->cartClientValue();
        DB::table('carrito')
            ->where('ID_Carrito', $item)
            ->where($columnaCliente, $valorCliente)
            ->delete();

        return redirect()->route('carrito')->with('success', 'Producto eliminado del carrito.');
    }

<<<<<<< HEAD
    public function confirmCart(Request $request)
=======
    public function confirmCart(Request $request, InAppNotificationService $notifications)
>>>>>>> 08917fc (Sistema de pedidos + Notificaciones + Ajustes en el repartidor (opciones para cambiar el estado del pedido e ingresar codigo de confirmacion de entrega))
    {
        $this->requireClient();

        $validated = $request->validate([
            'direccion_envio' => 'required|string|max:500',
            'telefono_contacto' => 'nullable|string|max:30',
            'referencias' => 'nullable|string|max:1000',
            'metodo_pago' => 'required|in:efectivo,tarjeta',
            'latitud' => 'nullable|numeric|between:-90,90',
            'longitud' => 'nullable|numeric|between:-180,180',
        ]);

        $coordenadas = [
            'latitud' => $validated['latitud'] ?? null,
            'longitud' => $validated['longitud'] ?? null,
        ];
        if (! $coordenadas['latitud'] || ! $coordenadas['longitud']) {
            $coordenadas = $this->geocode($validated['direccion_envio']);
        }
        abort_unless($coordenadas['latitud'] && $coordenadas['longitud'], 422, 'No pudimos ubicar la dirección. Selecciona un punto en el mapa.');

        if (Schema::hasTable('cliente') && Schema::hasColumn('cliente', 'latitud') && Schema::hasColumn('cliente', 'longitud')) {
            DB::table('cliente')
                ->where('CI', session('usuario_id'))
                ->update([
                    'latitud' => $coordenadas['latitud'],
                    'longitud' => $coordenadas['longitud'],
                ]);
        }

<<<<<<< HEAD
        $locales = DB::transaction(function () use ($validated, $coordenadas) {
=======
        $locales = DB::transaction(function () use ($validated, $coordenadas, $notifications) {
>>>>>>> 08917fc (Sistema de pedidos + Notificaciones + Ajustes en el repartidor (opciones para cambiar el estado del pedido e ingresar codigo de confirmacion de entrega))
            $columnaCliente = $this->cartClientColumn();
            $valorCliente = $this->cartClientValue();
            $items = DB::table('carrito')
                ->where($columnaCliente, $valorCliente)
                ->lockForUpdate()
                ->get();

            abort_if($items->isEmpty(), 422, 'El carrito está vacío.');
            $productosPorLocal = [];

            foreach ($items as $item) {
                $producto = DB::table('producto')
                    ->join('comercio', 'producto.RUT_Comercio', '=', 'comercio.RUT')
                    ->where('ID_Producto', $item->ID_Producto)
                    ->where('Disponible', true)
                    ->where('comercio.Abierto', true)
                    ->lockForUpdate()
                    ->first(['producto.ID_Producto', 'producto.Precio', 'producto.Descuento_Porcentaje', 'producto.RUT_Comercio']);

                abort_unless($producto, 422, 'Uno de los productos ya no está disponible o el local está cerrado.');
                $productosPorLocal[$producto->RUT_Comercio][] = [
                    'producto' => $producto,
                    'cantidad' => (int) $item->Cantidad,
                ];
            }

<<<<<<< HEAD
            $this->insertOrder($productosPorLocal, $validated, $coordenadas);
=======
            $this->insertOrder($productosPorLocal, $validated, $coordenadas, $notifications);
>>>>>>> 08917fc (Sistema de pedidos + Notificaciones + Ajustes en el repartidor (opciones para cambiar el estado del pedido e ingresar codigo de confirmacion de entrega))
            DB::table('carrito')->where($columnaCliente, $valorCliente)->delete();

            return count($productosPorLocal);
        });

        return redirect()->route('carrito')->with('order_sent', $locales);
    }

<<<<<<< HEAD
    public function updateStatus(Request $request, int $pedido)
=======
    public function updateStatus(Request $request, int $pedido, InAppNotificationService $notifications)
>>>>>>> 08917fc (Sistema de pedidos + Notificaciones + Ajustes en el repartidor (opciones para cambiar el estado del pedido e ingresar codigo de confirmacion de entrega))
    {
        abort_unless(session('tipo_usuario') === 'comercio' && session('usuario_id'), 403);

        $validated = $request->validate([
            'estado' => 'required|in:aceptado,rechazado,listo',
        ]);

        $rutComercio = DB::table('comercio')
            ->where('Email_Usuario', session('email'))
            ->value('RUT');

        abort_unless($rutComercio, 403);

<<<<<<< HEAD
        $subpedido = DB::table('subpedido')
            ->where('N_Subpedido', $pedido)
            ->where('RUT_Comercio', $rutComercio)
            ->first(['N_Subpedido', 'N_Pedido']);

        abort_unless($subpedido, 404, 'El pedido no pertenece a este local.');

        DB::transaction(function () use ($subpedido, $validated): void {
=======
        DB::transaction(function () use ($pedido, $rutComercio, $validated, $notifications): void {
            $subpedido = DB::table('subpedido')
                ->where('N_Subpedido', $pedido)
                ->where('RUT_Comercio', $rutComercio)
                ->lockForUpdate()
                ->first(['N_Subpedido', 'N_Pedido', 'Estado']);

            abort_unless($subpedido, 404, 'El pedido no pertenece a este local.');

            $pedidoActual = DB::table('pedido')
                ->where('N_Pedido', $subpedido->N_Pedido)
                ->lockForUpdate()
                ->first(['N_Pedido', 'CI_Cliente', 'Estado']);

            abort_unless($pedidoActual, 404, 'El pedido no existe.');
            abort_if(
                in_array($pedidoActual->Estado, ['cancelado', 'en_reparto', 'entregado', 'completado'], true),
                409,
                'Este pedido ya no admite cambios.'
            );

            $estadoAnterior = $subpedido->Estado ?? 'pendiente';
            $transicionPermitida = match ($validated['estado']) {
                'aceptado', 'rechazado' => $estadoAnterior === 'pendiente',
                'listo' => $estadoAnterior === 'aceptado',
            };
            abort_unless($transicionPermitida, 409, 'El estado del subpedido ya fue actualizado.');

>>>>>>> 08917fc (Sistema de pedidos + Notificaciones + Ajustes en el repartidor (opciones para cambiar el estado del pedido e ingresar codigo de confirmacion de entrega))
            DB::table('subpedido')
                ->where('N_Subpedido', $subpedido->N_Subpedido)
                ->update(['Estado' => $validated['estado']]);

            $estados = DB::table('subpedido')
                ->where('N_Pedido', $subpedido->N_Pedido)
<<<<<<< HEAD
                ->pluck('Estado');

            if ($estados->contains('rechazado')) {
                $estadoPedido = 'rechazado';
=======
                ->where('Estado', '<>', 'omitido')
                ->pluck('Estado');

            if ($estados->contains('rechazado')) {
                $estadoPedido = 'requiere_decision_cliente';
>>>>>>> 08917fc (Sistema de pedidos + Notificaciones + Ajustes en el repartidor (opciones para cambiar el estado del pedido e ingresar codigo de confirmacion de entrega))
            } elseif ($estados->isNotEmpty() && $estados->every(fn ($estado) => $estado === 'listo')) {
                $estadoPedido = 'listo';
            } elseif ($estados->contains('aceptado') || $estados->contains('listo')) {
                $estadoPedido = 'aceptado';
            } else {
                $estadoPedido = 'pendiente';
            }

            DB::table('pedido')
                ->where('N_Pedido', $subpedido->N_Pedido)
                ->update([
                    'Estado' => $estadoPedido,
                    'Confirmacion_entrega' => in_array($estadoPedido, ['aceptado', 'listo'], true),
                ]);
<<<<<<< HEAD
=======

            $nombreComercio = DB::table('comercio')
                ->where('RUT', $rutComercio)
                ->value('Nombre_Comercio') ?? 'Un comercio';

            if (in_array($validated['estado'], ['aceptado', 'rechazado'], true) && $pedidoActual->CI_Cliente) {
                $estadoTexto = $validated['estado'] === 'aceptado' ? 'aceptó' : 'rechazó';
                $notifications->sendTo(
                    'cliente',
                    $pedidoActual->CI_Cliente,
                    'respuesta_comercio',
                    "{$nombreComercio} {$estadoTexto} su parte del pedido #{$subpedido->N_Pedido}.",
                    (int) $subpedido->N_Pedido,
                    [
                        'comercio' => $nombreComercio,
                        'estado' => $validated['estado'],
                        'subpedido_id' => $subpedido->N_Subpedido,
                    ],
                );
            }

            if ($estadoPedido === 'listo' && $pedidoActual->Estado !== 'listo' && $pedidoActual->CI_Cliente) {
                $notifications->sendTo(
                    'cliente',
                    $pedidoActual->CI_Cliente,
                    'pedido_listo',
                    "Tu pedido #{$subpedido->N_Pedido} está listo; estamos esperando un repartidor.",
                    (int) $subpedido->N_Pedido,
                    ['estado' => 'listo', 'esperando_repartidor' => true],
                );
            }
>>>>>>> 08917fc (Sistema de pedidos + Notificaciones + Ajustes en el repartidor (opciones para cambiar el estado del pedido e ingresar codigo de confirmacion de entrega))
        });

        $mensaje = match ($validated['estado']) {
            'aceptado' => 'Pedido aceptado correctamente.',
            'rechazado' => 'Pedido rechazado correctamente.',
            'listo' => 'Pedido marcado como listo.',
        };

        return redirect()
            ->route('dashboard.local')
            ->with('success', $mensaje);
    }

<<<<<<< HEAD
    private function insertOrder(array $productosPorLocal, array $validated, array $coordenadas): int
=======
    public function customerDecision(Request $request, int $pedido, InAppNotificationService $notifications)
    {
        $this->requireClient();
        $validated = $request->validate([
            'decision' => 'required|in:continuar,cancelar',
        ]);

        $mensaje = DB::transaction(function () use ($pedido, $validated, $notifications): string {
            $pedidoActual = DB::table('pedido')
                ->where('N_Pedido', $pedido)
                ->where('CI_Cliente', session('usuario_id'))
                ->lockForUpdate()
                ->first(['N_Pedido', 'Estado', 'Monto_Total', 'CI_Cliente']);

            abort_unless($pedidoActual, 404, 'El pedido no existe.');
            abort_unless($pedidoActual->Estado === 'requiere_decision_cliente', 409, 'Este pedido no requiere una decisión.');

            if ($validated['decision'] === 'cancelar') {
                DB::table('subpedido')
                    ->where('N_Pedido', $pedido)
                    ->update(['Estado' => 'cancelado']);
                DB::table('pedido')
                    ->where('N_Pedido', $pedido)
                    ->update(['Estado' => 'cancelado', 'Confirmacion_entrega' => false]);
                $notifications->notifyOrderParticipants(
                    $pedido,
                    'pedido_cancelado_cliente',
                    "El cliente canceló el pedido #{$pedido}.",
                    ['estado' => 'cancelado'],
                );

                return 'El pedido completo fue cancelado.';
            }

            $comerciosActivos = DB::table('subpedido')
                ->where('N_Pedido', $pedido)
                ->whereNotIn('Estado', ['rechazado', 'omitido', 'cancelado'])
                ->count();
            abort_if($comerciosActivos === 0, 409, 'No quedan comercios que puedan preparar este pedido; cancelalo por completo.');

            DB::table('subpedido')
                ->where('N_Pedido', $pedido)
                ->where('Estado', 'rechazado')
                ->update(['Estado' => 'omitido']);

            $estados = DB::table('subpedido')
                ->where('N_Pedido', $pedido)
                ->where('Estado', '<>', 'omitido')
                ->pluck('Estado');
            $nuevoEstado = $estados->every(fn ($estado) => $estado === 'listo')
                ? 'listo'
                : ($estados->contains('aceptado') || $estados->contains('listo') ? 'aceptado' : 'pendiente');
            $nuevoTotal = DB::table('subpedido')
                ->where('N_Pedido', $pedido)
                ->where('Estado', '<>', 'omitido')
                ->sum('Monto_Total');

            DB::table('pedido')
                ->where('N_Pedido', $pedido)
                ->update([
                    'Estado' => $nuevoEstado,
                    'Monto_Total' => $nuevoTotal,
                    'Confirmacion_entrega' => in_array($nuevoEstado, ['aceptado', 'listo'], true),
                ]);

            if ($nuevoEstado === 'listo' && $pedidoActual->Estado !== 'listo') {
                $notifications->sendTo(
                    'cliente',
                    $pedidoActual->CI_Cliente,
                    'pedido_listo',
                    "Tu pedido #{$pedido} está listo; estamos esperando un repartidor.",
                    $pedido,
                    ['estado' => 'listo', 'esperando_repartidor' => true],
                );
            }

            return 'El pedido continúa sin los comercios que lo rechazaron.';
        });

        return redirect()->route('cliente.pedidos')->with('success', $mensaje);
    }

    private function insertOrder(
        array $productosPorLocal,
        array $validated,
        array $coordenadas,
        InAppNotificationService $notifications,
    ): int
>>>>>>> 08917fc (Sistema de pedidos + Notificaciones + Ajustes en el repartidor (opciones para cambiar el estado del pedido e ingresar codigo de confirmacion de entrega))
    {
        $totalPedido = 0;
        foreach ($productosPorLocal as $productos) {
            foreach ($productos as $item) {
                $precioFinal = $this->productUnitPriceWithDiscount(
                    $item['producto']->Precio,
                    $item['producto']->Descuento_Porcentaje ?? 0,
                );
                $totalPedido += round($precioFinal * $item['cantidad'], 2);
            }
        }

        $distancia = null;
        if (count($productosPorLocal) === 1) {
            $rutLocal = array_key_first($productosPorLocal);
            $local = DB::table('comercio')->where('RUT', $rutLocal)->first(['latitud', 'longitud']);
            if ($local?->latitud !== null && $local?->longitud !== null) {
                $distancia = DB::selectOne(
                    'SELECT 6371 * ACOS(LEAST(1, GREATEST(-1, COS(RADIANS(?)) * COS(RADIANS(latitud)) * COS(RADIANS(longitud) - RADIANS(?)) + SIN(RADIANS(?)) * SIN(RADIANS(latitud))))) AS distancia FROM comercio WHERE RUT = ? LIMIT 1',
                    [$coordenadas['latitud'], $coordenadas['longitud'], $coordenadas['latitud'], $rutLocal]
                )->distancia ?? null;
            }
        }

        $tarjeta = DB::table('tarjeta')
            ->where('CI_Cliente', session('usuario_id'))
            ->value('ID');
        $tarjeta ??= DB::table('tarjeta')->insertGetId([
            'CI_Cliente' => session('usuario_id'),
            'Banco' => $validated['metodo_pago'],
        ]);

        $pedido = DB::table('pedido')->insertGetId([
            'CI_Cliente' => session('usuario_id'),
            'CI_Repartidor' => null,
            'ID_Tarjeta' => $tarjeta,
            'Fecha' => now()->toDateString(),
            'Hora' => now()->toTimeString(),
            'Estado' => 'pendiente',
            'Ubicacion' => $validated['direccion_envio'],
            'latitud' => $coordenadas['latitud'],
            'longitud' => $coordenadas['longitud'],
            'Distancia_Local_Km' => $distancia,
            'Monto_Total' => $totalPedido,
            'Metodo_de_pago' => $validated['metodo_pago'],
<<<<<<< HEAD
=======
            'Codigo_Secreto' => $this->generateDeliveryCode(),
>>>>>>> 08917fc (Sistema de pedidos + Notificaciones + Ajustes en el repartidor (opciones para cambiar el estado del pedido e ingresar codigo de confirmacion de entrega))
        ], 'N_Pedido');

        foreach ($productosPorLocal as $rutLocal => $productos) {
            $totalLocal = 0;
            foreach ($productos as $item) {
                $precioFinal = $this->productUnitPriceWithDiscount(
                    $item['producto']->Precio,
                    $item['producto']->Descuento_Porcentaje ?? 0,
                );
                $totalLocal += round($precioFinal * $item['cantidad'], 2);
            }
            $subpedido = DB::table('subpedido')->insertGetId([
                'N_Pedido' => $pedido,
                'RUT_Comercio' => $rutLocal,
                'Fecha' => now()->toDateString(),
                'Hora' => now()->toTimeString(),
                'Monto_Total' => $totalLocal,
                'Estado' => 'pendiente',
            ], 'N_Subpedido');

            foreach ($productos as $item) {
                DB::table('detalle_de_pedido')->insert([
                    'N_Subpedido' => $subpedido,
                    'ID_Producto' => $item['producto']->ID_Producto,
                    'Cantidad' => $item['cantidad'],
                ]);
            }
<<<<<<< HEAD
=======

            $nombreComercio = DB::table('comercio')
                ->where('RUT', $rutLocal)
                ->value('Nombre_Comercio') ?? 'Un comercio';
            $notifications->sendTo(
                'comercio',
                $rutLocal,
                'nuevo_pedido',
                "Recibiste un nuevo subpedido #{$subpedido} del pedido #{$pedido}.",
                $pedido,
                ['comercio' => $nombreComercio, 'subpedido_id' => $subpedido],
            );
>>>>>>> 08917fc (Sistema de pedidos + Notificaciones + Ajustes en el repartidor (opciones para cambiar el estado del pedido e ingresar codigo de confirmacion de entrega))
        }

        return $pedido;
    }
<<<<<<< HEAD
=======

    private function generateDeliveryCode(): string
    {
        do {
            $code = strtoupper(\Illuminate\Support\Str::random(12));
        } while (DB::table('pedido')->where('Codigo_Secreto', $code)->exists());

        return $code;
    }
>>>>>>> 08917fc (Sistema de pedidos + Notificaciones + Ajustes en el repartidor (opciones para cambiar el estado del pedido e ingresar codigo de confirmacion de entrega))
}
