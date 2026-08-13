<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Usuario;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function registro(Request $request)
    {
        $datos = $request->validate([
            'nombre' => 'required|string|max:255',
            'correo' => 'required|email|unique:usuarios,correo',
            'contrasena' => 'required|min:6',
        ]);

        $usuario = Usuario::create([
            'nombre' => $datos['nombre'],
            'correo' => $datos['correo'],
            'contrasena' => Hash::make($datos['contrasena']),
        ]);

        $token = $usuario->createToken('token-app')->plainTextToken;

        return response()->json(['usuario' => $usuario, 'token' => $token], 201);
    }

    public function login(Request $request)
    {
        $datos = $request->validate([
            'correo' => 'required|email',
            'contrasena' => 'required',
        ]);

        if (!Auth::attempt(['email' => $datos['correo'], 'password' => $datos['contrasena']])) {
            return response()->json(['mensaje' => 'Credenciales inválidas'], 401);
        }

        $usuario = Auth::user();
        $token = $usuario->createToken('token-app')->plainTextToken;

        return response()->json(['usuario' => $usuario, 'token' => $token]);
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();
        return response()->json(['mensaje' => 'Sesión cerrada']);
    }
}
