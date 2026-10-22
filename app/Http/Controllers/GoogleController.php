<?php

namespace App\Http\Controllers;

use App\Services\EmailVerificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;

class GoogleController extends Controller
{
    public function redirect()
    {
        return Socialite::driver('google')->stateless()->redirect();
    }

    public function callback()
    {
        $googleUser = Socialite::driver('google')->stateless()->user();
        $email = $googleUser->getEmail();

        $cliente = DB::table('cliente')->where('Email_Usuario', $email)->first();

        if ($cliente) {
            $verificacionPendiente = Schema::hasTable('email_verifications')
                && DB::table('email_verifications')
                    ->where('email', $email)
                    ->whereNull('verified_at')
                    ->exists();

            if ($verificacionPendiente) {
                session([
                    'pending_email_verification' => $email,
                    'pending_email_verification_user' => [
                        'email' => $email,
                        'tipo_usuario' => 'cliente',
                        'usuario_id' => $cliente->CI,
                    ],
                ]);

                return redirect()
                    ->route('email-verification.show')
                    ->with('error', 'Debes confirmar tu correo antes de iniciar sesión.');
            }

            if (strtolower($cliente->Estado_Cuenta ?? 'activa') !== 'activa') {
                return redirect()
                    ->route('login')
                    ->with('error', 'Esta cuenta está suspendida. Contacta al administrador.');
            }

            session()->regenerate();
            session([
                'email' => $email,
                'tipo_usuario' => 'cliente',
                'usuario_id' => $cliente->CI,
            ]);

            return redirect()->route('home');
        }

        session()->put('google_email', $email);
        session()->put('google_name', $googleUser->getName() ?: 'Usuario Google');

        return redirect()->route('google.complete.form');
    }

    public function completeRegisterForm()
    {
        abort_unless(session('google_email'), 403);

        return view('auth.google-complete-register', [
            'email' => session('google_email'),
            'nombre' => session('google_name'),
        ]);
    }

    public function completeRegister(Request $request)
    {
        $validated = $request->validate([
            'cedula' => ['required', 'string', 'max:8', 'unique:cliente,CI'],
            'nombre' => ['required', 'string', 'max:255'],
            'apellido' => ['required', 'string', 'max:255'],
            'telefono' => ['required', 'string', 'max:9'],
            'nacimiento' => ['required', 'date'],
        ]);

        $email = session('google_email');
        abort_unless($email, 403);

        $password = bcrypt(Str::random(24));

        session([
            'pending_email_verification' => $email,
            'pending_email_verification_user' => [
                'email' => $email,
                'tipo_usuario' => 'cliente',
                'usuario_id' => $validated['cedula'],
            ],
        ]);

        try {
            app(EmailVerificationService::class)->start($email, [
                'type' => 'cliente',
                'allow_existing_user' => true,
                'data' => [
                    'cedula' => $validated['cedula'],
                    'nombre' => $validated['nombre'],
                    'apellido' => $validated['apellido'],
                    'telefono' => $validated['telefono'],
                    'nacimiento' => $validated['nacimiento'],
                    'password' => $password,
                ],
            ]);
        } catch (\Throwable $e) {
            report($e);

            return redirect()
                ->route('email-verification.show')
                ->with('error', 'No se pudo enviar el código. Revisá la configuración del correo y volvé a intentar el reenvío.');
        }

        return redirect()
            ->route('email-verification.show')
            ->with('success', 'Ingresá el código que enviamos a tu correo para terminar el registro.');
    }
}
