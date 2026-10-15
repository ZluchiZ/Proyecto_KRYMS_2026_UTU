<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;

class LoginController extends Controller
{
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required'
        ]);

        if (Schema::hasTable('administrador')) {
            $administrador = DB::table('administrador')
                ->where('Email_Usuario', $request->email)
                ->first();

            if ($administrador && Hash::check($request->password, $administrador->{'Contraseña'})) {
                $request->session()->regenerate();
                session([
                    'email' => $administrador->Email_Usuario,
                    'tipo_usuario' => 'administrador',
                    'usuario_id' => $administrador->Email_Usuario,
                ]);

                return redirect()->route('dashboard.admin');
            }
        }

        $cuentas = [
            ['tabla' => 'cliente', 'tipo' => 'cliente', 'id' => 'CI'],
            ['tabla' => 'comercio', 'tipo' => 'comercio', 'id' => 'RUT'],
            ['tabla' => 'repartidor', 'tipo' => 'repartidor', 'id' => 'CI'],
        ];

        foreach ($cuentas as $cuenta) {
            $usuario = DB::table($cuenta['tabla'])
                ->where('Email_Usuario', $request->email)
                ->first();

            if ($usuario && Hash::check($request->password, $usuario->{'Contraseña'})) {
                $identificador = $usuario->{$cuenta['id']} ?? null;

                if ($cuenta['tipo'] === 'comercio' && ! $identificador) {
                    $identificador = $usuario->id;
                }

                $request->session()->regenerate();
                session([
                    'email' => $usuario->Email_Usuario,
                    'tipo_usuario' => $cuenta['tipo'],
                    'usuario_id' => $identificador,
                ]);

                return match ($cuenta['tipo']) {
                    'repartidor' => redirect()->route('dashboard.repartidor'),
                    'comercio' => redirect()->route('dashboard.local'),
                    default => redirect()->route('home'),
                };
            }
        }

        return back()
            ->withInput($request->only('email'))
            ->with('error', 'Correo o contraseña incorrectos.');
    }

    public function logout(Request $request)
    {
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('home');
    }
}