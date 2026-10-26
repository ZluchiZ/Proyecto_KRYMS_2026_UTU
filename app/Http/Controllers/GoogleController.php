<?php

namespace App\Http\Controllers;

use App\Exceptions\RegistrationMailDeliveryException;
use App\Services\RegistrationVerificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
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

    public function completeRegister(Request $request, RegistrationVerificationService $verification)
    {
        $email = session('google_email');
        abort_unless($email, 403);
        $request->merge(['email' => $email]);

        $request->validate([
            'cedula' => ['required', 'string', 'max:8', 'unique:cliente,CI'],
            'email' => Rule::unique('usuario', 'Email'),
            'nombre' => ['required', 'string', 'max:255'],
            'apellido' => ['required', 'string', 'max:255'],
            'telefono' => ['required', 'string', 'max:9'],
            'nacimiento' => ['required', 'date'],
        ]);

        try {
            $sent = $verification->start('cliente', $email, [
                'cedula' => $request->cedula,
                'name' => $request->nombre,
                'apellido' => $request->apellido,
                'telefono' => $request->telefono,
                'nacimiento' => $request->nacimiento,
                'password' => Hash::make(\Illuminate\Support\Str::random(40)),
                'google_registration' => true,
            ]);
        } catch (RegistrationMailDeliveryException $exception) {
            report($exception);

            return back()
                ->withInput($request->except(['password']))
                ->withErrors(['email' => 'No se pudo enviar el código. Revisá la configuración del correo e intentá nuevamente.']);
        }

        session()->forget(['google_email', 'google_name']);

        return redirect()
            ->route('registration.verify.form', ['email' => $email])
            ->with('success', $sent
                ? 'Te enviamos un código de verificación para completar el registro.'
                : 'Ya hay un registro pendiente para ese correo. Ingresá el código recibido o solicitá uno nuevo.');
    }
}
