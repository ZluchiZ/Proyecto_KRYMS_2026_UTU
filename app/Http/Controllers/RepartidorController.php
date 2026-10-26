<?php

namespace App\Http\Controllers;

use App\Exceptions\RegistrationMailDeliveryException;
use App\Services\RegistrationVerificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\Rule;

class RepartidorController extends Controller
{
    public function store(Request $request, RegistrationVerificationService $verification)
    {
        $validated = $request->validate([
            'cedula' => ['required', 'string', 'max:8', Rule::unique('repartidor', 'CI')],
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
            $sent = $verification->start('repartidor', $validated['correo'], [
                'cedula' => $validated['cedula'],
                'name' => $validated['nombre'],
                'apellido' => $validated['apellido'],
                'telefono' => $validated['telefono'],
                'nacimiento' => $validated['fecha_nacimiento'],
                'password' => Hash::make($validated['contrasena']),
            ]);
        } catch (RegistrationMailDeliveryException $exception) {
            report($exception);

            return back()
                ->withInput($request->except(['contrasena', 'contrasena_confirmation']))
                ->with('error', 'No se pudo enviar el código. Revisá la configuración del correo e intentá nuevamente.');
        }

        return redirect()
            ->route('registration.verify.form', ['email' => $validated['correo']])
            ->with('success', $sent
                ? 'Te enviamos un código de verificación. Ingresalo para completar el registro.'
                : 'Ya hay un registro pendiente para ese correo. Ingresá el código recibido o solicitá uno nuevo.');
    }

    public function updateLocation(Request $request)
    {
        $repartidorId = session('usuario_id');
        $email = session('email');

        if (! $repartidorId && $email) {
            $repartidorId = DB::table('repartidor')
                ->where('Email_Usuario', $email)
                ->value('CI');
        }

        $esRepartidor = $repartidorId && DB::table('repartidor')
            ->where('CI', (string) $repartidorId)
            ->exists();

        abort_unless($esRepartidor, 403, 'Solo un repartidor autenticado puede compartir su ubicación.');
        session(['tipo_usuario' => 'repartidor', 'usuario_id' => (string) $repartidorId]);
        abort_unless(
            Schema::hasColumn('repartidor', 'latitud') && Schema::hasColumn('repartidor', 'longitud'),
            409,
            'La ubicación aún no está habilitada en la base de datos. Ejecutá las migraciones de Laravel.'
        );

        $validated = $request->validate([
            'latitud' => 'required|numeric|between:-90,90',
            'longitud' => 'required|numeric|between:-180,180',
        ]);

        DB::table('repartidor')
            ->where('CI', (string) $repartidorId)
            ->update([
                'latitud' => $validated['latitud'],
                'longitud' => $validated['longitud'],
            ]);

        return response()->json([
            'ok' => true,
            'latitud' => (float) $validated['latitud'],
            'longitud' => (float) $validated['longitud'],
        ]);
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

        $totalSubpedidos = DB::table('subpedido')->where('N_Pedido', $pedido)->count();
        $subpedidosListos = DB::table('subpedido')
            ->where('N_Pedido', $pedido)
            ->where('Estado', 'listo')
            ->count();
        abort_if(
            $totalSubpedidos === 0 || $subpedidosListos !== $totalSubpedidos,
            409,
            'El pedido solo se puede gestionar cuando todos los locales lo hayan finalizado.'
        );
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