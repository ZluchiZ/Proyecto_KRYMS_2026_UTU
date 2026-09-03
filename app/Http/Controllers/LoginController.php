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
            ['tabla' => 'usuarios', 'tipo' => 'cliente'],
            ['tabla' => 'local', 'tipo' => 'local'],
            ['tabla' => 'repartidor', 'tipo' => 'repartidor'],
        ];

        foreach ($cuentas as $cuenta) {
            $usuario = DB::table($cuenta['tabla'])
                ->where('correo', $request->email)
                ->first();

            if ($usuario && Hash::check($request->password, $usuario->contrasena)) {
                session([
                    'email' => $usuario->correo,
                    'tipo_usuario' => $cuenta['tipo'],
                    'usuario_id' => $usuario->id,
                ]);

                return redirect()->route('home');
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