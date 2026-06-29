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

        $cliente = DB::table('Cliente')
            ->where('email', $request->email)
            ->first();

        if (!$cliente) {
            return back()->with('error', 'Correo o contraseña incorrectos.');
        }

        if ($cliente->EmailVerificado == 0) {
            return back()->with('error', 'Debes verificar tu correo antes de iniciar sesión.');
        }

        if (!Hash::check($request->password, $cliente->Contrasena)) {
            return back()->with('error', 'Correo o contraseña incorrectos.');
        }

        session([
            'email' => $cliente->email,
            'password' => $request->password,
        ]);

        return redirect()->route('home');
    }
}