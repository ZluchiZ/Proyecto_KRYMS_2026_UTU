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

        $comercio = DB::table('Comercio')
            ->where('RUT', session('usuario_id'))
            ->where('Email_Usuario', session('email'))
            ->first(['RUT', 'Email_Usuario', 'Nombre_Comercio']);

        abort_unless($comercio, 403);

        DB::table('Producto')->insert([
                'RUT_Comercio' => $comercio->RUT,
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

    public function destroyProducto(int $producto): \Illuminate\Http\RedirectResponse
    {
        abort_unless(session('tipo_usuario') === 'comercio' && session('usuario_id') && session('email'), 403);

        $eliminado = DB::table('Producto')
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
            'rut' => 'required|string|max:20',
            'nombre' => 'required|string|max:150',
            'direccion' => 'nullable|string|max:255',
            'logo' => 'nullable|string|max:255',
            'correo' => [
                'required',
                'email',
                'max:255',
                Rule::unique('Usuario', 'Email'),
            ],
            'contrasena' => 'required|string|min:8|confirmed',
        ], [
            'correo.unique' => 'Este correo ya está registrado.',
        ]);

        try {
            $password = bcrypt($validated['contrasena']);

            DB::transaction(function () use ($validated, $password) {
                DB::table('Usuario')->insert([
                    'Email' => $validated['correo'],
                    'Nombre_de_Usuario' => $validated['nombre'],
                    'Contraseña' => $password,
                    'Tipo_Usuario' => 'comercio',
                ]);

                DB::table('Comercio')->insert([
                    'Email_Usuario' => $validated['correo'],
                    'Contraseña' => $password,
                    'RUT' => $validated['rut'],
                    'Nombre_Comercio' => $validated['nombre'],
                    'Dirección' => $validated['direccion'] ?? null,
                    'Logo' => $validated['logo'] ?? null,
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