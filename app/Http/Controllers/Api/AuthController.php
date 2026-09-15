<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Usuario;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class AuthController extends Controller
{
    public function registro(Request $request)
    {
        $datos = $request->validate([
            'nombre' => 'required|string|max:255',
            'correo' => [
                'required',
                'email',
                Rule::unique('Usuario', 'Email'),
            ],
            'contrasena' => 'required|min:6',
        ]);

        $usuario = Usuario::create([
            'Email' => $datos['correo'],
            'Nombre_de_Usuario' => $datos['nombre'],
            'Contraseña' => Hash::make($datos['contrasena']),
            'Tipo_Usuario' => 'cliente',
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

        $usuario = Usuario::where('Email', $datos['correo'])->first();

        if (! $usuario || ! Hash::check($datos['contrasena'], $usuario->{'Contraseña'})) {
            return response()->json(['mensaje' => 'Credenciales inválidas'], 401);
        }

        $token = $usuario->createToken('token-app')->plainTextToken;

        return response()->json(['usuario' => $usuario, 'token' => $token]);
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();
        return response()->json(['mensaje' => 'Sesión cerrada']);
    }
}
