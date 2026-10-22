<?php

namespace App\Http\Controllers;

use App\Exceptions\RegistrationMailDeliveryException;
use App\Services\RegistrationVerificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;

class RegistrationVerificationController extends Controller
{
    public function show(Request $request)
    {
        $email = $request->query('email');
        $pending = DB::table('registration_verifications')
            ->where('email', $email)
            ->first(['email', 'expires_at']);

        abort_unless($pending, 404, 'No encontramos un registro pendiente para ese correo.');

        return view('auth.registration-verification', [
            'email' => $pending->email,
            'expired' => now()->greaterThan($pending->expires_at),
        ]);
    }

    public function verify(Request $request, RegistrationVerificationService $verification)
    {
        $validated = $request->validate([
            'email' => 'required|email',
            'code' => 'required|digits:6',
        ]);

        try {
            $account = $verification->verify($validated['email'], $validated['code']);
        } catch (HttpExceptionInterface $exception) {
            return back()
                ->withInput($request->only('email'))
                ->withErrors(['code' => $exception->getMessage()]);
        }

        if ($account['google_registration']) {
            $request->session()->regenerate();
            session([
                'email' => $validated['email'],
                'tipo_usuario' => 'cliente',
                'usuario_id' => $account['id'],
            ]);

            return redirect()->route('home')->with('success', 'Tu correo fue verificado y tu cuenta ya está activa.');
        }

        $message = $account['type'] === 'cliente'
            ? 'Correo verificado. Ya puedes iniciar sesión.'
            : 'Correo verificado. Tu solicitud quedó enviada y podrás iniciar sesión cuando el administrador apruebe tu adhesión.';

        return redirect()->route('login')->with('success', $message);
    }

    public function resend(Request $request, RegistrationVerificationService $verification)
    {
        $validated = $request->validate([
            'email' => 'required|email',
        ]);

        try {
            $verification->resend($validated['email']);
        } catch (HttpExceptionInterface $exception) {
            return back()
                ->withInput($request->only('email'))
                ->withErrors(['code' => $exception->getMessage()]);
        } catch (RegistrationMailDeliveryException $exception) {
            report($exception);
            Log::error('No se pudo reenviar un código de verificación de registro.', [
                'email' => $validated['email'],
            ]);

            return back()
                ->withInput($request->only('email'))
                ->withErrors(['code' => 'No se pudo enviar otro código. Revisá la configuración del correo e intentá nuevamente.']);
        }

        return back()
            ->withInput($request->only('email'))
            ->with('success', 'Te enviamos un nuevo código de verificación.');
    }
}
