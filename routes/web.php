<?php

use App\Http\Controllers\ClienteController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\GoogleController;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\LocalController;
use App\Http\Controllers\PedidoController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\RegistrationVerificationController;
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
    if (session('tipo_usuario') === 'administrador' && session('usuario_id')) {
        return redirect()->route('dashboard.admin');
    }

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
                'subpedido.Monto_Total as Total_Local',
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

Route::get('/dashboard-admin', function (Request $request) {
    abort_unless(session('tipo_usuario') === 'administrador' && session('usuario_id'), 403);

    $seccion = $request->query('seccion', 'adhesiones');
    abort_unless(in_array($seccion, ['adhesiones', 'usuarios', 'reportes', 'resenas'], true), 404);

    $usuarios = collect();
    foreach ([
        ['tabla' => 'cliente', 'tipo' => 'Cliente', 'tipo_ruta' => 'cliente', 'clave' => 'CI', 'nombre' => 'Nombre'],
        ['tabla' => 'comercio', 'tipo' => 'Local', 'tipo_ruta' => 'local', 'clave' => 'RUT', 'nombre' => 'Nombre_Comercio'],
        ['tabla' => 'repartidor', 'tipo' => 'Repartidor', 'tipo_ruta' => 'repartidor', 'clave' => 'CI', 'nombre' => 'Nombre'],
    ] as $cuenta) {
        if (! Schema::hasTable($cuenta['tabla'])) {
            continue;
        }

        foreach (DB::table($cuenta['tabla'])->get() as $registro) {
            $usuarios->push((object) [
                'tipo' => $cuenta['tipo'],
                'identificador' => $registro->{$cuenta['clave']} ?? 'Sin asignar',
                'nombre' => trim(($registro->{$cuenta['nombre']} ?? '').' '.($registro->Apellido ?? '')) ?: 'Sin nombre',
                'email' => $registro->Email_Usuario ?? 'Sin correo',
                'tipo_ruta' => $cuenta['tipo_ruta'],
                'estado' => $registro->Estado_Cuenta ?? 'Activa',
            ]);
        }
    }

    $totalUsuarios = $usuarios->count();
    $usuariosActivos = $usuarios->filter(fn ($usuario) => strtolower($usuario->estado) === 'activa')->count();
    $busqueda = trim($request->query('q', ''));
    if ($busqueda !== '') {
        $usuarios = $usuarios->filter(fn ($usuario) => str_contains(mb_strtolower($usuario->nombre.' '.$usuario->email.' '.$usuario->identificador), mb_strtolower($busqueda)));
    }

    $solicitudes = collect();
    foreach ([
        'local' => ['tabla' => 'comercio', 'nombre' => 'Nombre_Comercio', 'identificador' => 'RUT'],
        'repartidor' => ['tabla' => 'repartidor', 'nombre' => 'Nombre', 'identificador' => 'CI'],
    ] as $tipo => $cuenta) {
        if (! Schema::hasTable($cuenta['tabla']) || ! Schema::hasColumn($cuenta['tabla'], 'Estado_Adhesion')) {
            continue;
        }

        foreach (DB::table($cuenta['tabla'])->where('Estado_Adhesion', 'Pendiente')->get() as $solicitud) {
            $solicitudes->push((object) [
                'tipo' => $tipo,
                'nombre_tipo' => $tipo === 'local' ? 'Local' : 'Repartidor',
                'nombre' => trim(($solicitud->{$cuenta['nombre']} ?? '').' '.($solicitud->Apellido ?? '')) ?: $cuenta['nombre'],
                'identificador' => $solicitud->{$cuenta['identificador']} ?? 'Sin asignar',
                'email' => $solicitud->Email_Usuario,
            ]);
        }
    }

    $reportes = Schema::hasTable('reporte') ? DB::table('reporte')->orderByDesc('ID_Reporte')->get() : collect();
    $resenas = Schema::hasTable('reseña') ? DB::table('reseña')->orderByDesc('ID')->get() : collect();
    $resenasPublicadas = $resenas->filter(fn ($resena) => strtoupper((string) ($resena->Estado_Moderacion ?? 'Visible')) !== 'RETIRADA')->count();
    $notificaciones = [
        'adhesiones' => $solicitudes->count(),
        'usuarios' => $usuariosActivos,
        'reportes' => $reportes->count(),
        'resenas' => $resenasPublicadas,
    ];

    return view('Administrador.DashboardAdministrador', [
        'seccion' => $seccion,
        'usuarios' => $usuarios,
        'busqueda' => $busqueda,
        'solicitudes' => $solicitudes,
        'reportes' => $reportes,
        'resenas' => $resenas,
        'totalUsuarios' => $totalUsuarios,
        'notificaciones' => $notificaciones,
    ]);
})->name('dashboard.admin');

