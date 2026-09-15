<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class LoginController extends Controller
{
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required'
        ]);

        $cuentas = [
            ['tabla' => 'Cliente', 'tipo' => 'cliente', 'id' => 'CI'],
            ['tabla' => 'Comercio', 'tipo' => 'comercio', 'id' => 'RUT'],
            ['tabla' => 'Repartidor', 'tipo' => 'repartidor', 'id' => 'CI'],
        ];

        foreach ($cuentas as $cuenta) {
            $usuario = DB::table($cuenta['tabla'])
                ->where('Email_Usuario', $request->email)
                ->first();

            if ($usuario && Hash::check($request->password, $usuario->{'Contraseña'})) {
<<<<<<< HEAD
                $identificador = $usuario->{$cuenta['id']} ?? null;

                if ($cuenta['tipo'] === 'comercio' && ! $identificador) {
                    $identificador = $usuario->id;
                }

=======
>>>>>>> a391eb105a2f6f2cbe09e9877773fb0cef2cdc50
                $request->session()->regenerate();
                session([
                    'email' => $usuario->Email_Usuario,
                    'tipo_usuario' => $cuenta['tipo'],
<<<<<<< HEAD
                    'usuario_id' => $identificador,
=======
                    'usuario_id' => $usuario->{$cuenta['id']},
>>>>>>> a391eb105a2f6f2cbe09e9877773fb0cef2cdc50
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