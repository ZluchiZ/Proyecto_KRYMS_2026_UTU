<?php

use App\Http\Controllers\ClienteController;
use App\Http\Controllers\GoogleController;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\LocalController;
use App\Http\Controllers\PedidoController;
use App\Http\Controllers\RepartidorController;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;

Route::get('/test-cliente', function () {
    return DB::table('usuario')->get();
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
    $direccionEntrega = null;
    $latitudUsuario = null;
    $longitudUsuario = null;

    if (session('tipo_usuario') && session('usuario_id')) {
        $tablas = [
            'cliente' => ['tabla' => 'cliente', 'clave' => 'CI'],
            'comercio' => ['tabla' => 'comercio', 'clave' => 'RUT'],
            'repartidor' => ['tabla' => 'repartidor', 'clave' => 'CI'],
        ];
        $cuenta = $tablas[session('tipo_usuario')] ?? null;

        if ($cuenta) {
            $usuario = DB::table($cuenta['tabla'])
                ->where('Email_Usuario', session('email'))
                ->first();
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
                $direccionEntrega = $usuario->direccion ?? $usuario->Direccion_Entrega;
                $latitudUsuario = $usuario->latitud;
                $longitudUsuario = $usuario->longitud;
            }

            if ($usuario && session('tipo_usuario') === 'comercio') {
                $identificadorComercio = $usuario->{'RUT'} ?? session('usuario_id');
            }
        }
    }

    $productos = DB::table('producto')
        ->leftJoin('comercio', 'producto.RUT_Comercio', '=', 'comercio.RUT')
        ->select('producto.*', 'comercio.Nombre_Comercio', 'comercio.Logo', 'comercio.Dirección', 'comercio.Abierto')
        ->when($identificadorComercio, function ($query) use ($identificadorComercio) {
            $query->where('RUT_Comercio', $identificadorComercio);
        })
        ->when($request->filled('categoria'), function ($query) use ($request) {
            $query->where('Categoria', $request->string('categoria')->toString());
        })
        ->when($request->filled('q'), function ($query) use ($request) {
            $busqueda = trim($request->string('q')->toString());

            if ($busqueda === '') {
                return;
            }

            $query->where(function ($query) use ($busqueda) {
                $query->where('Nombre_Producto', 'like', "%{$busqueda}%")
                    ->orWhere('Categoria', 'like', "%{$busqueda}%")
                    ->orWhere('comercio.Nombre_Comercio', 'like', "%{$busqueda}%");
            });
        })
        ->orderByDesc('ID_Producto')
        ->get();

    $locales = DB::table('comercio')
        ->whereNotNull('RUT')
        ->orderBy('Nombre_Comercio')
        ->get(['RUT', 'Nombre_Comercio', 'Logo', 'Dirección', 'Horario', 'Abierto']);

    $cantidadCarrito = 0;
    if (session('tipo_usuario') === 'cliente' && session('usuario_id')) {
        $columnaClienteCarrito = Schema::hasColumn('carrito', 'ID_Cliente')
            ? 'ID_Cliente'
            : 'CI_Cliente';
        $idClienteCarrito = $columnaClienteCarrito === 'ID_Cliente'
            ? DB::table('cliente')->where('CI', session('usuario_id'))->value('id')
            : session('usuario_id');

        $cantidadCarrito = DB::table('carrito')
            ->where($columnaClienteCarrito, $idClienteCarrito)
            ->sum('Cantidad');
    }

    return view('home', [
        'nombreUsuario' => $nombreUsuario,
        'correoUsuario' => $correoUsuario,
        'tipoUsuario' => $tipoUsuario,
        'telefonoUsuario' => $telefonoUsuario,
        'direccionEntrega' => $direccionEntrega,
        'latitudUsuario' => $latitudUsuario,
        'longitudUsuario' => $longitudUsuario,
        'esClienteRegistrado' => session('tipo_usuario') === 'cliente' && session('usuario_id'),
        'productos' => $productos,
        'locales' => $locales,
        'cantidadCarrito' => $cantidadCarrito,
    ]);
})->name('home');