Route::patch('/admin/adhesiones/{tipo}/{email}', [AdminController::class, 'updateAdhesion'])
    ->name('admin.adhesiones.update');
Route::patch('/admin/usuarios/{tipo}/{email}', [AdminController::class, 'updateUserStatus'])
    ->name('admin.usuarios.estado');
Route::patch('/admin/reportes/{report}', [AdminController::class, 'updateReport'])
    ->name('admin.reportes.update');
Route::patch('/admin/resenas/{review}', [AdminController::class, 'moderateReview'])
    ->name('admin.resenas.moderate');

Route::get('/dashboard-repartidor', function () {
    $email = session('email');
    $ciRepartidor = session('usuario_id');

    if (! $ciRepartidor && $email) {
        $ciRepartidor = DB::table('repartidor')->where('Email_Usuario', $email)->value('CI');
    }

    if (session('tipo_usuario') !== 'repartidor' && $email) {
        $esRepartidor = DB::table('repartidor')->where('Email_Usuario', $email)->exists();
        if ($esRepartidor) {
            $ciRepartidor = $ciRepartidor ?: DB::table('repartidor')->where('Email_Usuario', $email)->value('CI');
            session(['tipo_usuario' => 'repartidor', 'usuario_id' => $ciRepartidor]);
        }
    }

    abort_unless(session('tipo_usuario') === 'repartidor' || ($email && $ciRepartidor), 403, 'No tienes permisos para acceder al dashboard del repartidor.');
    abort_unless($ciRepartidor, 403, 'El repartidor no está registrado.');
    $ubicacionRepartidor = Schema::hasColumn('repartidor', 'latitud')
        && Schema::hasColumn('repartidor', 'longitud')
        ? DB::table('repartidor')
            ->where('CI', $ciRepartidor)
            ->first(['latitud', 'longitud'])
        : null;

    $pedidosListosQuery = DB::table('subpedido')
        ->join('pedido', 'subpedido.N_Pedido', '=', 'pedido.N_Pedido')
        ->join('detalle_de_pedido', 'subpedido.N_Subpedido', '=', 'detalle_de_pedido.N_Subpedido')
        ->join('producto', 'detalle_de_pedido.ID_Producto', '=', 'producto.ID_Producto')
        ->join('comercio', 'subpedido.RUT_Comercio', '=', 'comercio.RUT')
        ->leftJoin('cliente', 'pedido.CI_Cliente', '=', 'cliente.CI')
        ->where('subpedido.Estado', 'listo')
        ->whereNotExists(function ($subQuery) {
            $subQuery->from('subpedido as otros_subpedidos')
                ->whereColumn('otros_subpedidos.N_Pedido', 'pedido.N_Pedido')
                ->where(function ($estadoQuery) {
                    $estadoQuery->whereNull('otros_subpedidos.Estado')
                        ->orWhere('otros_subpedidos.Estado', '<>', 'listo');
                });
        })
        ->where(function ($query) {
            $query->whereNull('pedido.CI_Repartidor')->orWhere('pedido.CI_Repartidor', '');
        });

    if (Schema::hasTable('pedido_repartidor_estado')) {
        $pedidosListosQuery->whereNotExists(function ($subQuery) {
            $subQuery->from('pedido_repartidor_estado')
                ->whereColumn('pedido_repartidor_estado.N_Pedido', 'pedido.N_Pedido')
                ->whereIn('pedido_repartidor_estado.Estado', ['aceptado', 'rechazado']);
        });
    }

    $columnasEntrega = [
        'pedido.N_Pedido', 'pedido.CI_Cliente', 'pedido.Metodo_de_pago',
        'pedido.Monto_Total as Total', 'pedido.Ubicacion as Direccion_Envio',
        'pedido.Fecha', 'pedido.Hora', 'subpedido.N_Subpedido', 'subpedido.RUT_Comercio',
        'subpedido.Estado as Estado_Subpedido', 'producto.Nombre_Producto', 'producto.Precio',
        'detalle_de_pedido.Cantidad', 'comercio.Nombre_Comercio',
        'cliente.Nombre as Nombre_Cliente', 'cliente.Apellido as Apellido_Cliente',
        'cliente.Email_Usuario as Correo_Cliente', 'cliente.Teléfono as Telefono_Cliente',
    ];

    $agruparPedidos = static function ($lineas) {
        return $lineas->groupBy('N_Pedido')->map(function ($productos) {
            $pedido = $productos->first();
            $pedido->locales = $productos->groupBy('N_Subpedido')->map(function ($productosLocal) {
                $local = $productosLocal->first();
                $local->productos = $productosLocal;

                return $local;
            })->values();

            return $pedido;
        })->values();
    };

    $pedidosListos = $agruparPedidos($pedidosListosQuery
        ->select($columnasEntrega)
        ->distinct()
        ->orderByDesc('pedido.N_Pedido')
        ->get());

    $miEntrega = $agruparPedidos(DB::table('subpedido')
        ->join('pedido', 'subpedido.N_Pedido', '=', 'pedido.N_Pedido')
        ->join('detalle_de_pedido', 'subpedido.N_Subpedido', '=', 'detalle_de_pedido.N_Subpedido')
        ->join('producto', 'detalle_de_pedido.ID_Producto', '=', 'producto.ID_Producto')
        ->join('comercio', 'subpedido.RUT_Comercio', '=', 'comercio.RUT')
        ->leftJoin('cliente', 'pedido.CI_Cliente', '=', 'cliente.CI')
        ->where('pedido.CI_Repartidor', $ciRepartidor)
        ->where('subpedido.Estado', 'en_reparto')
        ->select($columnasEntrega)
        ->distinct()
        ->orderByDesc('pedido.N_Pedido')
        ->get());

    return view('Repartidor.DashboardRepartidor', [
        'pedidosListos' => $pedidosListos,
        'miEntrega' => $miEntrega,
        'nombreUsuario' => session('email'),
        'tipoUsuario' => 'Repartidor',
        'latitudRepartidor' => $ubicacionRepartidor?->latitud,
        'longitudRepartidor' => $ubicacionRepartidor?->longitud,
    ]);
})->name('dashboard.repartidor');

