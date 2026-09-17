<?php

namespace App\Http\Controllers;

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

        DB::transaction(function () use ($validated, $password) {
            DB::table('usuario')->insert([
                'Email' => $validated['email'],
                'Nombre_de_Usuario' => $validated['nombre'],
                'Contraseña' => $password,
                'Tipo_Usuario' => 'cliente',
            ]);

            DB::table('cliente')->insert([
                'CI' => $validated['cedula'],
                'Email_Usuario' => $validated['email'],
                'Contraseña' => $password,
                'Fecha_nacimiento' => $validated['nacimiento'],
                'Nombre' => $validated['nombre'],
                'Apellido' => $validated['apellido'],
                'Teléfono' => $validated['Numero'],
            ]);
        });

        return redirect()
            ->route('login')
            ->with('success', 'Registro exitoso. Ya puedes iniciar sesión.');
    } catch (\Throwable $e) {
        return back()
            ->withInput()
            ->with('error', 'No se pudo guardar el cliente: '.$e->getMessage());
    }
}

    public function verificar($token)
    {
        return redirect()
            ->route('login')
            ->with('error', 'La verificación por correo no está disponible con la configuración actual.');
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