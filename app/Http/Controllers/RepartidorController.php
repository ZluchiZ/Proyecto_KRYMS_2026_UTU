<?php

namespace App\Http\Controllers;

use App\Exceptions\RegistrationMailDeliveryException;
use App\Services\InAppNotificationService;
use App\Services\RegistrationVerificationService;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class RepartidorController extends Controller
{
    public function store(Request $request, RegistrationVerificationService $verification)
    {
        $validated = $request->validate([
            'cedula' => ['required', 'string', 'max:8', Rule::unique('repartidor', 'CI')],
            'correo' => [
                'required',
                'email:rfc',
                'regex:/^[^\s@]+@[^\s@]+\.[^\s@]+$/',
                'max:255',
                Rule::unique('usuario', 'Email'),
            ],
            'nombre' => 'required|string|max:255',
            'apellido' => 'required|string|max:255',
            'telefono' => 'required|string|max:9',
            'fecha_nacimiento' => ['required', 'date', 'before_or_equal:'.now()->subYears(18)->toDateString()],
            'contrasena' => [
                'required',
                'string',
                'min:8',
                'regex:/[A-Z]/',
                'regex:/[a-z]/',
                'regex:/[0-9]/',
                'regex:/[^A-Za-z0-9\s]/',
                'confirmed',
            ],
        ], [
            'correo.unique' => 'Este correo ya está registrado.',
            'correo.regex' => 'Ingresá un correo válido, por ejemplo nombre@gmail.com.',
            'fecha_nacimiento.before_or_equal' => 'Debes tener al menos 18 años para registrarte.',
            'contrasena.min' => 'La contraseña debe tener al menos 8 caracteres.',
            'contrasena.regex' => 'La contraseña debe incluir al menos una mayúscula, una minúscula, un número y un símbolo.',
        ]);

        try {
            $sent = $verification->start('repartidor', $validated['correo'], [
                'cedula' => $validated['cedula'],
                'name' => $validated['nombre'],
                'apellido' => $validated['apellido'],
                'telefono' => $validated['telefono'],
                'nacimiento' => $validated['fecha_nacimiento'],
                'password' => $validated['contrasena'],
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

    public function updateAvailability(Request $request)
    {
        abort_unless(
            Schema::hasColumn('repartidor', 'Estado_Repartidor'),
            409,
            'La disponibilidad todavía no está habilitada en la base de datos. Ejecutá las migraciones de Laravel.'
        );

        $validated = $request->validate([
            'estado' => ['required', Rule::in(['Conectado', 'Desconectado'])],
        ]);
        $columns = ['CI'];
        foreach (['Estado_Adhesion', 'Estado_Cuenta'] as $column) {
            if (Schema::hasColumn('repartidor', $column)) {
                $columns[] = $column;
            }
        }
        $repartidor = DB::table('repartidor')
            ->where('CI', (string) session('usuario_id'))
            ->first($columns);

        abort_unless($repartidor, 403, 'El repartidor no está registrado.');
        abort_unless(
            strtolower((string) ($repartidor->Estado_Adhesion ?? 'Aceptada')) === 'aceptada'
                && strtolower((string) ($repartidor->Estado_Cuenta ?? 'Activa')) === 'activa',
            403,
            'Tu cuenta no está habilitada para conectarse.'
        );

        DB::table('repartidor')
            ->where('CI', (string) session('usuario_id'))
            ->update(['Estado_Repartidor' => $validated['estado']]);

        return redirect()->route('dashboard.repartidor')->with(
            'success',
            $validated['estado'] === 'Conectado'
                ? 'Ahora estás conectado y disponible para recibir pedidos.'
                : 'Te desconectaste y ya no aparecerás como disponible.'
        );
    }

    public function deleteVehicle()
    {
        abort_unless(session('tipo_usuario') === 'repartidor', 403, 'Solo un repartidor puede borrar su vehículo.');

        $email = session('email');
        abort_unless($email, 403, 'No hay una sesión activa para borrar el vehículo.');

        $repartidor = DB::table('repartidor')
            ->where('Email_Usuario', $email)
            ->first(['CI']);

        abort_unless($repartidor, 404, 'El repartidor no está registrado.');

        $vehiculoTable = $this->vehicleTableName();
        abort_unless($vehiculoTable !== null, 409, 'La tabla de vehículos aún no está disponible en la base de datos.');

        $vehiculo = DB::table($vehiculoTable)
            ->where('CI_Repartidor', (string) $repartidor->CI)
            ->first();

        abort_unless($vehiculo, 404, 'No tienes un vehículo guardado para eliminar.');

        foreach (['Foto', 'Libreta_de_propiedad', 'Seguro'] as $campo) {
            $path = $vehiculo->{$campo} ?? null;

            if ($path && Storage::disk('public')->exists($path)) {
                Storage::disk('public')->delete($path);
            }
        }

        DB::table($vehiculoTable)
            ->where('CI_Repartidor', (string) $repartidor->CI)
            ->delete();

        return redirect()->route('profile.edit')->with(
            'success',
            'Tu vehículo fue eliminado correctamente.'
        );
    }

    public function storeVehicle(Request $request, InAppNotificationService $notifications)
    {
        abort_unless(session('tipo_usuario') === 'repartidor', 403, 'Solo un repartidor puede registrar un vehículo.');

        $email = session('email');
        abort_unless($email, 403, 'No hay una sesión activa para registrar el vehículo.');

        $repartidor = DB::table('repartidor')
            ->where('Email_Usuario', $email)
            ->first(['CI']);

        abort_unless($repartidor, 404, 'El repartidor no está registrado.');

        $vehiculoTable = $this->vehicleTableName();

        abort_unless(
            $vehiculoTable !== null,
            409,
            'La tabla de vehículos aún no está disponible en la base de datos.'
        );

        $validated = $request->validate([
            'tipo_vehiculo' => ['required', Rule::in(['bicicleta', 'moto', 'auto_camioneta'])],
            'matricula' => ['nullable', 'string', 'max:20', Rule::requiredIf(in_array($request->tipo_vehiculo, ['moto', 'auto_camioneta']))],
            'modelo' => ['nullable', 'string', 'max:100', Rule::requiredIf(in_array($request->tipo_vehiculo, ['moto', 'auto_camioneta']))],
            'color' => ['nullable', 'string', 'max:50', Rule::requiredIf(in_array($request->tipo_vehiculo, ['moto', 'auto_camioneta']))],
            'foto' => ['nullable', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:5120'],
            'libreta_de_propiedad' => ['nullable', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:5120'],
            'seguro' => ['nullable', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:5120'],
            'libreta_de_conducir' => ['nullable', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:5120'],
            'cedula_identidad' => ['nullable', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:5120'],
        ]);

        $existingVehicle = DB::table($vehiculoTable)
            ->where('CI_Repartidor', (string) $repartidor->CI)
            ->first();

        $repartidorRecord = DB::table('repartidor')
            ->where('CI', (string) $repartidor->CI)
            ->first(['Foto_Libreta', 'Cedula_Archivo']);

        $matriculaBase = $validated['tipo_vehiculo'] === 'bicicleta'
            ? 'BIC-'.trim((string) $repartidor->CI)
            : strtoupper(trim((string) ($validated['matricula'] ?? '')));

        $vehicleData = [
            'Matricula' => $matriculaBase,
            'CI_Repartidor' => (string) $repartidor->CI,
            'Tipo_Vehiculo' => $validated['tipo_vehiculo'],
            'Modelo' => $validated['tipo_vehiculo'] === 'bicicleta' ? null : trim((string) ($validated['modelo'] ?? '')),
            'Color' => $validated['tipo_vehiculo'] === 'bicicleta' ? null : trim((string) ($validated['color'] ?? '')),
            'Libreta_de_propiedad' => $validated['tipo_vehiculo'] === 'bicicleta'
                ? null
                : $this->storeDocumentIfNeeded($request->file('libreta_de_propiedad'), 'repartidor/vehiculos/libretas', $existingVehicle->Libreta_de_propiedad ?? null),
            'Seguro' => $validated['tipo_vehiculo'] === 'bicicleta'
                ? null
                : $this->storeDocumentIfNeeded($request->file('seguro'), 'repartidor/vehiculos/seguros', $existingVehicle->Seguro ?? null),
            'Foto' => $validated['tipo_vehiculo'] === 'bicicleta'
                ? null
                : $this->storeDocumentIfNeeded($request->file('foto'), 'repartidor/vehiculos/fotos', $existingVehicle->Foto ?? null),
        ];

        $vehicleUpdate = $vehicleData;
        if (Schema::hasColumn($vehiculoTable, 'Estado_Aprobacion')) {
            $vehicleUpdate = array_merge($vehicleUpdate, [
                'Estado_Aprobacion' => 'Pendiente',
                'Motivo_Rechazo' => null,
                'Fecha_Revision' => null,
            ]);
        }

        DB::table($vehiculoTable)
            ->updateOrInsert(
                ['CI_Repartidor' => (string) $repartidor->CI],
                $vehicleUpdate,
            );

        foreach ($this->adminEmails() as $adminEmail) {
            $notifications->sendTo(
                'administrador',
                (string) $adminEmail,
                'vehiculo_pendiente',
                'Hay una solicitud de revisión de vehículo pendiente para el repartidor '.$repartidor->CI.'.',
                null,
                [
                    'ci_repartidor' => (string) $repartidor->CI,
                    'tipo_vehiculo' => $validated['tipo_vehiculo'],
                ],
            );
        }

        $repartidorUpdates = [];

        if ($request->hasFile('libreta_de_conducir')) {
            $repartidorUpdates['Foto_Libreta'] = $this->storeDocumentIfNeeded(
                $request->file('libreta_de_conducir'),
                'repartidor/documentos/libretas',
                $repartidorRecord->Foto_Libreta ?? null,
            );
        }

        if ($request->hasFile('cedula_identidad')) {
            $repartidorUpdates['Cedula_Archivo'] = $this->storeDocumentIfNeeded(
                $request->file('cedula_identidad'),
                'repartidor/documentos/cedulas',
                $repartidorRecord->Cedula_Archivo ?? null,
            );
        }

        if ($repartidorUpdates !== []) {
            DB::table('repartidor')
                ->where('CI', (string) $repartidor->CI)
                ->update($repartidorUpdates);
        }

        return redirect()->route('profile.edit')->with(
            'success',
            'Tu vehículo y documentos fueron registrados correctamente.'
        );
    }

    private function vehicleTableName(): ?string
    {
        foreach (['vehículo', 'Vehículo'] as $table) {
            if (Schema::hasTable($table)) {
                return $table;
            }
        }

        return null;
    }

    private function storeDocumentIfNeeded(?UploadedFile $file, string $directory, ?string $existingPath = null): ?string
    {
        if (! $file) {
            return $existingPath;
        }

        if ($existingPath && Storage::disk('public')->exists($existingPath)) {
            Storage::disk('public')->delete($existingPath);
        }

        return $file->store($directory, 'public');
    }

    private function adminEmails(): array
    {
        $emails = [];

        if (Schema::hasTable('administrador')) {
            $emails = array_merge($emails, DB::table('administrador')->pluck('Email_Usuario')->all());
        }

        if (Schema::hasTable('usuario')) {
            $emails = array_merge(
                $emails,
                DB::table('usuario')
                    ->whereRaw('LOWER(Tipo_Usuario) IN (?, ?)', ['admin', 'administrador'])
                    ->pluck('Email')
                    ->all()
            );
        }

        return array_values(array_unique(array_filter(array_map('trim', $emails))));
    }

    public function updatePedidoStatus(Request $request, int $pedido, InAppNotificationService $notifications)
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
    }
}
