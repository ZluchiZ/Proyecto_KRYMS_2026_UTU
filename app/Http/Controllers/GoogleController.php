<?php

namespace App\Http\Controllers;

<<<<<<< HEAD
use App\Services\EmailVerificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
=======
use App\Exceptions\RegistrationMailDeliveryException;
use App\Services\RegistrationVerificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
>>>>>>> 2454183e5acfdf3e4c0d75ce3f8717a428fe5a8f
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
<<<<<<< HEAD
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

=======
>>>>>>> 2454183e5acfdf3e4c0d75ce3f8717a428fe5a8f
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

<<<<<<< HEAD
    public function completeRegister(Request $request)
    {
        $validated = $request->validate([
            'cedula' => ['required', 'string', 'max:8', 'unique:cliente,CI'],
=======
    public function completeRegister(Request $request, RegistrationVerificationService $verification)
    {
        $email = session('google_email');
        abort_unless($email, 403);
        $request->merge(['email' => $email]);

        $request->validate([
            'cedula' => ['required', 'string', 'max:8', 'unique:cliente,CI'],
            'email' => Rule::unique('usuario', 'Email'),
>>>>>>> 2454183e5acfdf3e4c0d75ce3f8717a428fe5a8f
            'nombre' => ['required', 'string', 'max:255'],
            'apellido' => ['required', 'string', 'max:255'],
            'telefono' => ['required', 'string', 'max:9'],
            'nacimiento' => ['required', 'date'],
        ]);

<<<<<<< HEAD
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
=======
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
>>>>>>> 2454183e5acfdf3e4c0d75ce3f8717a428fe5a8f
    }
}
