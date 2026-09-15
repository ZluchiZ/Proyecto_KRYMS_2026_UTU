<?php

use App\Http\Controllers\ClienteController;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\LocalController;
use App\Http\Controllers\PedidoController;
use App\Http\Controllers\RepartidorController;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;

Route::get('/test-cliente', function () {
    return DB::table('Usuario')->get();
});

Route::get('/login', function () {
    return view('Login');
})->name('login');

Route::get('/', function (Request $request) {
    if (session('tipo_usuario') === 'comercio' && session('usuario_id')) {
        return redirect()->route('dashboard.local');
    }

    $nombreUsuario = null;
    $correoUsuario = session('email');
    $tipoUsuario = null;
    $identificadorComercio = null;
    $telefonoUsuario = null;

    if (session('tipo_usuario') && session('usuario_id')) {
        $tablas = [
            'cliente' => ['tabla' => 'Cliente', 'clave' => 'CI'],
            'comercio' => ['tabla' => 'Comercio', 'clave' => 'RUT'],
            'repartidor' => ['tabla' => 'Repartidor', 'clave' => 'CI'],
        ];
        $cuenta = $tablas[session('tipo_usuario')] ?? null;

        if ($cuenta) {
            $usuario = DB::table($cuenta['tabla'])->where($cuenta['clave'], session('usuario_id'))->first();
            $nombreUsuario = $usuario?->{'Nombre'} ?? $usuario?->{'Nombre_Comercio'};
            $tipoUsuario = match (session('tipo_usuario')) {
                'comercio' => 'Local',
                'cliente' => 'Cliente',
                'repartidor' => 'Repartidor',
                default => null,
            };

            if ($usuario && session('tipo_usuario') === 'repartidor') {
                $nombreUsuario .= ' '.$usuario->{'Apellido'};
            }

            if ($usuario && session('tipo_usuario') === 'cliente') {
                $telefonoUsuario = $usuario->{'Teléfono'};
            }

            if ($usuario && session('tipo_usuario') === 'comercio') {
                $identificadorComercio = $usuario->{'RUT'};
            }
        }
    }

    $productos = DB::table('Producto')
        ->leftJoin('Comercio', 'Producto.RUT_Comercio', '=', 'Comercio.RUT')
        ->select('Producto.*', 'Comercio.Nombre_Comercio')
        ->where('Disponible', true)
        ->when($identificadorComercio, function ($query) use ($identificadorComercio) {
            $query->where('RUT_Comercio', $identificadorComercio);
        })
        ->when($request->filled('q'), function ($query) use ($request) {
            $busqueda = $request->string('q')->toString();

            $query->where(function ($query) use ($busqueda) {
                $query->where('Nombre_Producto', 'like', "%{$busqueda}%")
                    ->orWhere('Categoria', 'like', "%{$busqueda}%")
                    ;
            });
        })
        ->orderByDesc('ID_Producto')
        ->get();

    $cantidadCarrito = session('tipo_usuario') === 'cliente'
        ? DB::table('Carrito')->where('CI_Cliente', session('usuario_id'))->sum('Cantidad')
        : 0;

    return view('home', [
        'nombreUsuario' => $nombreUsuario,
        'correoUsuario' => $correoUsuario,
        'tipoUsuario' => $tipoUsuario,
        'telefonoUsuario' => $telefonoUsuario,
        'esClienteRegistrado' => session('tipo_usuario') === 'cliente' && session('usuario_id'),
        'productos' => $productos,
        'cantidadCarrito' => $cantidadCarrito,
    ]);
})->name('home');

Route::get('/dashboard-local', function () {
    abort_unless(session('tipo_usuario') === 'comercio' && session('usuario_id'), 403);

    $comercio = DB::table('Comercio')
        ->where('RUT', session('usuario_id'))
        ->where('Email_Usuario', session('email'))
        ->first(['Nombre_Comercio', 'Email_Usuario']);

    abort_unless($comercio, 403);

    $productos = DB::table('Producto')
        ->where('RUT_Comercio', session('usuario_id'))
        ->orderByDesc('ID_Producto')
        ->get();

    $pedidos = DB::table('Pedido')
        ->join('Subpedido', 'Pedido.N_Pedido', '=', 'Subpedido.N_Pedido')
        ->join('Detalle_de_pedido', 'Subpedido.N_Subpedido', '=', 'Detalle_de_pedido.N_Subpedido')
        ->join('Producto', 'Detalle_de_pedido.ID_Producto', '=', 'Producto.ID_Producto')
        ->leftJoin('Cliente', 'Pedido.CI_Cliente', '=', 'Cliente.CI')
        ->where('Subpedido.RUT_Comercio', session('usuario_id'))
        ->select(
            'Pedido.*',
            'Producto.Nombre_Producto',
            'Detalle_de_pedido.Cantidad',
            'Cliente.Nombre as Nombre_Cliente',
            'Cliente.Apellido as Apellido_Cliente',
            'Cliente.Email_Usuario as Correo_Cliente',
            'Cliente.Teléfono as Telefono_Cliente',
            DB::raw('NULL as Telefono_Contacto'),
            DB::raw('NULL as Referencias')
        )
        ->orderByDesc(Schema::hasColumn('Pedido', 'N_Pedido') ? 'Pedido.N_Pedido' : 'Pedido.ID_Pedido')
        ->get();

    return view('Local.DashboardLocal', [
        'productos' => $productos,
        'pedidos' => $pedidos,
        'nombreUsuario' => $comercio->Nombre_Comercio,
        'correoUsuario' => $comercio->Email_Usuario,
        'tipoUsuario' => 'Local',
    ]);
})->name('dashboard.local');

Route::get('/dashboard-repartidor', function () {
    return view('Repartidor.DashboardRepartidor');
})->name('dashboard.repartidor');

Route::get('/register', function () {
    return view('register');
})->name('register');

Route::get('/registerLocal', function () {
    return view('registerLocal');
})->name('registerLocal');
Route::get('/registerRepartidor', function () {
    return view('registerRepartidor');
})->name('registerRepartidor');

Route::post('/Cliente', [ClienteController::class, 'store'])->name('cliente.store');
Route::post('/local', [LocalController::class, 'store'])->name('local.store');
Route::post('/repartidor', [RepartidorController::class, 'store'])->name('repartidor.store');
Route::post('/productos', [LocalController::class, 'storeProducto'])->name('productos.store');
Route::delete('/productos/{producto}', [LocalController::class, 'destroyProducto'])->name('productos.destroy');
Route::post('/carrito/agregar', [PedidoController::class, 'addToCart'])->name('carrito.add');
Route::get('/carrito', [PedidoController::class, 'cart'])->name('carrito');
Route::delete('/carrito/{item}', [PedidoController::class, 'removeFromCart'])->name('carrito.remove');
Route::post('/carrito/confirmar', [PedidoController::class, 'confirmCart'])->name('carrito.confirm');
Route::patch('/pedidos/{pedido}/estado', [PedidoController::class, 'updateStatus'])->name('pedidos.status');

Route::post('/login', [LoginController::class, 'login'])->name('login.post');
Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

Route::get('/verificar/{token}', [ClienteController::class, 'verificar']);