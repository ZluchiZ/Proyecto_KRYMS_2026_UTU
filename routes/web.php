<?php

use App\Http\Controllers\ClienteController;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\LocalController;
use App\Http\Controllers\RepartidorController;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;

Route::get('/test-cliente', function () {
    return DB::table('usuario')->get();
});

Route::get('/login', function () {
    return view('Login');
})->name('login');

Route::get('/', function () {
    $nombreUsuario = null;

    if (session('tipo_usuario') && session('usuario_id')) {
        $tablas = [
            'cliente' => 'cliente',
            'comercio' => 'comercio',
            'repartidor' => 'repartidor',
        ];
        $tabla = $tablas[session('tipo_usuario')] ?? null;

        if ($tabla) {
            $usuario = DB::table($tabla)->find(session('usuario_id'));
            $nombreUsuario = $usuario?->nombre;

            if ($usuario && session('tipo_usuario') === 'repartidor') {
                $nombreUsuario .= ' '.$usuario->apellido;
            }
        }
    }

    return view('home', compact('nombreUsuario'));
})->name('home');

Route::get('/dashboard-local', function () {
    abort_unless(session('tipo_usuario') === 'comercio' && session('usuario_id'), 403);

    $comercio = DB::table('comercio')
        ->where('id', session('usuario_id'))
        ->first(['rut', 'cedula']);

    abort_unless($comercio, 403);

    $identificadorComercio = $comercio->rut ?: $comercio->cedula;

    $productos = DB::table('Producto')
        ->where('RUT_Comercio', $identificadorComercio)
        ->orderByDesc('ID_Producto')
        ->get();

    return view('Local.DashboardLocal', compact('productos'));
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

Route::post('/login', [LoginController::class, 'login'])->name('login.post');
Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

Route::get('/verificar/{token}', [ClienteController::class, 'verificar']);