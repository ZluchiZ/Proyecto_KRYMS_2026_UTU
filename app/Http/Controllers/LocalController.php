<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class LocalController extends Controller
{
    public function storeProducto(Request $request)
    {
        abort_unless(session('tipo_usuario') === 'comercio' && session('usuario_id'), 403);

        $validated = $request->validate([
            'nombre' => 'required|string|max:255',
            'precio' => 'required|numeric|min:0',
            'categoria' => 'required|string|max:100',
            'descripcion' => 'required|string|max:1000',
            'imagen_url' => 'required|url|max:2048',
            'disponible' => 'nullable|boolean',
        ]);

        $comercio = DB::table('comercio')
            ->where('id', session('usuario_id'))
            ->first(['rut', 'cedula', 'correo', 'nombre', 'direccion', 'logo', 'contrasena']);

        abort_unless($comercio, 403);

        $identificadorComercio = $comercio->rut ?: $comercio->cedula;
        $correoComercio = session('email');

        DB::transaction(function () use ($comercio, $identificadorComercio, $correoComercio, $validated, $request) {
            if (! DB::table('Usuario')->where('Email', $comercio->correo)->exists()) {
                DB::table('Usuario')->insert([
                    'Email' => $comercio->correo,
                    'Nombre_de_Usuario' => $comercio->nombre,
                    'Contraseña' => $comercio->contrasena,
                    'Tipo_Usuario' => 'comercio',
                ]);
            }

            if (! DB::table('Comercio')->where('RUT', $identificadorComercio)->exists()) {
                DB::table('Comercio')->insert([
                    'RUT' => $identificadorComercio,
                    'Email_Usuario' => $comercio->correo,
                    'Nombre_Comercio' => $comercio->nombre,
                    'Dirección' => $comercio->direccion,
                    'Logo' => $comercio->logo,
                    'CI_Dueño' => $comercio->cedula,
                ]);
            }

            DB::table('Producto')->insert([
                'RUT_Comercio' => $identificadorComercio,
                'Correo_Comercio' => $correoComercio,
                'Nombre_Producto' => $validated['nombre'],
                'Precio' => $validated['precio'],
                'Categoria' => $validated['categoria'],
                'Descripcion' => $validated['descripcion'],
                'Foto_Producto' => $validated['imagen_url'],
                'Disponible' => $request->boolean('disponible'),
            ]);
        });

        return redirect()
            ->route('dashboard.local')
            ->with('success', 'Producto añadido correctamente.');
    }

    public function destroyProducto(int $producto): \Illuminate\Http\RedirectResponse
    {
        abort_unless(session('tipo_usuario') === 'comercio' && session('usuario_id') && session('email'), 403);

        $eliminado = DB::table('Producto')
            ->where('ID_Producto', $producto)
            ->where('Correo_Comercio', session('email'))
            ->delete();

        abort_unless($eliminado, 404);

        return redirect()
            ->route('dashboard.local')
            ->with('success', 'Producto eliminado correctamente.');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'rut' => 'nullable|string|max:12',
            'cedula' => 'required|string|max:8',
            'nombre' => 'required|string|max:255',
            'direccion' => 'required|string|max:255',
            'logo' => 'required|string|max:255',
            'numero_cuenta' => 'required|string|max:20',
            'correo' => [
                'required',
                'email',
                'max:255',
                Rule::unique('cliente', 'correo'),
                Rule::unique('comercio', 'correo'),
                Rule::unique('repartidor', 'correo'),
            ],
            'contrasena' => 'required|string|min:8|confirmed',
        ], [
            'correo.unique' => 'Este correo ya está registrado.',
        ]);

        try {
            DB::table('comercio')->insert([
                'rut' => $validated['rut'] ?? null,
                'cedula' => $validated['cedula'],
                'nombre' => $validated['nombre'],
                'direccion' => $validated['direccion'],
                'logo' => $validated['logo'],
                'numero_cuenta' => $validated['numero_cuenta'],
                'correo' => $validated['correo'],
                'contrasena' => bcrypt($validated['contrasena']),
                'created_at' => now(),
                'updated_at' => now(),
            ]);

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