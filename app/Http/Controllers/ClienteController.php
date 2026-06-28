<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use App\Mail\VerificacionCorreo;

class ClienteController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'cedula' => 'required|string|max:8',
            'nombre' => 'required|string|max:255',
            'apellido' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:Cliente,email',
            'Numero' => 'required|string|max:9',
            'password' => 'required|string|min:8',
            'password2' => 'required|string|same:password',
            'nacimiento' => 'required|date',
        ]);

        // Generar token único
        $token = Str::random(64);

        try {

            DB::table('Cliente')->insert([
                'cedula' => $validated['cedula'],
                'Nombre' => $validated['nombre'],
                'Apellido' => $validated['apellido'],
                'email' => $validated['email'],
                'Telefono' => $validated['Numero'],

                // Contraseña cifrada
                'Contrasena' => bcrypt($validated['password']),

                // No es recomendable guardar este campo.
                // Si tu tabla aún lo tiene, déjalo temporalmente.
                'RepetirContrasena' => bcrypt($validated['password2']),

                'FechaNacimiento' => $validated['nacimiento'],

                // Nuevas columnas
                'EmailVerificado' => 0,
                'TokenVerificacion' => $token,
            ]);

            // Enviar correo de verificación
            Mail::to($validated['email'])
                ->send(new VerificacionCorreo($token));

            return redirect()
                ->route('login')
                ->with(
                    'success',
                    'Registro exitoso. Revisa tu correo para verificar tu cuenta.'
                );

        } catch (\Throwable $e) {

            return back()
                ->withInput()
                ->with('error', 'No se pudo guardar el cliente: '.$e->getMessage());
        }
    }

    public function verificar($token)
    {
        $cliente = DB::table('Cliente')
            ->where('TokenVerificacion', $token)
            ->first();

        if (!$cliente) {
            return "El enlace de verificación no es válido o ya fue utilizado.";
        }

        DB::table('Cliente')
            ->where('TokenVerificacion', $token)
            ->update([
                'EmailVerificado' => 1,
                'TokenVerificacion' => null,
            ]);

        return redirect()
            ->route('login')
            ->with('success', 'Correo verificado correctamente. Ya puedes iniciar sesión.');
    }
}