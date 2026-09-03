<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class LocalController extends Controller
{
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
                Rule::unique('usuarios', 'correo'),
                Rule::unique('local', 'correo'),
                Rule::unique('repartidor', 'correo'),
            ],
            'contrasena' => 'required|string|min:8|confirmed',
        ], [
            'correo.unique' => 'Este correo ya está registrado.',
        ]);

        try {
            DB::table('local')->insert([
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