Route::get('/restaurantes', function () {
    $locales = DB::table('comercio')
        ->whereNotNull('RUT')
        ->orderBy('Nombre_Comercio')
        ->get(['RUT', 'Nombre_Comercio', 'Logo', 'Dirección', 'Horario', 'Abierto']);

    return view('restaurants', [
        'locales' => $locales,
        'esClienteRegistrado' => session('tipo_usuario') === 'cliente' && session('usuario_id'),
    ]);
})->name('restaurants');

Route::get('/productos', function () {
    $productos = DB::table('producto')
        ->join('comercio', 'producto.RUT_Comercio', '=', 'comercio.RUT')
        ->select('producto.*', 'comercio.Nombre_Comercio', 'comercio.Abierto')
        ->orderByDesc('producto.Disponible')
        ->orderByDesc('producto.ID_Producto')
        ->get();

    return view('products', [
        'productos' => $productos,
        'esClienteRegistrado' => session('tipo_usuario') === 'cliente' && session('usuario_id'),
    ]);
})->name('products');

Route::get('/local/{rut}', function (string $rut) {
    $comercio = DB::table('comercio')
        ->where('RUT', $rut)
        ->first(['RUT', 'Nombre_Comercio', 'Logo', 'Dirección', 'Horario', 'Abierto']);

    abort_unless($comercio, 404);

    $productos = DB::table('producto')
        ->where('RUT_Comercio', $comercio->RUT)
        ->orderByDesc('Disponible')
        ->orderByDesc('ID_Producto')
        ->get();

    $productos->each(function ($producto) use ($comercio): void {
        $producto->Nombre_Comercio = $comercio->Nombre_Comercio;
        $producto->Abierto = $comercio->Abierto;
    });

    return view('local-profile', [
        'comercio' => $comercio,
        'productos' => $productos,
        'esClienteRegistrado' => session('tipo_usuario') === 'cliente' && session('usuario_id'),
    ]);
})->name('local.profile');

Route::get('/dashboard-local', function () {
    abort_unless(session('tipo_usuario') === 'comercio' && session('usuario_id'), 403);

    $comercio = DB::table('comercio')
        ->where('Email_Usuario', session('email'))
        ->first(['Nombre_Comercio', 'Email_Usuario', 'RUT', 'Abierto']);

    abort_unless($comercio, 403);

    $productos = collect();
    $pedidos = collect();

    // Products and orders reference Comercio.RUT, so a local without RUT has no
    // compatible rows to query until it registers one.
    if ($comercio->RUT) {
        $productos = DB::table('producto')
            ->where('RUT_Comercio', $comercio->RUT)
            ->orderByDesc('ID_Producto')
            ->get();

        $pedidos = DB::table('pedido')
            ->join('subpedido', 'pedido.N_Pedido', '=', 'subpedido.N_Pedido')
            ->join('detalle_de_pedido', 'subpedido.N_Subpedido', '=', 'detalle_de_pedido.N_Subpedido')
            ->join('producto', 'detalle_de_pedido.ID_Producto', '=', 'producto.ID_Producto')
            ->leftJoin('cliente', 'pedido.CI_Cliente', '=', 'cliente.CI')
            ->where('subpedido.RUT_Comercio', $comercio->RUT)
            ->where('subpedido.Estado', '<>', 'rechazado')
            ->select(
                'pedido.*',
                'subpedido.N_Subpedido',
                'subpedido.Estado as Estado_Subpedido',
                'producto.Nombre_Producto',
                'detalle_de_pedido.Cantidad',
                'cliente.Nombre as Nombre_Cliente',
                'cliente.Apellido as Apellido_Cliente',
                'cliente.Email_Usuario as Correo_Cliente',
                'cliente.Teléfono as Telefono_Cliente',
                DB::raw('NULL as Telefono_Contacto'),
                DB::raw('NULL as Referencias')
            )
            ->orderByDesc('pedido.N_Pedido')
            ->get();
    }

    return view('Local.DashboardLocal', [
        'productos' => $productos,
        'pedidos' => $pedidos,
        'comercioAbierto' => (bool) $comercio->Abierto,
        'nombreUsuario' => $comercio->Nombre_Comercio,
        'correoUsuario' => $comercio->Email_Usuario,
        'tipoUsuario' => 'Local',
    ]);
})->name('dashboard.local');

