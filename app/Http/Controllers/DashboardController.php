<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class DashboardController extends Controller
{
    public function commerce()
    {
        abort_unless(session('tipo_usuario') === 'comercio' && session('usuario_id'), 403);

        $comercio = DB::table('comercio')
            ->where('Email_Usuario', session('email'))
            ->first(['Nombre_Comercio', 'Email_Usuario', 'RUT', 'Abierto']);

        abort_unless($comercio, 403);

        $productos = collect();
        $pedidos = collect();

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

        $consultaResenas = DB::table('reseña')
            ->join('cliente', 'reseña.CI_Cliente', '=', 'cliente.CI')
            ->where('reseña.RUT_Comercio', $comercio->RUT ?? '')
            ->where(function ($query): void {
                $query->whereNull('reseña.Estado_Moderacion')
                    ->orWhere('reseña.Estado_Moderacion', '<>', 'Retirada');
            });
        $totalResenas = (clone $consultaResenas)->count('reseña.ID');
        $promedioResenas = (clone $consultaResenas)->avg('reseña.Puntuacion');
        $resenas = $consultaResenas
            ->select('reseña.ID', 'reseña.Puntuacion', 'reseña.Comentario', 'reseña.Estado_Moderacion', 'cliente.Nombre as Nombre_Cliente')
            ->orderByDesc('reseña.ID')
            ->paginate(10);

        return view('Local.DashboardLocal', [
            'productos' => $productos,
            'pedidos' => $pedidos,
            'resenas' => $resenas,
            'totalResenas' => $totalResenas,
            'promedioResenas' => $promedioResenas,
            'comercioAbierto' => (bool) $comercio->Abierto,
            'nombreUsuario' => $comercio->Nombre_Comercio,
            'correoUsuario' => $comercio->Email_Usuario,
            'tipoUsuario' => 'Local',
        ]);
    }

    public function administrator(Request $request)
    {
        abort_unless(session('tipo_usuario') === 'administrador' && session('usuario_id'), 403);

        $seccion = $request->query('seccion', 'adhesiones');
        abort_unless(in_array($seccion, ['adhesiones', 'usuarios', 'reportes', 'resenas', 'recuperaciones'], true), 404);

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
        $recuperaciones = Schema::hasTable('password_recovery_requests')
            ? DB::table('password_recovery_requests')->where('status', 'pending')->orderBy('created_at')->get()
            : collect();
        $resenasPublicadas = $resenas->filter(fn ($resena) => strtoupper((string) ($resena->Estado_Moderacion ?? 'Visible')) !== 'RETIRADA')->count();
        $notificaciones = [
            'adhesiones' => $solicitudes->count(),
            'usuarios' => $usuariosActivos,
            'reportes' => $reportes->count(),
            'resenas' => $resenasPublicadas,
            'recuperaciones' => $recuperaciones->count(),
        ];

        return view('Administrador.DashboardAdministrador', [
            'seccion' => $seccion,
            'usuarios' => $usuarios,
            'busqueda' => $busqueda,
            'solicitudes' => $solicitudes,
            'reportes' => $reportes,
            'resenas' => $resenas,
            'recuperaciones' => $recuperaciones,
            'totalUsuarios' => $totalUsuarios,
            'notificaciones' => $notificaciones,
        ]);
    }

    public function courier()
    {
        $ciRepartidor = session('usuario_id');
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
            ->where('pedido.Estado', 'listo')
            ->where('subpedido.Estado', 'listo')
            ->whereNotIn('subpedido.Estado', ['omitido', 'rechazado', 'cancelado'])
            ->whereNotExists(function ($subQuery) {
                $subQuery->from('subpedido as otros_subpedidos')
                    ->whereColumn('otros_subpedidos.N_Pedido', 'pedido.N_Pedido')
                    ->whereNotIn('otros_subpedidos.Estado', ['omitido', 'rechazado', 'cancelado'])
                    ->where(function ($estadoQuery) {
                        $estadoQuery->whereNull('otros_subpedidos.Estado')
                            ->orWhere('otros_subpedidos.Estado', '<>', 'listo');
                    });
            })
            ->where(function ($query) {
                $query->whereNull('pedido.CI_Repartidor')->orWhere('pedido.CI_Repartidor', '');
            });

        if (Schema::hasTable('pedido_repartidor_estado')) {
            $pedidosListosQuery->whereNotExists(function ($subQuery) use ($ciRepartidor) {
                $subQuery->from('pedido_repartidor_estado')
                    ->whereColumn('pedido_repartidor_estado.N_Pedido', 'pedido.N_Pedido')
                    ->where('pedido_repartidor_estado.CI_Repartidor', (string) $ciRepartidor)
                    ->where('pedido_repartidor_estado.Estado', 'rechazado');
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
            ->select([...$columnasEntrega, 'pedido.Estado_Entrega'])
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
    }
}