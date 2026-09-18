<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\Rule;

class RepartidorController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'cedula' => 'required|string|max:8',
            'correo' => [
                'required',
                'email',
                'max:255',
                Rule::unique('usuario', 'Email'),
            ],
            'nombre' => 'required|string|max:255',
            'apellido' => 'required|string|max:255',
            'telefono' => 'required|string|max:9',
            'fecha_nacimiento' => 'required|date',
            'contrasena' => 'required|string|min:8|confirmed',
        ], [
            'correo.unique' => 'Este correo ya está registrado.',
        ]);

        try {
            $password = bcrypt($validated['contrasena']);

            DB::transaction(function () use ($validated, $password) {
                DB::table('usuario')->insert([
                    'Email' => $validated['correo'],
                    'Nombre_de_Usuario' => $validated['nombre'],
                    'Contraseña' => $password,
                    'Tipo_Usuario' => 'repartidor',
                ]);

                DB::table('repartidor')->insert([
                    'CI' => $validated['cedula'],
                    'Email_Usuario' => $validated['correo'],
                    'Contraseña' => $password,
                    'Nombre' => $validated['nombre'],
                    'Apellido' => $validated['apellido'],
                    'Teléfono' => $validated['telefono'],
                ]);
            });

            return redirect()
                ->route('login')
                ->with('success', 'Registro exitoso. Ya puedes iniciar sesión.');
        } catch (\Throwable $e) {
            return back()
                ->withInput()
                ->with('error', 'No se pudo guardar el repartidor: '.$e->getMessage());
        }
    }

    public function verificar($token)
    {
        return redirect()
            ->route('login')
            ->with('error', 'La verificación por correo no está disponible con la configuración actual.');
    }

    public function updatePedidoStatus(Request $request, int $pedido)
    {
        $email = session('email');
        $tipoUsuario = session('tipo_usuario');
        $repartidorId = session('usuario_id');

        if ($repartidorId && DB::table('repartidor')->where('CI', (string) $repartidorId)->exists()) {
            $tipoUsuario = 'repartidor';
        }

        if (! $repartidorId && $email) {
            $repartidorId = DB::table('repartidor')
                ->where('Email_Usuario', $email)
                ->value('CI');
        }

        if ($repartidorId && DB::table('repartidor')->where('CI', (string) $repartidorId)->exists()) {
            $tipoUsuario = 'repartidor';
            session([
                'tipo_usuario' => 'repartidor',
                'usuario_id' => (string) $repartidorId,
            ]);
        }

        if ($tipoUsuario !== 'repartidor' && $email) {
            $repartidorPorEmail = DB::table('repartidor')
                ->where('Email_Usuario', $email)
                ->first(['CI']);

            if ($repartidorPorEmail) {
                $repartidorId = $repartidorPorEmail->CI;
                $tipoUsuario = 'repartidor';
                session([
                    'tipo_usuario' => 'repartidor',
                    'usuario_id' => (string) $repartidorId,
                ]);
            }
        }

        $esRepartidorAutorizado = $tipoUsuario === 'repartidor' || ($email && $repartidorId && DB::table('repartidor')->where('CI', (string) $repartidorId)->exists());

        abort_unless($esRepartidorAutorizado, 403, 'No tienes permisos para gestionar este pedido.');
        abort_unless($repartidorId, 403, 'El repartidor no está registrado.');

        $validated = $request->validate([
            'estado' => 'required|in:aceptado,rechazado',
        ]);

        $pedidoActual = DB::table('pedido')
            ->where('N_Pedido', $pedido)
            ->first(['N_Pedido', 'CI_Repartidor', 'Estado']);

        abort_unless($pedidoActual, 404, 'El pedido no existe.');

        $decidido = false;

        if (Schema::hasTable('pedido_repartidor_estado')) {
            $decidido = DB::table('pedido_repartidor_estado')
                ->where('N_Pedido', $pedido)
                ->whereIn('Estado', ['aceptado', 'rechazado'])
                ->exists();
        }

        abort_if($decidido && $validated['estado'] === 'aceptado', 409, 'Este pedido ya fue aceptado o rechazado por otro repartidor.');

        if (Schema::hasTable('pedido_repartidor_estado')) {
            DB::table('pedido_repartidor_estado')
                ->updateOrInsert(
                    ['N_Pedido' => $pedido, 'CI_Repartidor' => $repartidorId],
                    ['Estado' => $validated['estado']]
                );
        }

        if ($validated['estado'] === 'aceptado') {
            abort_if($pedidoActual->CI_Repartidor !== null, 409, 'Este pedido ya tiene un repartidor asignado.');

            DB::table('pedido')
                ->where('N_Pedido', $pedido)
                ->update(['CI_Repartidor' => $repartidorId, 'Estado' => 'en_reparto']);

            DB::table('subpedido')
                ->where('N_Pedido', $pedido)
                ->update(['Estado' => 'en_reparto']);

            return redirect()->route('dashboard.repartidor')->with('success', 'Entrega aceptada.');
        }

        DB::table('pedido')
            ->where('N_Pedido', $pedido)
            ->update(['Estado' => 'rechazado']);

        DB::table('subpedido')
            ->where('N_Pedido', $pedido)
            ->update(['Estado' => 'rechazado']);

        return redirect()->route('dashboard.repartidor')->with('success', 'Pedido rechazado.');
    }
}