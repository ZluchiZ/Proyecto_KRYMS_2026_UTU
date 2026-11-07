<?php

namespace App\Http\Controllers;

use App\Exceptions\RegistrationMailDeliveryException;
use App\Services\RegistrationVerificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class ClienteController extends Controller
{
    public function store(Request $request, RegistrationVerificationService $verification)
    {
        $validated = $request->validate([
            'cedula' => [
                'required',
                'string',
                'max:8',
                Rule::unique('cliente', 'CI'),
            ],
            'nombre' => 'required|string|max:255',
            'apellido' => 'required|string|max:255',
            'email' => [
                'required',
                'email:rfc',
                'regex:/^[^\s@]+@[^\s@]+\.[^\s@]+$/',
                'max:255',
                Rule::unique('usuario', 'Email'),
            ],
            'Numero' => 'required|string|max:9',
            'password' => [
                'required',
                'string',
                'min:8',
                'regex:/[A-Z]/',
                'regex:/[a-z]/',
                'regex:/[0-9]/',
                'regex:/[^A-Za-z0-9\s]/',
            ],
            'password2' => 'required|string|same:password',
            'nacimiento' => ['required', 'date', 'before_or_equal:'.now()->subYears(18)->toDateString()],
        ], [
            'cedula.unique' => 'Esta cédula ya está registrada.',
            'email.unique' => 'Este correo ya está registrado.',
            'email.regex' => 'Ingresá un correo válido, por ejemplo nombre@gmail.com.',
            'password.min' => 'La contraseña debe tener al menos 8 caracteres.',
            'password.regex' => 'La contraseña debe incluir al menos una mayúscula, una minúscula, un número y un símbolo.',
            'nacimiento.before_or_equal' => 'Debes tener al menos 18 años para registrarte.',
        ]);

        try {
            $sent = $verification->start('cliente', $validated['email'], [
                'cedula' => $validated['cedula'],
                'name' => $validated['nombre'],
                'apellido' => $validated['apellido'],
                'telefono' => $validated['Numero'],
                'nacimiento' => $validated['nacimiento'],
                'password' => $validated['password'],
            ]);
        } catch (RegistrationMailDeliveryException $exception) {
            report($exception);

            return back()
                ->withInput($request->except(['password', 'password2']))
                ->with('error', 'No se pudo enviar el código. Revisá la configuración del correo e intentá nuevamente.');
        }

        return redirect()
            ->route('registration.verify.form', ['email' => $validated['email']])
            ->with('success', $sent
                ? 'Te enviamos un código de verificación. Ingresalo para completar el registro.'
                : 'Ya hay un registro pendiente para ese correo. Ingresá el código recibido o solicitá uno nuevo.');
    }

    public function profile()
    {
        abort_unless(session('tipo_usuario') === 'cliente' && session('usuario_id'), 403);

        $cliente = DB::table('cliente')
            ->where('CI', session('usuario_id'))
            ->first();

        abort_unless($cliente, 404);

        return view('Cliente.Perfil', [
            'cliente' => $cliente,
        ]);
    }
}
