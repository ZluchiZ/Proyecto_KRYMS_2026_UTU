<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Validation\Rule;

class LocalController extends Controller
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

    public function updateStatus(Request $request): \Illuminate\Http\RedirectResponse
    {
        abort_unless(session('tipo_usuario') === 'comercio' && session('usuario_id') && session('email'), 403);

        $validated = $request->validate([
            'abierto' => 'required|boolean',
        ]);

        $actualizado = DB::table('comercio')
            ->where('Email_Usuario', session('email'))
            ->update(['Abierto' => $request->boolean('abierto')]);

        abort_unless($actualizado, 404);

        return redirect()
            ->route('dashboard.local')
            ->with('success', $request->boolean('abierto') ? 'El local está abierto.' : 'El local está cerrado.');
    }

    public function storeProducto(Request $request)
    {
        abort_unless(session('tipo_usuario') === 'comercio' && session('usuario_id'), 403);

        $validated = $request->validate([
            'nombre' => 'required|string|max:255',
            'precio' => 'required|numeric|min:0|max:99999999.99',
            'categoria' => [
                'required',
                Rule::in([
                    'Restaurantes',
                    'Supermercados',
                    'Farmacia',
                    'Kioscos',
                    'Bebidas',
                    'Mascotas',
                    'Otros',
                    'Supermercado',
                    'Ferretería',
                    'Rotisería',
                ]),
            ],
            'descripcion' => 'required|string|max:1000',
            'imagen_url' => 'required|url|max:2048',
            'disponible' => 'nullable|boolean',
        ]);

        $comercio = DB::table('comercio')
            ->where('Email_Usuario', session('email'))
            ->first(['RUT', 'Email_Usuario', 'Nombre_Comercio']);

        abort_unless($comercio, 403);

        $identificadorComercio = $comercio->RUT ?? session('usuario_id');
        abort_unless($identificadorComercio, 403, 'Este comercio no tiene un RUT asociado.');

        DB::table('producto')->insert([
                'RUT_Comercio' => $identificadorComercio,
                'Nombre_Producto' => $validated['nombre'],
                'Precio' => $validated['precio'],
                'Categoria' => $validated['categoria'],
                'Foto_Producto' => $validated['imagen_url'],
                'Disponible' => $request->boolean('disponible'),
        ]);

        return redirect()
            ->route('dashboard.local')
            ->with('success', 'Producto añadido correctamente.');
    }

    public function updateProducto(Request $request, int $producto): \Illuminate\Http\RedirectResponse
    {
        abort_unless(session('tipo_usuario') === 'comercio' && session('usuario_id') && session('email'), 403);

        $validated = $request->validate([
            'precio' => 'required|numeric|min:0|max:99999999.99',
            'descuento' => 'required|numeric|min:0|max:100',
            'disponible' => 'required|boolean',
        ]);

        $comercio = DB::table('comercio')
            ->where('Email_Usuario', session('email'))
            ->first(['RUT']);

        abort_unless($comercio, 403);

        $identificadorComercio = $comercio->RUT ?? session('usuario_id');
        abort_unless($identificadorComercio, 403, 'Este comercio no tiene un RUT asociado.');

        $productoQuery = DB::table('producto')
            ->where('ID_Producto', $producto)
            ->where('RUT_Comercio', $identificadorComercio);

        abort_unless($productoQuery->exists(), 404);

        $productoQuery->update([
            'Precio' => $validated['precio'],
            'Descuento_Porcentaje' => $validated['descuento'],
            'Disponible' => $request->boolean('disponible'),
        ]);

        return redirect()
            ->route('dashboard.local')
            ->with('success', 'Producto actualizado correctamente.');
    }

    public function destroyProducto(int $producto): \Illuminate\Http\RedirectResponse
    {
        abort_unless(session('tipo_usuario') === 'comercio' && session('usuario_id') && session('email'), 403);

        $eliminado = DB::table('producto')
            ->where('ID_Producto', $producto)
            ->where('RUT_Comercio', session('usuario_id'))
            ->delete();

        abort_unless($eliminado, 404);

        return redirect()
            ->route('dashboard.local')
            ->with('success', 'Producto eliminado correctamente.');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'rut' => [
                'nullable',
                'string',
                'max:20',
                Rule::unique('comercio', 'RUT'),
            ],
            'nombre' => 'required|string|max:150',
            'nombre_dueno' => 'required|string|max:100',
            'cedula_dueno' => 'required|string|max:20',
            'horario' => ['required', 'string', 'max:255', 'regex:/^([01]\d|2[0-3]):[0-5]\d hrs - ([01]\d|2[0-3]):[0-5]\d hrs$/'],
            'direccion' => 'nullable|string|max:255',
            'logo' => 'nullable|string|max:255',
            'correo' => [
                'required',
                'email',
                'max:255',
                Rule::unique('usuario', 'Email'),
            ],
            'contrasena' => 'required|string|min:8|confirmed',
        ], [
            'rut.unique' => 'Este RUT ya está registrado. Puedes dejarlo vacío o ingresar otro.',
            'correo.unique' => 'Este correo ya está registrado.',
        ]);

        try {
            $password = bcrypt($validated['contrasena']);
            $coordenadas = $this->geocode($validated['direccion'] ?? null);

            DB::transaction(function () use ($validated, $password, $coordenadas) {
                DB::table('usuario')->insert([
                    'Email' => $validated['correo'],
                    'Nombre_de_Usuario' => $validated['nombre'],
                    'Contraseña' => $password,
                    'Tipo_Usuario' => 'comercio',
                ]);

                DB::table('comercio')->insert([
                    'Email_Usuario' => $validated['correo'],
                    'Contraseña' => $password,
                    'RUT' => $validated['rut'] ?? null,
                    'Nombre_Comercio' => $validated['nombre'],
                    'Nombre_dueño' => $validated['nombre_dueno'],
                    'CI_Dueño' => $validated['cedula_dueno'],
                    'Horario' => $validated['horario'],
                    'Dirección' => $validated['direccion'] ?? null,
                    'Logo' => $validated['logo'] ?? null,
                    'latitud' => $coordenadas['latitud'],
                    'longitud' => $coordenadas['longitud'],
                ]);
            });

            return redirect()
                ->route('login')
                ->with('success', 'Registro exitoso. Ya puedes iniciar sesión.');
        } catch (\Throwable $e) {
            return back()
                ->withInput()
                ->with('error', 'No se pudo guardar el local: '.$e->getMessage());
        }
    }

    public function verificar($token)
    {
        return redirect()
            ->route('login')
            ->with('error', 'La verificación por correo no está disponible con la configuración actual.');
    }
}