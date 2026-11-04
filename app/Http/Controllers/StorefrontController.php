<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class StorefrontController extends Controller
{
    public function loginForm()
    {
        return view('Login');
    }

    public function home(Request $request)
    {
        if (session('tipo_usuario') === 'repartidor' && session('usuario_id')) {
            return redirect()->route('dashboard.repartidor');
        }

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
    }

    public function searchProducts(Request $request)
    {
        $busqueda = trim($request->string('q')->toString());
        $productos = DB::table('producto')
            ->leftJoin('comercio', 'producto.RUT_Comercio', '=', 'comercio.RUT')
            ->select('producto.*', 'comercio.Nombre_Comercio', 'comercio.Logo', 'comercio.Dirección', 'comercio.Abierto')
            ->where(function ($query) use ($busqueda) {
                $query->where('producto.Nombre_Producto', 'like', "%{$busqueda}%")
                    ->orWhere('producto.Categoria', 'like', "%{$busqueda}%")
                    ->orWhere('comercio.Nombre_Comercio', 'like', "%{$busqueda}%");
            })
            ->orderByDesc('producto.ID_Producto')
            ->get();

        return response()
            ->view('partials.home-search-results', [
                'productos' => $productos,
                'busqueda' => $busqueda,
                'esClienteRegistrado' => session('tipo_usuario') === 'cliente' && session('usuario_id'),
            ])
            ->header('Cache-Control', 'no-store');
    }

    public function restaurants()
    {
        $locales = DB::table('comercio')
            ->whereNotNull('RUT')
            ->orderBy('Nombre_Comercio')
            ->get(['RUT', 'Nombre_Comercio', 'Logo', 'Dirección', 'Horario', 'Abierto']);

        return view('restaurants', [
            'locales' => $locales,
            'esClienteRegistrado' => session('tipo_usuario') === 'cliente' && session('usuario_id'),
        ]);
    }

    public function products()
    {
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
    }

    public function localProfile(string $rut)
    {
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

        $consultaResenas = DB::table('reseña')
            ->join('cliente', 'reseña.CI_Cliente', '=', 'cliente.CI')
            ->where('reseña.RUT_Comercio', $comercio->RUT)
            ->where(function ($query): void {
                $query->whereNull('reseña.Estado_Moderacion')
                    ->orWhere('reseña.Estado_Moderacion', '<>', 'Retirada');
            });
        $totalResenas = (clone $consultaResenas)->count('reseña.ID');
        $promedioResenas = (clone $consultaResenas)->avg('reseña.Puntuacion');
        $resenas = $consultaResenas
            ->select('reseña.ID', 'reseña.Puntuacion', 'reseña.Comentario', 'cliente.Nombre as Nombre_Cliente')
            ->orderByDesc('reseña.ID')
            ->paginate(10)
            ->withQueryString();
        $miResena = null;

        if (session('tipo_usuario') === 'cliente' && session('usuario_id')) {
            $miResena = DB::table('reseña')
                ->where('CI_Cliente', session('usuario_id'))
                ->where('RUT_Comercio', $comercio->RUT)
                ->first();
        }

        return view('local-profile', [
            'comercio' => $comercio,
            'productos' => $productos,
            'esClienteRegistrado' => session('tipo_usuario') === 'cliente' && session('usuario_id'),
            'resenas' => $resenas,
            'totalResenas' => $totalResenas,
            'promedioResenas' => $promedioResenas,
            'miResena' => $miResena,
        ]);
    }

    public function registerForm()
    {
        return view('register');
    }

    public function commerceRegistrationForm()
    {
        return view('registerLocal');
    }

    public function courierRegistrationForm()
    {
        return view('registerRepartidor');
    }
}