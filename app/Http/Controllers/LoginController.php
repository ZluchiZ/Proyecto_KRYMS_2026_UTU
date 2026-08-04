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

        $cliente = DB::table('usuario')
            ->where('email', $request->email)
            ->first();

        if (!$cliente) {
            return back()->with('error', 'Correo o contraseña incorrectos.');
        }

        if (!Hash::check($request->password, $cliente->contrasena)) {
            return back()->with('error', 'Correo o contraseña incorrectos.');
        }

        session([
            'email' => $cliente->email,
        ]);

        return redirect()->route('home');
    }
}