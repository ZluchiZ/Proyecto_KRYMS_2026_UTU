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

        $this->validateSearchRequest($request);
        $busqueda = trim($request->string('q')->toString());
        $productos = $this->searchProductsQuery($request, $busqueda)
            ->when($identificadorComercio, function ($query) use ($identificadorComercio) {
                $query->where('producto.RUT_Comercio', $identificadorComercio);
            })
            ->orderByDesc('producto.ID_Producto')
            ->get();

        $locales = DB::table('comercio')
            ->whereNotNull('RUT')
            ->orderBy('Nombre_Comercio')
            ->get(['RUT', 'Nombre_Comercio', 'Logo', 'Dirección', 'Horario', 'Abierto']);
        $busquedaActiva = $busqueda !== '' || $this->hasSearchFilters($request);
        $localesBusqueda = $busquedaActiva
            ? $this->searchStoresQuery($request, $busqueda)->get()
            : collect();
        $categoriasBusqueda = DB::table('producto')
            ->whereNotNull('Categoria')
            ->where('Categoria', '!=', '')
            ->distinct()
            ->orderBy('Categoria')
            ->pluck('Categoria');

        $localesSuscriptos = collect();
        if (Schema::hasColumn('comercio', 'Suscripcion_Activa')) {
            $localesSuscriptosQuery = DB::table('comercio')
                ->whereNotNull('RUT')
                ->where('Suscripcion_Activa', true)
                ->orderBy('Nombre_Comercio');
            if (Schema::hasColumn('comercio', 'Estado_Adhesion')) {
                $localesSuscriptosQuery->where('Estado_Adhesion', 'Aceptada');
            }
            if (Schema::hasColumn('comercio', 'Estado_Cuenta')) {
                $localesSuscriptosQuery->where('Estado_Cuenta', 'Activa');
            }
            $localesSuscriptos = $localesSuscriptosQuery
                ->get(['RUT', 'Nombre_Comercio', 'Logo', 'Dirección', 'Horario', 'Abierto']);
        }

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
            'localesBusqueda' => $localesBusqueda,
            'categoriasBusqueda' => $categoriasBusqueda,
            'localesSuscriptos' => $localesSuscriptos,
            'cantidadCarrito' => $cantidadCarrito,
        ]);
    }

    public function searchProducts(Request $request)
    {
        $this->validateSearchRequest($request);
        $busqueda = trim($request->string('q')->toString());
        $productos = $this->searchProductsQuery($request, $busqueda)
            ->orderByDesc('producto.ID_Producto')
            ->get();
        $locales = $this->searchStoresQuery($request, $busqueda)->get();

        return response()
            ->view('partials.home-search-results', [
                'productos' => $productos,
                'locales' => $locales,
                'busqueda' => $busqueda,
                'esClienteRegistrado' => session('tipo_usuario') === 'cliente' && session('usuario_id'),
            ])
            ->header('Cache-Control', 'no-store');
    }

    private function validateSearchRequest(Request $request): void
    {
        $request->validate([
            'q' => ['nullable', 'string', 'max:100'],
            'categoria' => ['nullable', 'string', 'max:100'],
            'min_price' => ['nullable', 'numeric', 'min:0'],
            'max_price' => ['nullable', 'numeric', 'min:0', 'gte:min_price'],
            'offers' => ['nullable', 'boolean'],
            'available' => ['nullable', 'boolean'],
        ]);
    }

    private function searchProductsQuery(Request $request, string $busqueda)
    {
        $query = DB::table('producto')
            ->leftJoin('comercio', 'producto.RUT_Comercio', '=', 'comercio.RUT')
            ->select('producto.*', 'comercio.Nombre_Comercio', 'comercio.Logo', 'comercio.Dirección', 'comercio.Abierto');

        if ($busqueda !== '') {
            $query->where(function ($query) use ($busqueda) {
                $this->applyProductTextSearch($query, $busqueda);
                $query->orWhere('comercio.Nombre_Comercio', 'like', "%{$busqueda}%");
            });
        }

        $this->applySearchFilters($query, $request);

        return $query;
    }

    private function searchStoresQuery(Request $request, string $busqueda)
    {
        $query = DB::table('comercio')
            ->whereNotNull('comercio.RUT')
            ->select('comercio.RUT', 'comercio.Nombre_Comercio', 'comercio.Logo', 'comercio.Dirección', 'comercio.Horario', 'comercio.Abierto');

        if ($busqueda !== '') {
            $query->where(function ($query) use ($busqueda, $request) {
                $query->where('comercio.Nombre_Comercio', 'like', "%{$busqueda}%")
                    ->orWhereExists(function ($products) use ($busqueda, $request) {
                        $products->selectRaw('1')
                            ->from('producto')
                            ->whereColumn('producto.RUT_Comercio', 'comercio.RUT')
                            ->where(function ($products) use ($busqueda) {
                                $this->applyProductTextSearch($products, $busqueda);
                            });
                        $this->applySearchFilters($products, $request);
                    });
            });
        }

        if ($this->hasSearchFilters($request)) {
            $query->whereExists(function ($products) use ($request) {
                $products->selectRaw('1')
                    ->from('producto')
                    ->whereColumn('producto.RUT_Comercio', 'comercio.RUT');
                $this->applySearchFilters($products, $request);
            });
        }

        return $query->orderBy('comercio.Nombre_Comercio');
    }

    private function applyProductTextSearch($query, string $busqueda): void
    {
        $query->where('producto.Nombre_Producto', 'like', "%{$busqueda}%")
            ->orWhere('producto.Categoria', 'like', "%{$busqueda}%");
    }

    private function applySearchFilters($query, Request $request): void
    {
        if ($request->filled('categoria')) {
            $query->where('producto.Categoria', $request->string('categoria')->toString());
        }

        if ($request->filled('min_price')) {
            $query->whereRaw(
                'COALESCE(producto.Precio, 0) * (1 - COALESCE(producto.Descuento_Porcentaje, 0) / 100.0) >= CAST(? AS DECIMAL(10, 2))',
                [(float) $request->input('min_price')],
            );
        }

        if ($request->filled('max_price')) {
            $query->whereRaw(
                'COALESCE(producto.Precio, 0) * (1 - COALESCE(producto.Descuento_Porcentaje, 0) / 100.0) <= CAST(? AS DECIMAL(10, 2))',
                [(float) $request->input('max_price')],
            );
        }

        if ($request->boolean('offers')) {
            $query->where('producto.Descuento_Porcentaje', '>', 0);
        }

        if ($request->boolean('available')) {
            $query->where('producto.Disponible', true)
                ->where('comercio.Abierto', true);
        }
    }

    private function hasSearchFilters(Request $request): bool
    {
        return $request->filled('categoria')
            || $request->filled('min_price')
            || $request->filled('max_price')
            || $request->boolean('offers')
            || $request->boolean('available');
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

    public function courierProfile(string $ci)
    {
        $repartidor = DB::table('repartidor')
            ->where('CI', $ci)
            ->first(['CI', 'Nombre', 'Apellido', 'Teléfono']);

        abort_unless($repartidor, 404);
        $pedidoConRepartidor = DB::table('pedido')
            ->where('CI_Cliente', session('usuario_id'))
            ->where(function ($query) use ($repartidor): void {
                $query->where('CI_Repartidor', $repartidor->CI);
                if (Schema::hasTable('pedido_repartidor_estado')) {
                    $query->orWhereExists(function ($assignmentQuery) use ($repartidor): void {
                        $assignmentQuery->selectRaw('1')
                            ->from('pedido_repartidor_estado')
                            ->whereColumn('pedido_repartidor_estado.N_Pedido', 'pedido.N_Pedido')
                            ->where('pedido_repartidor_estado.CI_Repartidor', $repartidor->CI)
                            ->where('pedido_repartidor_estado.Estado', 'aceptado');
                    });
                }
            })
            ->exists();
        abort_unless($pedidoConRepartidor, 404);

        $consultaResenas = DB::table('reseña')
            ->join('cliente', 'reseña.CI_Cliente', '=', 'cliente.CI')
            ->where('reseña.CI_Repartidor', $repartidor->CI)
            ->whereNotNull('reseña.Puntuacion')
            ->where(function ($query): void {
                $query->whereNull('reseña.Estado_Moderacion')
                    ->orWhere('reseña.Estado_Moderacion', '<>', 'Retirada');
            });
        $totalCalificaciones = (clone $consultaResenas)->count('reseña.ID');
        $promedioCalificaciones = (clone $consultaResenas)->avg('reseña.Puntuacion');
        $resenas = $consultaResenas
            ->select('reseña.ID', 'reseña.Puntuacion', 'reseña.Comentario', 'cliente.Nombre as Nombre_Cliente')
            ->orderByDesc('reseña.ID')
            ->paginate(10);

        return view('courier-profile', [
            'repartidor' => $repartidor,
            'resenas' => $resenas,
            'totalCalificaciones' => $totalCalificaciones,
            'promedioCalificaciones' => $promedioCalificaciones,
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