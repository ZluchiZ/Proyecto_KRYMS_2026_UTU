<?php

namespace App\Http\Controllers;

use App\Services\EmailVerificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class ClienteController extends Controller
{
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