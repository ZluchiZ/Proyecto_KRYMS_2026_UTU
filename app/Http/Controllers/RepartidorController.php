<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class RepartidorController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'cedula' => 'required|string|max:8',
            'correo' => 'required|email|max:255|unique:repartidor,correo',
            'nombre' => 'required|string|max:255',
            'apellido' => 'required|string|max:255',
            'telefono' => 'required|string|max:9',
            'fecha_nacimiento' => 'required|date',
            'contrasena' => 'required|string|min:8|confirmed',
        ]);

        try {
            DB::table('repartidor')->insert([
                'cedula' => $validated['cedula'],
                'correo' => $validated['correo'],
                'nombre' => $validated['nombre'],
                'apellido' => $validated['apellido'],
                'telefono' => $validated['telefono'],
                'fecha_nacimiento' => $validated['fecha_nacimiento'],
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
                ->with('error', 'No se pudo guardar el repartidor: '.$e->getMessage());
        }
    }

    public function verificar($token)
    {
        return redirect()
            ->route('login')
            ->with('error', 'La verificación por correo no está disponible con la configuración actual.');
    }
}