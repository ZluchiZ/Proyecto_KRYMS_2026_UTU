<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class PedidoController extends Controller
{
    private function requireClient(): void
    {
        abort_unless(session('tipo_usuario') === 'cliente' && session('usuario_id'), 403);
    }

    public function addToCart(Request $request)
    {
        $this->requireClient();

        $validated = $request->validate([
            'producto_id' => 'required|integer|exists:Producto,ID_Producto',
            'cantidad' => 'required|integer|min:1|max:999999',
        ]);

        $producto = DB::table('Producto')
            ->where('ID_Producto', $validated['producto_id'])
            ->where('Disponible', true)
            ->first(['ID_Producto', 'Stock']);

        abort_unless($producto, 404, 'El producto ya no está disponible.');

        $carrito = DB::table('Carrito')
            ->where('ID_Cliente', session('usuario_id'))
            ->where('ID_Producto', $producto->ID_Producto)
            ->first();
        $cantidad = ($carrito->Cantidad ?? 0) + $validated['cantidad'];

        abort_if($cantidad > $producto->Stock, 422, 'La cantidad solicitada supera el stock disponible.');

        DB::table('Carrito')->updateOrInsert(
            [
                'ID_Cliente' => session('usuario_id'),
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

        $items = DB::table('Carrito')
            ->join('Producto', 'Carrito.ID_Producto', '=', 'Producto.ID_Producto')
            ->leftJoin('comercio', 'Producto.Correo_Comercio', '=', 'comercio.correo')
            ->where('Carrito.ID_Cliente', session('usuario_id'))
            ->select('Carrito.*', 'Producto.Nombre_Producto', 'Producto.Precio', 'Producto.Stock', 'Producto.Foto_Producto', 'comercio.nombre as Nombre_Comercio')
            ->orderBy('Carrito.ID_Carrito')
            ->get();

        $cliente = DB::table('cliente')->find(session('usuario_id'));

        return view('Cliente.Carrito', [
            'items' => $items,
            'telefonoUsuario' => $cliente?->telefono,
        ]);
    }

    public function removeFromCart(int $item)
    {
        $this->requireClient();

        DB::table('Carrito')
            ->where('ID_Carrito', $item)
            ->where('ID_Cliente', session('usuario_id'))
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
        ]);

        $pedidos = DB::transaction(function () use ($validated) {
            $items = DB::table('Carrito')
                ->where('ID_Cliente', session('usuario_id'))
                ->lockForUpdate()
                ->get();

            abort_if($items->isEmpty(), 422, 'El carrito está vacío.');
            $ids = [];

            foreach ($items as $item) {
                $producto = DB::table('Producto')
                    ->where('ID_Producto', $item->ID_Producto)
                    ->where('Disponible', true)
                    ->lockForUpdate()
                    ->first(['ID_Producto', 'Precio', 'Stock']);

                abort_unless($producto, 422, 'Uno de los productos ya no está disponible.');
                abort_if($item->Cantidad > $producto->Stock, 422, "No hay stock suficiente para el producto #{$producto->ID_Producto}.");

                $ids[] = $this->insertOrder($producto, $item->Cantidad, $validated);
            }

            DB::table('Carrito')->where('ID_Cliente', session('usuario_id'))->delete();

            return $ids;
        });

        return redirect()->route('carrito')->with('order_sent', count($pedidos));
    }

    public function updateStatus(Request $request, int $pedido)
    {
        abort_unless(session('tipo_usuario') === 'comercio' && session('usuario_id'), 403);

        $validated = $request->validate([
            'estado' => 'required|in:aceptado,rechazado',
        ]);

        DB::transaction(function () use ($pedido, $validated) {
            $correoComercio = session('email');
            $idColumn = Schema::hasColumn('Pedido', 'N_Pedido') ? 'Pedido.N_Pedido' : 'Pedido.ID_Pedido';
            $pedidoActual = DB::table('Pedido')
                ->join('Producto', 'Pedido.ID_Producto', '=', 'Producto.ID_Producto')
                ->where($idColumn, $pedido)
                ->where('Producto.Correo_Comercio', $correoComercio)
                ->lockForUpdate()
                ->first(['Pedido.Estado', 'Pedido.Cantidad', 'Pedido.ID_Producto']);

            abort_unless($pedidoActual, 404);

            if ($pedidoActual->Estado === 'rechazado' || $pedidoActual->Estado === 'aceptado') {
                return;
            }

            if ($validated['estado'] === 'rechazado') {
                DB::table('Pedido')
                    ->where($idColumn, $pedido)
                    ->delete();

                return;
            }

            $producto = DB::table('Producto')
                ->where('ID_Producto', $pedidoActual->ID_Producto)
                ->lockForUpdate()
                ->first(['Stock']);

            abort_unless($producto, 404);
            abort_if($pedidoActual->Cantidad > $producto->Stock, 422, 'No hay stock suficiente para aceptar este pedido.');

            DB::table('Producto')
                ->where('ID_Producto', $pedidoActual->ID_Producto)
                ->decrement('Stock', $pedidoActual->Cantidad);

            DB::table('Pedido')
                ->where($idColumn, $pedido)
                ->update([
                    'Estado' => $validated['estado'],
                    'updated_at' => now(),
                ]);
        });

        return redirect()->route('dashboard.local');
    }

    private function insertOrder(object $producto, int $cantidad, array $validated): int
    {
            $total = $producto->Precio * $cantidad;
            $datosPedido = [
                'ID_Cliente' => session('usuario_id'),
                'ID_Producto' => $producto->ID_Producto,
                'Cantidad' => $cantidad,
                'Total' => $total,
                'Direccion_Envio' => $validated['direccion_envio'],
                'Telefono_Contacto' => $validated['telefono_contacto'] ?? null,
                'Referencias' => $validated['referencias'] ?? null,
                'Metodo_Pago' => $validated['metodo_pago'],
                'Estado' => 'pendiente',
                'created_at' => now(),
                'updated_at' => now(),
            ];

            if (Schema::hasColumn('Pedido', 'CI_Cliente')) {
                $datosPedido['CI_Cliente'] = null;
            }
            if (Schema::hasColumn('Pedido', 'CI_Repartidor')) {
                $datosPedido['CI_Repartidor'] = null;
            }
            if (Schema::hasColumn('Pedido', 'ID_Tarjeta')) {
                $datosPedido['ID_Tarjeta'] = null;
            }
            if (Schema::hasColumn('Pedido', 'Fecha')) {
                $datosPedido['Fecha'] = now()->toDateString();
            }
            if (Schema::hasColumn('Pedido', 'Hora')) {
                $datosPedido['Hora'] = now()->toTimeString();
            }
            if (Schema::hasColumn('Pedido', 'Ubicacion')) {
                $datosPedido['Ubicacion'] = $validated['direccion_envio'];
            }
            if (Schema::hasColumn('Pedido', 'Monto_Total')) {
                $datosPedido['Monto_Total'] = $total;
            }
            if (Schema::hasColumn('Pedido', 'Metodo_de_pago')) {
                $datosPedido['Metodo_de_pago'] = $validated['metodo_pago'];
            }

            $idColumn = Schema::hasColumn('Pedido', 'ID_Pedido') ? 'ID_Pedido' : 'N_Pedido';

            return DB::table('Pedido')->insertGetId($datosPedido, $idColumn);
    }
}
