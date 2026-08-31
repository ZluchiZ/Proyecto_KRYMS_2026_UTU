<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class RepartidorController extends Controller
{
    public function store(Request $request)
{
    $validated = $request->validate([
        'cirepartidor' => 'required|string|max:8',
        'nombrerepartidor' => 'required|string|max:255',
        'apellidorepartidor' => 'required|string|max:255',
        'emailrepartidor' => 'required|email|max:255|unique:usuarios,correo',
        'numrepartidor' => 'required|string|max:9',
        'passwordrepartidor' => 'required|string|min:8',
        'passwordrepartidor2' => 'required|string|same:password',
        'nacimientorepartidor' => 'required|date',
    ]);

    try {
        DB::table('usuarios')->insert([
            'nombre' => $validated['nombrerepartidor'].' '.$validated['apellidorepartidor'],
            'correo' => $validated['emailrepartidor'],
            'contrasena' => bcrypt($validated['passwordrepartidor']),
            'created_at' => now(),
            'updated_at' => now(),
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