Route::get('/dashboard-repartidor', function () {
    $email = session('email');
    $ciRepartidor = session('usuario_id');

    if (! $ciRepartidor && $email) {
        $ciRepartidor = DB::table('repartidor')
            ->where('Email_Usuario', $email)
            ->value('CI');
    }

    if (session('tipo_usuario') !== 'repartidor' && $email) {
        $esRepartidor = DB::table('repartidor')
            ->where('Email_Usuario', $email)
            ->exists();

        if ($esRepartidor) {
            session(['tipo_usuario' => 'repartidor', 'usuario_id' => $ciRepartidor ?: DB::table('repartidor')->where('Email_Usuario', $email)->value('CI')]);
            $ciRepartidor = session('usuario_id');
        }
    }

    abort_unless(session('tipo_usuario') === 'repartidor' || ($email && $ciRepartidor), 403, 'No tienes permisos para acceder al dashboard del repartidor.');
    abort_unless($ciRepartidor, 403, 'El repartidor no está registrado.');

    $tieneTablaDecisiones = Schema::hasTable('pedido_repartidor_estado');

    $pedidosListosQuery = DB::table('subpedido')
        ->join('pedido', 'subpedido.N_Pedido', '=', 'pedido.N_Pedido')
        ->join('detalle_de_pedido', 'subpedido.N_Subpedido', '=', 'detalle_de_pedido.N_Subpedido')
        ->join('producto', 'detalle_de_pedido.ID_Producto', '=', 'producto.ID_Producto')
        ->join('comercio', 'subpedido.RUT_Comercio', '=', 'comercio.RUT')
        ->leftJoin('cliente', 'pedido.CI_Cliente', '=', 'cliente.CI')
        ->where('subpedido.Estado', 'listo')
        ->where(function ($query) {
            $query->whereNull('pedido.CI_Repartidor')
                ->orWhere('pedido.CI_Repartidor', '');
        });

    if ($tieneTablaDecisiones) {
        $pedidosListosQuery->whereNotExists(function ($subQuery) {
            $subQuery->from('pedido_repartidor_estado')
                ->whereColumn('pedido_repartidor_estado.N_Pedido', 'pedido.N_Pedido')
                ->whereIn('pedido_repartidor_estado.Estado', ['aceptado', 'rechazado']);
        });
    }

    $pedidosListos = $pedidosListosQuery
        ->select(
            'pedido.N_Pedido',
            'pedido.CI_Cliente',
            'pedido.Metodo_de_pago',
            'pedido.Monto_Total as Total',
            'pedido.Ubicacion as Direccion_Envio',
            'pedido.Fecha',
            'pedido.Hora',
            'subpedido.N_Subpedido',
            'subpedido.Estado as Estado_Subpedido',
            'producto.Nombre_Producto',
            'producto.Precio',
            'detalle_de_pedido.Cantidad',
            'comercio.Nombre_Comercio',
            'cliente.Nombre as Nombre_Cliente',
            'cliente.Apellido as Apellido_Cliente',
            'cliente.Email_Usuario as Correo_Cliente',
            'cliente.Teléfono as Telefono_Cliente'
        )
        ->distinct()
        ->orderByDesc('pedido.N_Pedido')
        ->get();

    $miEntrega = DB::table('subpedido')
        ->join('pedido', 'subpedido.N_Pedido', '=', 'pedido.N_Pedido')
        ->join('detalle_de_pedido', 'subpedido.N_Subpedido', '=', 'detalle_de_pedido.N_Subpedido')
        ->join('producto', 'detalle_de_pedido.ID_Producto', '=', 'producto.ID_Producto')
        ->join('comercio', 'subpedido.RUT_Comercio', '=', 'comercio.RUT')
        ->leftJoin('cliente', 'pedido.CI_Cliente', '=', 'cliente.CI')
        ->where('pedido.CI_Repartidor', $ciRepartidor)
        ->whereIn('subpedido.Estado', ['listo', 'en_reparto'])
        ->select(
            'pedido.N_Pedido',
            'pedido.CI_Cliente',
            'pedido.Metodo_de_pago',
            'pedido.Monto_Total as Total',
            'pedido.Ubicacion as Direccion_Envio',
            'pedido.Fecha',
            'pedido.Hora',
            'subpedido.N_Subpedido',
            'subpedido.Estado as Estado_Subpedido',
            'producto.Nombre_Producto',
            'producto.Precio',
            'detalle_de_pedido.Cantidad',
            'comercio.Nombre_Comercio',
            'cliente.Nombre as Nombre_Cliente',
            'cliente.Apellido as Apellido_Cliente',
            'cliente.Email_Usuario as Correo_Cliente',
            'cliente.Teléfono as Telefono_Cliente'
        )
        ->distinct()
        ->orderByDesc('pedido.N_Pedido')
        ->get();

    return view('Repartidor.DashboardRepartidor', [
        'pedidosListos' => $pedidosListos,
        'miEntrega' => $miEntrega,
        'nombreUsuario' => session('email'),
        'tipoUsuario' => 'Repartidor',
    ]);
})->name('dashboard.repartidor');

