<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ClienteController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'cedula' => 'required|string|max:8',
            'nombre' => 'required|string|max:255',
            'apellido' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:usuario,mail',
            'Numero' => 'required|string|max:9',
            'password' => 'required|string|min:8',
            'password2' => 'required|string|same:password',
            'nacimiento' => 'required|date',
        ]);

        try {
            DB::table('usuario')->insert([
                'ci' => $validated['cedula'],
                'mail' => $validated['email'],
                'nombre' => $validated['nombre'],
                'apellido' => $validated['apellido'],
                'telefono' => $validated['Numero'],
                'fecha_nacimiento' => $validated['nacimiento'],
                'contrasena' => bcrypt($validated['password']),
                'repetir_contrasena' => bcrypt($validated['password']),
                'opciones' => $request->input('opciones'),
            ]);

            return redirect()
                ->route('login')
                ->with('success', 'Registro exitoso. Ya puedes iniciar sesión.');

        } catch (\Throwable $e) {

            return back()
                ->withInput()
                ->with('error', 'No se pudo guardar el cliente: '.$e->getMessage());
        }
    }

    public function verificar($token)
    {
        return redirect()
            ->route('login')
            ->with('error', 'La verificación por correo no está disponible con la configuración actual.');
    }
}