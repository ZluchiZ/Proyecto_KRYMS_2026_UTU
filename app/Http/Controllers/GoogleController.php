<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
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

        $cliente = DB::table('Cliente')->where('Email_Usuario', $email)->first();

        if ($cliente) {
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
        $request->validate([
            'cedula' => ['required', 'string', 'max:8', 'unique:Cliente,CI'],
            'nombre' => ['required', 'string', 'max:255'],
            'apellido' => ['required', 'string', 'max:255'],
            'telefono' => ['required', 'string', 'max:9'],
            'nacimiento' => ['required', 'date'],
        ]);

        $email = session('google_email');
        abort_unless($email, 403);

        $password = bcrypt(Str::random(24));

        DB::transaction(function () use ($request, $email, $password) {
            $usuarioExiste = DB::table('Usuario')->where('Email', $email)->exists();

            if (! $usuarioExiste) {
                DB::table('Usuario')->insert([
                    'Email' => $email,
                    'Nombre_de_Usuario' => $request->nombre,
                    'Contraseña' => $password,
                    'Tipo_Usuario' => 'cliente',
                ]);
            }

            $clienteExiste = DB::table('Cliente')->where('Email_Usuario', $email)->exists();

            if (! $clienteExiste) {
                DB::table('Cliente')->insert([
                    'CI' => $request->cedula,
                    'Email_Usuario' => $email,
                    'Contraseña' => $password,
                    'Fecha_nacimiento' => $request->nacimiento,
                    'Nombre' => $request->nombre,
                    'Apellido' => $request->apellido,
                    'Teléfono' => $request->telefono,
                ]);
            }
        });

        session()->regenerate();
        session([
            'email' => $email,
            'tipo_usuario' => 'cliente',
            'usuario_id' => $request->cedula,
        ]);

        session()->forget(['google_email', 'google_name']);

        return redirect()->route('home');
    }
}
