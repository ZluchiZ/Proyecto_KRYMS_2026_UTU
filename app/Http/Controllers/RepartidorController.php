<?php

namespace App\Http\Controllers;

use App\Exceptions\RegistrationMailDeliveryException;
<<<<<<< HEAD
=======
use App\Services\InAppNotificationService;
>>>>>>> 08917fc (Sistema de pedidos + Notificaciones + Ajustes en el repartidor (opciones para cambiar el estado del pedido e ingresar codigo de confirmacion de entrega))
use App\Services\RegistrationVerificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
<<<<<<< HEAD
=======
use Illuminate\Validation\ValidationException;
>>>>>>> 08917fc (Sistema de pedidos + Notificaciones + Ajustes en el repartidor (opciones para cambiar el estado del pedido e ingresar codigo de confirmacion de entrega))
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

<<<<<<< HEAD
    public function updatePedidoStatus(Request $request, int $pedido)
=======
    public function updatePedidoStatus(Request $request, int $pedido, InAppNotificationService $notifications)
>>>>>>> 08917fc (Sistema de pedidos + Notificaciones + Ajustes en el repartidor (opciones para cambiar el estado del pedido e ingresar codigo de confirmacion de entrega))
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

<<<<<<< HEAD
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
=======
        abort_unless(
            Schema::hasTable('pedido_repartidor_estado'),
            409,
            'La gestión de repartidores todavía no está habilitada. Ejecutá las migraciones de Laravel.'
        );

        $repartidor = DB::table('repartidor')
            ->where('CI', (string) $repartidorId)
            ->first(['CI', 'Nombre', 'Apellido', 'Teléfono']);
        abort_unless($repartidor, 403, 'El repartidor no está registrado.');

        DB::transaction(function () use ($pedido, $repartidorId, $repartidor, $validated, $notifications): void {
            $pedidoActual = DB::table('pedido')
                ->where('N_Pedido', $pedido)
                ->lockForUpdate()
                ->first(['N_Pedido', 'CI_Repartidor', 'Estado']);

            abort_unless($pedidoActual, 404, 'El pedido no existe.');
            abort_unless(
                $pedidoActual->Estado === 'listo',
                409,
                'El pedido solo se puede gestionar cuando todos los locales lo hayan finalizado.'
            );
            abort_if(
                $pedidoActual->CI_Repartidor !== null && $pedidoActual->CI_Repartidor !== '',
                409,
                'Este pedido ya tiene un repartidor asignado.'
            );

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

            $asignacionExistente = DB::table('pedido_repartidor_estado')
                ->where('N_Pedido', $pedido)
                ->where('Estado', 'aceptado')
                ->exists();
            abort_if($asignacionExistente, 409, 'Este pedido ya fue aceptado por otro repartidor.');

            $decisionAnterior = DB::table('pedido_repartidor_estado')
                ->where('N_Pedido', $pedido)
                ->where('CI_Repartidor', (string) $repartidorId)
                ->value('Estado');
            abort_if($decisionAnterior === 'rechazado', 409, 'Ya rechazaste este pedido.');

            DB::table('pedido_repartidor_estado')->updateOrInsert(
                ['N_Pedido' => $pedido, 'CI_Repartidor' => (string) $repartidorId],
                ['Estado' => $validated['estado']],
            );

            $datosRepartidor = [
                'cedula' => (string) $repartidor->CI,
                'nombre' => trim(($repartidor->Nombre ?? '').' '.($repartidor->Apellido ?? '')) ?: 'Repartidor',
                'telefono' => $repartidor->{'Teléfono'} ?? '',
                'estado' => $validated['estado'],
            ];

            if ($validated['estado'] === 'aceptado') {
                DB::table('pedido')
                    ->where('N_Pedido', $pedido)
                    ->update(['CI_Repartidor' => (string) $repartidorId, 'Estado' => 'en_reparto']);
                DB::table('subpedido')
                    ->where('N_Pedido', $pedido)
                    ->update(['Estado' => 'en_reparto']);
            }

            $decisionTexto = $validated['estado'] === 'aceptado' ? 'aceptó' : 'rechazó';
            $notifications->notifyOrderParticipants(
                $pedido,
                'decision_repartidor',
                "{$datosRepartidor['nombre']} {$decisionTexto} el envío del pedido #{$pedido}.",
                $datosRepartidor,
            );
        });

        return redirect()->route('dashboard.repartidor')->with(
            'success',
            $validated['estado'] === 'aceptado' ? 'Entrega aceptada.' : 'Pedido rechazado; quedó disponible para otros repartidores.',
        );
    }

    public function updateDeliveryProgress(Request $request, int $pedido, InAppNotificationService $notifications)
    {
        $email = session('email');
        $repartidorId = session('usuario_id');

        if (! $repartidorId || ! DB::table('repartidor')->where('CI', (string) $repartidorId)->exists()) {
            $repartidorId = $email
                ? DB::table('repartidor')->where('Email_Usuario', $email)->value('CI')
                : null;
        }

        abort_unless($repartidorId && DB::table('repartidor')->where('CI', (string) $repartidorId)->exists(), 403);
        session(['tipo_usuario' => 'repartidor', 'usuario_id' => (string) $repartidorId]);

        $validated = $request->validate([
            'estado_entrega' => 'required|in:productos_recolectados,en_camino,en_puerta',
        ]);
        abort_unless(Schema::hasColumn('pedido', 'Estado_Entrega'), 409, 'El progreso de entrega todavía no está habilitado. Ejecutá las migraciones de Laravel.');

        $etapas = [
            null => [
                'siguiente' => 'productos_recolectados',
                'mensaje' => 'El gaucho recogió todos sus productos.',
                'boton' => 'El gaucho recogió todos sus productos',
            ],
            'productos_recolectados' => [
                'siguiente' => 'en_camino',
                'mensaje' => 'El gaucho va en camino.',
                'boton' => 'El gaucho va en camino',
            ],
            'en_camino' => [
                'siguiente' => 'en_puerta',
                'mensaje' => 'El gaucho está en tu puerta.',
                'boton' => 'El gaucho está en tu puerta',
            ],
        ];

        DB::transaction(function () use ($pedido, $repartidorId, $validated, $notifications, $etapas): void {
            $pedidoActual = DB::table('pedido')
                ->where('N_Pedido', $pedido)
                ->lockForUpdate()
                ->first(['N_Pedido', 'CI_Cliente', 'CI_Repartidor', 'Estado', 'Estado_Entrega']);

            abort_unless($pedidoActual, 404, 'El pedido no existe.');
            abort_unless(
                (string) $pedidoActual->CI_Repartidor === (string) $repartidorId
                    && $pedidoActual->Estado === 'en_reparto',
                403,
                'Este pedido no está asignado a tu cuenta.'
            );

            $etapaActual = $pedidoActual->Estado_Entrega ?: null;
            abort_unless(
                isset($etapas[$etapaActual])
                    && $etapas[$etapaActual]['siguiente'] === $validated['estado_entrega'],
                409,
                'El progreso debe avanzar una etapa a la vez.'
            );

            $etapa = $etapas[$etapaActual];
            DB::table('pedido')
                ->where('N_Pedido', $pedido)
                ->update(['Estado_Entrega' => $validated['estado_entrega']]);

            if ($pedidoActual->CI_Cliente) {
                $notifications->sendTo(
                    'cliente',
                    $pedidoActual->CI_Cliente,
                    'progreso_entrega',
                    "Pedido #{$pedido}: {$etapa['mensaje']}",
                    $pedido,
                    [
                        'estado_entrega' => $validated['estado_entrega'],
                        'mensaje' => $etapa['mensaje'],
                    ],
                );
            }
        });

        return redirect()->route('dashboard.repartidor')->with('success', 'Se actualizó el progreso de entrega.');
    }

    public function confirmDelivery(Request $request, int $pedido, InAppNotificationService $notifications)
    {
        $email = session('email');
        $repartidorId = session('usuario_id');

        if (! $repartidorId || ! DB::table('repartidor')->where('CI', (string) $repartidorId)->exists()) {
            $repartidorId = $email
                ? DB::table('repartidor')->where('Email_Usuario', $email)->value('CI')
                : null;
        }

        abort_unless($repartidorId && DB::table('repartidor')->where('CI', (string) $repartidorId)->exists(), 403);
        session(['tipo_usuario' => 'repartidor', 'usuario_id' => (string) $repartidorId]);

        $validated = $request->validate([
            'codigo_secreto' => 'required|string',
        ]);
        abort_unless(
            Schema::hasColumn('pedido', 'Codigo_Secreto') && Schema::hasColumn('pedido', 'Estado_Entrega'),
            409,
            'La confirmación de entrega todavía no está habilitada. Ejecutá las migraciones de Laravel.'
        );

        DB::transaction(function () use ($pedido, $repartidorId, $validated, $notifications): void {
            $pedidoActual = DB::table('pedido')
                ->where('N_Pedido', $pedido)
                ->lockForUpdate()
                ->first(['N_Pedido', 'CI_Cliente', 'CI_Repartidor', 'Estado', 'Estado_Entrega', 'Codigo_Secreto']);

            abort_unless($pedidoActual, 404, 'El pedido no existe.');
            abort_unless(
                (string) $pedidoActual->CI_Repartidor === (string) $repartidorId
                    && $pedidoActual->Estado === 'en_reparto',
                403,
                'Este pedido no está asignado a tu cuenta.'
            );
            abort_unless($pedidoActual->Estado_Entrega === 'en_puerta', 409, 'El pedido debe estar en la puerta antes de confirmar la entrega.');

            if (mb_strlen($validated['codigo_secreto']) > 16
                || ! $pedidoActual->Codigo_Secreto
                || ! hash_equals((string) $pedidoActual->Codigo_Secreto, strtoupper(trim($validated['codigo_secreto'])))) {
                throw ValidationException::withMessages(['codigo_secreto' => 'Código inválido.']);
            }

            DB::table('pedido')
                ->where('N_Pedido', $pedido)
                ->update(['Estado' => 'entregado', 'Estado_Entrega' => 'entregado']);
            DB::table('subpedido')
                ->where('N_Pedido', $pedido)
                ->where('Estado', 'en_reparto')
                ->update(['Estado' => 'entregado']);

            if ($pedidoActual->CI_Cliente) {
                $notifications->sendTo(
                    'cliente',
                    $pedidoActual->CI_Cliente,
                    'pedido_entregado',
                    "El pedido #{$pedido} fue entregado correctamente.",
                    $pedido,
                    ['estado' => 'entregado'],
                );
            }
        });

        return redirect()->route('dashboard.repartidor')->with('success', 'Entrega confirmada correctamente.');
>>>>>>> 08917fc (Sistema de pedidos + Notificaciones + Ajustes en el repartidor (opciones para cambiar el estado del pedido e ingresar codigo de confirmacion de entrega))
    }
}