<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class RepartidorController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'cedula' => 'required|string|max:8',
            'correo' => [
                'required',
                'email',
                'max:255',
                Rule::unique('Usuario', 'Email'),
            ],
            'nombre' => 'required|string|max:255',
            'apellido' => 'required|string|max:255',
            'telefono' => 'required|string|max:9',
            'fecha_nacimiento' => 'required|date',
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
                    'Tipo_Usuario' => 'repartidor',
                ]);

                DB::table('Repartidor')->insert([
                    'CI' => $validated['cedula'],
                    'Email_Usuario' => $validated['correo'],
                    'Contraseña' => $password,
                    'Nombre' => $validated['nombre'],
                    'Apellido' => $validated['apellido'],
                    'Teléfono' => $validated['telefono'],
                ]);
            });

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