Route::patch('/repartidor/pedidos/{pedido}/estado', [RepartidorController::class, 'updatePedidoStatus'])->name('repartidor.pedidos.estado');

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
Route::get('/mi-cuenta', [ClienteController::class, 'profile'])->name('cliente.profile');
Route::post('/productos', [LocalController::class, 'storeProducto'])->name('productos.store');
Route::patch('/comercio/estado', [LocalController::class, 'updateStatus'])->name('comercio.status');
Route::patch('/productos/{producto}', [LocalController::class, 'updateProducto'])->name('productos.update');
Route::delete('/productos/{producto}', [LocalController::class, 'destroyProducto'])->name('productos.destroy');
Route::post('/carrito/agregar', [PedidoController::class, 'addToCart'])->name('carrito.add');
Route::get('/cliente/ubicacion', [PedidoController::class, 'clientLocationForm'])->name('cliente.location.form');
Route::patch('/cliente/ubicacion', [PedidoController::class, 'updateClientLocation'])->name('cliente.location.update');
Route::get('/carrito', [PedidoController::class, 'cart'])->name('carrito');
Route::delete('/carrito/{item}', [PedidoController::class, 'removeFromCart'])->name('carrito.remove');
Route::post('/carrito/confirmar', [PedidoController::class, 'confirmCart'])->name('carrito.confirm');
Route::patch('/pedidos/{pedido}/estado', [PedidoController::class, 'updateStatus'])->name('pedidos.status');

Route::post('/login', [LoginController::class, 'login'])->name('login.post');
Route::get('/auth/google', [GoogleController::class, 'redirect'])->name('google.login');
Route::get('/auth/google/callback', [GoogleController::class, 'callback'])->name('google.callback');
Route::get('/auth/google/complete-register', [GoogleController::class, 'completeRegisterForm'])->name('google.complete.form');
Route::post('/auth/google/complete-register', [GoogleController::class, 'completeRegister'])->name('google.complete.register');
Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

Route::get('/verificar/{token}', [ClienteController::class, 'verificar']);