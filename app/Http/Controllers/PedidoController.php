<?php

namespace App\Http\Controllers;

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

    public function confirmCart(Request $request)
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

        $pedidos = DB::transaction(function () use ($validated, $coordenadas) {
            $columnaCliente = $this->cartClientColumn();
            $valorCliente = $this->cartClientValue();
            $items = DB::table('carrito')
                ->where($columnaCliente, $valorCliente)
                ->lockForUpdate()
                ->get();

            abort_if($items->isEmpty(), 422, 'El carrito está vacío.');
            $ids = [];

            foreach ($items as $item) {
                $producto = DB::table('producto')
                    ->join('comercio', 'producto.RUT_Comercio', '=', 'comercio.RUT')
                    ->where('ID_Producto', $item->ID_Producto)
                    ->where('Disponible', true)
                    ->where('comercio.Abierto', true)
                    ->lockForUpdate()
                    ->first(['producto.ID_Producto', 'producto.Precio', 'producto.RUT_Comercio']);

                abort_unless($producto, 422, 'Uno de los productos ya no está disponible o el local está cerrado.');
                $ids[] = $this->insertOrder($producto, $item->Cantidad, $validated, $coordenadas);
            }

            DB::table('carrito')->where($columnaCliente, $valorCliente)->delete();

            return $ids;
        });

        return redirect()->route('carrito')->with('order_sent', count($pedidos));
    }

    public function updateStatus(Request $request, int $pedido)
    {
        abort_unless(session('tipo_usuario') === 'comercio' && session('usuario_id'), 403);

        $validated = $request->validate([
            'estado' => 'required|in:aceptado,rechazado,listo',
        ]);

        $rutComercio = DB::table('comercio')
            ->where('Email_Usuario', session('email'))
            ->value('RUT');

        abort_unless($rutComercio, 403);

        $subpedido = DB::table('subpedido')
            ->where('N_Subpedido', $pedido)
            ->where('RUT_Comercio', $rutComercio)
            ->first(['N_Subpedido', 'N_Pedido']);

        abort_unless($subpedido, 404, 'El pedido no pertenece a este local.');

        DB::table('subpedido')
            ->where('N_Subpedido', $subpedido->N_Subpedido)
            ->update(['Estado' => $validated['estado']]);

        DB::table('pedido')
            ->where('N_Pedido', $subpedido->N_Pedido)
            ->update([
                'Estado' => $validated['estado'],
                'Confirmacion_entrega' => in_array($validated['estado'], ['aceptado', 'listo'], true),
            ]);

        $mensaje = match ($validated['estado']) {
            'aceptado' => 'Pedido aceptado correctamente.',
            'rechazado' => 'Pedido rechazado correctamente.',
            'listo' => 'Pedido marcado como listo.',
        };

        return redirect()
            ->route('dashboard.local')
            ->with('success', $mensaje);
    }

    private function insertOrder(object $producto, int $cantidad, array $validated, array $coordenadas): int
    {
            $precioFinal = $this->productUnitPriceWithDiscount(
                $producto->Precio,
                $producto->Descuento_Porcentaje ?? 0,
            );
            $total = round($precioFinal * $cantidad, 2);
            $local = DB::table('comercio')
                ->where('RUT', $producto->RUT_Comercio)
                ->first(['latitud', 'longitud']);
            $distancia = null;
            if ($local?->latitud !== null && $local?->longitud !== null) {
                $distancia = DB::selectOne(
                    'SELECT 6371 * ACOS(LEAST(1, GREATEST(-1, COS(RADIANS(?)) * COS(RADIANS(latitud)) * COS(RADIANS(longitud) - RADIANS(?)) + SIN(RADIANS(?)) * SIN(RADIANS(latitud))))) AS distancia FROM comercio WHERE RUT = ? LIMIT 1',
                    [$coordenadas['latitud'], $coordenadas['longitud'], $coordenadas['latitud'], $producto->RUT_Comercio]
                )->distancia ?? null;
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
                'Monto_Total' => $total,
                'Metodo_de_pago' => $validated['metodo_pago'],
            ], 'N_Pedido');

            $subpedido = DB::table('subpedido')->insertGetId([
                'N_Pedido' => $pedido,
                'RUT_Comercio' => $producto->RUT_Comercio,
                'Fecha' => now()->toDateString(),
                'Hora' => now()->toTimeString(),
                'Monto_Total' => $total,
                'Estado' => 'pendiente',
            ], 'N_Subpedido');

            DB::table('detalle_de_pedido')->insert([
                'N_Subpedido' => $subpedido,
                'ID_Producto' => $producto->ID_Producto,
                'Cantidad' => $cantidad,
            ]);

            return $pedido;
    }
}