Route::patch('/repartidor/ubicacion', [RepartidorController::class, 'updateLocation'])->name('repartidor.ubicacion');
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
Route::get('/registro/verificar', [RegistrationVerificationController::class, 'show'])->name('registration.verify.form');
Route::post('/registro/verificar', [RegistrationVerificationController::class, 'verify'])->middleware('throttle:10,10')->name('registration.verify');
Route::post('/registro/verificar/reenviar', [RegistrationVerificationController::class, 'resend'])->middleware('throttle:3,10')->name('registration.verify.resend');
Route::get('/mi-cuenta', [ClienteController::class, 'profile'])->name('cliente.profile');
Route::get('/mi-perfil/editar', [ProfileController::class, 'edit'])->name('profile.edit');
Route::patch('/mi-perfil', [ProfileController::class, 'update'])->name('profile.update');
Route::post('/productos', [LocalController::class, 'storeProducto'])->name('productos.store');
Route::patch('/comercio/estado', [LocalController::class, 'updateStatus'])->name('comercio.status');
Route::patch('/productos/{producto}', [LocalController::class, 'updateProducto'])->name('productos.update');
Route::delete('/productos/{producto}', [LocalController::class, 'destroyProducto'])->name('productos.destroy');
Route::post('/carrito/agregar', [PedidoController::class, 'addToCart'])->name('carrito.add');
Route::get('/cliente/ubicacion', [PedidoController::class, 'clientLocationForm'])->name('cliente.location.form');
Route::patch('/cliente/ubicacion', [PedidoController::class, 'updateClientLocation'])->name('cliente.location.update');
Route::get('/carrito', [PedidoController::class, 'cart'])->name('carrito');
Route::get('/mis-pedidos', [PedidoController::class, 'orderHistory'])->name('cliente.pedidos');
Route::delete('/carrito/{item}', [PedidoController::class, 'removeFromCart'])->name('carrito.remove');
Route::post('/carrito/confirmar', [PedidoController::class, 'confirmCart'])->name('carrito.confirm');
Route::patch('/pedidos/{pedido}/estado', [PedidoController::class, 'updateStatus'])->name('pedidos.status');

Route::post('/login', [LoginController::class, 'login'])->name('login.post');
Route::get('/auth/google', [GoogleController::class, 'redirect'])->name('google.login');
Route::get('/auth/google/callback', [GoogleController::class, 'callback'])->name('google.callback');
Route::get('/auth/google/complete-register', [GoogleController::class, 'completeRegisterForm'])->name('google.complete.form');
Route::post('/auth/google/complete-register', [GoogleController::class, 'completeRegister'])->name('google.complete.register');
Route::post('/logout', [LoginController::class, 'logout'])->name('logout');
