<?php

namespace App\Http\Controllers;

<<<<<<< HEAD
use App\Services\EmailVerificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
=======
use App\Exceptions\RegistrationMailDeliveryException;
use App\Services\RegistrationVerificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
>>>>>>> 2454183e5acfdf3e4c0d75ce3f8717a428fe5a8f
use Illuminate\Validation\Rule;

class ClienteController extends Controller
{
<<<<<<< HEAD
    public function store(Request $request)
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
            'email',
            'max:255',
            Rule::unique('usuario', 'Email'),
        ],
        'Numero' => 'required|string|max:9',
        'password' => 'required|string|min:8',
        'password2' => 'required|string|same:password',
        'nacimiento' => 'required|date',
    ], [
        'cedula.unique' => 'Esta cédula ya está registrada.',
        'email.unique' => 'Este correo ya está registrado.',
    ]);

    try {
        $password = bcrypt($validated['password']);
        $request->session()->put('pending_email_verification', $validated['email']);

        try {
            app(EmailVerificationService::class)->start($validated['email'], [
                'type' => 'cliente',
                'data' => [
                    'cedula' => $validated['cedula'],
                    'nombre' => $validated['nombre'],
                    'apellido' => $validated['apellido'],
                    'telefono' => $validated['Numero'],
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
            ->with('success', 'Registro pendiente. Ingresá el código enviado para guardar y activar tu cuenta.');
    } catch (\Throwable $e) {
        report($e);

        return back()
            ->withInput()
            ->with('error', 'No se pudo iniciar el registro. Intentá nuevamente.');
    }
}
=======
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
                'email',
                'max:255',
                Rule::unique('usuario', 'Email'),
            ],
            'Numero' => 'required|string|max:9',
            'password' => 'required|string|min:8',
            'password2' => 'required|string|same:password',
            'nacimiento' => 'required|date',
        ], [
            'cedula.unique' => 'Esta cédula ya está registrada.',
            'email.unique' => 'Este correo ya está registrado.',
        ]);

        try {
            $sent = $verification->start('cliente', $validated['email'], [
                'cedula' => $validated['cedula'],
                'name' => $validated['nombre'],
                'apellido' => $validated['apellido'],
                'telefono' => $validated['Numero'],
                'nacimiento' => $validated['nacimiento'],
                'password' => Hash::make($validated['password']),
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
>>>>>>> 2454183e5acfdf3e4c0d75ce3f8717a428fe5a8f

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