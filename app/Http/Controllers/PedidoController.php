<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class PedidoController extends Controller
{
    private function cartClientColumn(): string
    {
        return Schema::hasColumn('Carrito', 'ID_Cliente') ? 'ID_Cliente' : 'CI_Cliente';
    }

    private function cartClientValue(): string|int
    {
        if ($this->cartClientColumn() === 'CI_Cliente') {
            return session('usuario_id');
        }

        return DB::table('Cliente')
            ->where('CI', session('usuario_id'))
            ->value('id');
    }

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
            ->first(['ID_Producto']);

        abort_unless($producto, 404, 'El producto ya no está disponible.');

        $columnaCliente = $this->cartClientColumn();
        $valorCliente = $this->cartClientValue();
        $carrito = DB::table('Carrito')
            ->where($columnaCliente, $valorCliente)
            ->where('ID_Producto', $producto->ID_Producto)
            ->first();
        $cantidad = ($carrito->Cantidad ?? 0) + $validated['cantidad'];

        DB::table('Carrito')->updateOrInsert(
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
        $items = DB::table('Carrito')
            ->join('Producto', 'Carrito.ID_Producto', '=', 'Producto.ID_Producto')
            ->leftJoin('Comercio', 'Producto.RUT_Comercio', '=', 'Comercio.RUT')
            ->where("Carrito.{$columnaCliente}", $valorCliente)
            ->select('Carrito.*', 'Producto.Nombre_Producto', 'Producto.Precio', 'Producto.Foto_Producto', 'Comercio.Nombre_Comercio')
            ->orderBy('Carrito.ID_Carrito')
            ->get();
        $cliente = DB::table('Cliente')->where('CI', session('usuario_id'))->first();

        return view('Cliente.Carrito', [
            'items' => $items,
            'telefonoUsuario' => $cliente?->{'Teléfono'},
        ]);
    }

    public function removeFromCart(int $item)
    {
        $this->requireClient();

        $columnaCliente = $this->cartClientColumn();
        $valorCliente = $this->cartClientValue();
        DB::table('Carrito')
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
        ]);

        $pedidos = DB::transaction(function () use ($validated) {
            $columnaCliente = $this->cartClientColumn();
            $valorCliente = $this->cartClientValue();
            $items = DB::table('Carrito')
                ->where($columnaCliente, $valorCliente)
                ->lockForUpdate()
                ->get();

            abort_if($items->isEmpty(), 422, 'El carrito está vacío.');
            $ids = [];

            foreach ($items as $item) {
                $producto = DB::table('Producto')
                    ->where('ID_Producto', $item->ID_Producto)
                    ->where('Disponible', true)
                    ->lockForUpdate()
                    ->first(['ID_Producto', 'Precio', 'RUT_Comercio']);

                abort_unless($producto, 422, 'Uno de los productos ya no está disponible.');
                $ids[] = $this->insertOrder($producto, $item->Cantidad, $validated);
            }

            DB::table('Carrito')->where($columnaCliente, $valorCliente)->delete();

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

        $rutComercio = DB::table('Comercio')
            ->where('Email_Usuario', session('email'))
            ->value('RUT');

        abort_unless($rutComercio, 403);

        $subpedido = DB::table('Subpedido')
            ->where('N_Subpedido', $pedido)
            ->where('RUT_Comercio', $rutComercio)
            ->first(['N_Subpedido', 'N_Pedido']);

        abort_unless($subpedido, 404, 'El pedido no pertenece a este local.');

        DB::table('Subpedido')
            ->where('N_Subpedido', $subpedido->N_Subpedido)
            ->update(['Estado' => $validated['estado']]);

        DB::table('Pedido')
            ->where('N_Pedido', $subpedido->N_Pedido)
            ->update([
                'Confirmacion_entrega' => $validated['estado'] === 'aceptado',
            ]);

        return redirect()
            ->route('dashboard.local')
            ->with('success', 'Pedido '.($validated['estado'] === 'aceptado' ? 'aceptado' : 'rechazado').' correctamente.');
    }

    private function insertOrder(object $producto, int $cantidad, array $validated): int
    {
            $total = $producto->Precio * $cantidad;
            $repartidor = DB::table('Repartidor')->value('CI');

            $tarjeta = DB::table('Tarjeta')
                ->where('CI_Cliente', session('usuario_id'))
                ->value('ID');
            $tarjeta ??= DB::table('Tarjeta')->insertGetId([
                'CI_Cliente' => session('usuario_id'),
                'Banco' => $validated['metodo_pago'],
            ]);

            $pedido = DB::table('Pedido')->insertGetId([
                'CI_Cliente' => session('usuario_id'),
                'CI_Repartidor' => $repartidor,
                'ID_Tarjeta' => $tarjeta,
                'Fecha' => now()->toDateString(),
                'Hora' => now()->toTimeString(),
                'Estado' => 'pendiente',
                'Ubicacion' => $validated['direccion_envio'],
                'Monto_Total' => $total,
                'Metodo_de_pago' => $validated['metodo_pago'],
            ], 'N_Pedido');

            $subpedido = DB::table('Subpedido')->insertGetId([
                'N_Pedido' => $pedido,
                'RUT_Comercio' => $producto->RUT_Comercio,
                'Fecha' => now()->toDateString(),
                'Hora' => now()->toTimeString(),
                'Monto_Total' => $total,
                'Estado' => 'pendiente',
            ], 'N_Subpedido');

            DB::table('Detalle_de_pedido')->insert([
                'N_Subpedido' => $subpedido,
                'ID_Producto' => $producto->ID_Producto,
                'Cantidad' => $cantidad,
            ]);

            return $pedido;
    }
}
