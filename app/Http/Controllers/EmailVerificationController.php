<?php

namespace App\Http\Controllers;

use App\Services\EmailVerificationService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class EmailVerificationController extends Controller
{
    public function show()
    {
        $email = session('pending_email_verification');

        if (! $email) {
            return redirect()->route('register');
        }

        $verification = DB::table('email_verifications')
            ->where('email', $email)
            ->first();

        if (! $verification) {
            return redirect()
                ->route('register')
                ->with('error', 'No encontramos un registro pendiente. Volvé a completar el formulario para continuar.');
        }

        if ($verification->verified_at) {
            return redirect()->route('login');
        }

        $resendAvailableAt = $verification->last_sent_at
            ? Carbon::parse($verification->last_sent_at)->addSeconds(200)
            : now();

        return view('auth.verify-email', [
            'email' => $email,
            'resendAvailableAt' => $resendAvailableAt,
        ]);
    }

    public function verify(Request $request, EmailVerificationService $verificationService)
    {
        $validated = $request->validate([
            'code' => ['required', 'regex:/^[0-9]{6}$/'],
        ], [
            'code.regex' => 'El código debe tener exactamente 6 dígitos.',
        ]);

        $email = session('pending_email_verification');

        if (! $email) {
            return redirect()->route('register');
        }

        $result = $verificationService->verify($email, $validated['code']);

        if ($result !== 'verified') {
            $message = match ($result) {
                'expired' => 'El código venció. Solicitá uno nuevo para continuar.',
                'locked' => 'Se alcanzó el máximo de intentos. Solicitá un código nuevo.',
                'conflict' => 'No se pudo completar el registro porque el correo o identificador ya está registrado. Contactá al soporte.',
                default => 'El código ingresado no es válido.',
            };

            return back()->withInput()->with('error', $message);
        }

        $pendingUser = $request->session()->pull('pending_email_verification_user');
        $request->session()->forget('pending_email_verification');

        if ($pendingUser) {
            $request->session()->regenerate();
            $request->session()->put($pendingUser);
            $request->session()->forget(['google_email', 'google_name']);

            return redirect()
                ->route('home')
                ->with('success', 'Correo verificado correctamente. Tu cuenta ya está activa.');
        }

        return redirect()
            ->route('login')
            ->with('success', 'Correo verificado correctamente. Ya podés iniciar sesión.');
    }

    public function resend(EmailVerificationService $verificationService)
    {
        $email = session('pending_email_verification');

        if (! $email) {
            return redirect()->route('register');
        }

        try {
            $remaining = $verificationService->resend($email);
        } catch (\Throwable $exception) {
            report($exception);

            return back()->with(
                'error',
                'Gmail rechazó el envío. Revisá que MAIL_USERNAME sea la cuenta remitente y MAIL_PASSWORD su contraseña de aplicación; luego ejecutá php artisan config:clear e intentá de nuevo.'
            );
        }

        if ($remaining < 0) {
            return redirect()->route('register');
        }

        if ($remaining > 0) {
            return back()->with('error', "Podés solicitar otro código dentro de {$remaining} segundos.");
        }

        return back()->with('success', 'Te enviamos un código nuevo. El anterior dejó de ser válido.');
    }
}
