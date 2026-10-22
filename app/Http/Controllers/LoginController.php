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

        if (Schema::hasTable('usuario')) {
            $usuarioAdministrador = DB::table('usuario')
                ->where('Email', $request->email)
                ->first();

            if ($usuarioAdministrador
                && in_array(strtolower((string) ($usuarioAdministrador->Tipo_Usuario ?? '')), ['admin', 'administrador'], true)
                && Hash::check($request->password, $usuarioAdministrador->{'Contraseña'})) {
                $request->session()->regenerate();
                session([
                    'email' => $usuarioAdministrador->Email,
                    'tipo_usuario' => 'administrador',
                    'usuario_id' => $usuarioAdministrador->Email,
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
<<<<<<< HEAD
                if (Schema::hasTable('email_verifications')
                    && DB::table('email_verifications')
                        ->where('email', $request->email)
                        ->whereNull('verified_at')
                        ->exists()) {
                    return back()
                        ->withInput($request->only('email'))
                        ->with('error', 'Debes confirmar tu correo antes de iniciar sesión.');
                }

=======
>>>>>>> 2454183e5acfdf3e4c0d75ce3f8717a428fe5a8f
                if (strtolower($usuario->Estado_Cuenta ?? 'activa') !== 'activa') {
                    return back()
                        ->withInput($request->only('email'))
                        ->with('error', 'Esta cuenta está suspendida. Contacta al administrador.');
                }

                if ($cuenta['tipo'] !== 'cliente') {
                    $estadoAdhesion = strtolower($usuario->Estado_Adhesion ?? 'aceptada');

                    if ($estadoAdhesion !== 'aceptada') {
                        $mensaje = $estadoAdhesion === 'rechazada'
                            ? 'La solicitud de adhesión fue rechazada.'
                            : 'La solicitud de adhesión está pendiente de aprobación.';

                        if ($estadoAdhesion === 'rechazada' && $usuario->Motivo_Rechazo) {
                            $mensaje .= ' Motivo: '.$usuario->Motivo_Rechazo;
                        }

                        return back()
                            ->withInput($request->only('email'))
                            ->with('error', $mensaje);
                    }
                }

                $identificador = $usuario->{$cuenta['id']} ?? null;

                if ($cuenta['tipo'] === 'comercio' && ! $identificador) {
                    $identificador = $usuario->id ?? null;
                }

                if ($cuenta['tipo'] === 'comercio' && ! $identificador) {
                    return back()
                        ->withInput($request->only('email'))
                        ->with('error', 'Este comercio no tiene un RUT o identificador asociado.');
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