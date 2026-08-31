<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class LocalController extends Controller
{
    public function store(Request $request)
{
    $validated = $request->validate([
        'rut' => 'required|numeric|max:12',
        'cedula' => 'required|numeric|max:8',
        'nombre' => 'required|string|max:255',
        'direccion' => 'required|string|max:255',
        'correolocal' => 'required|email|max:255|unique:usuarios,correo',
        'Numerocuental' => 'required|numeric|max:14',
        'passwordlocal' => 'required|string|min:8',
        'passwordlocal2' => 'required|string|same:password',
    ]);

    try {
        DB::table('local')->insert([
            'nombre' => $validated['nombrelocal'],
            'correo' => $validated['correolocal'],
            'contrasena' => bcrypt($validated['passwordlocal']),
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