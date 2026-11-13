<?php

namespace App\Http\Controllers;

use App\Mail\PasswordResetLink;
use App\Services\InAppNotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Throwable;

class AdminController extends Controller
{
    public function viewUserProfile(string $tipo, string $identificador)
    {
        abort_unless(session('tipo_usuario') === 'administrador' && session('usuario_id'), 403);

        $tabla = match ($tipo) {
            'cliente' => ['tabla' => 'cliente', 'columna' => 'CI'],
            'local' => ['tabla' => 'comercio', 'columna' => 'RUT'],
            'repartidor' => ['tabla' => 'repartidor', 'columna' => 'CI'],
            default => abort(404),
        };

        $usuario = DB::table($tabla['tabla'])
            ->where($tabla['columna'], $identificador)
            ->first();

        abort_unless($usuario, 404, 'No se encontró el usuario solicitado.');

        $profileType = $tipo === 'local' ? 'comercio' : $tipo;
        $vehicle = null;
        $driverLicenseFile = null;
        $identityFile = null;

        if ($profileType === 'repartidor') {
            $vehicleTable = null;
            foreach (['vehículo', 'Vehículo'] as $table) {
                if (Schema::hasTable($table)) {
                    $vehicleTable = $table;
                    break;
                }
            }

            if ($vehicleTable) {
                $vehicle = DB::table($vehicleTable)
                    ->where('CI_Repartidor', (string) $usuario->CI)
                    ->first();
            }

            if (Schema::hasColumn('repartidor', 'Foto_Libreta')) {
                $driverLicenseFile = DB::table('repartidor')
                    ->where('CI', (string) $usuario->CI)
                    ->value('Foto_Libreta');
            }

            if (Schema::hasColumn('repartidor', 'Cedula_Archivo')) {
                $identityFile = DB::table('repartidor')
                    ->where('CI', (string) $usuario->CI)
                    ->value('Cedula_Archivo');
            }
        }

        $fieldDefinitions = match ($profileType) {
            'cliente' => [
                'nombre' => ['label' => 'Nombre', 'columns' => ['Nombre']],
                'apellido' => ['label' => 'Apellido', 'columns' => ['Apellido']],
                'telefono' => ['label' => 'Teléfono', 'columns' => ['Teléfono']],
                'direccion' => ['label' => 'Dirección de entrega', 'columns' => ['Direccion_Entrega', 'direccion']],
            ],
            'comercio' => [
                'nombre' => ['label' => 'Nombre del local', 'columns' => ['Nombre_Comercio']],
                'nombre_dueno' => ['label' => 'Nombre del dueño', 'columns' => ['Nombre_dueño']],
                'telefono' => ['label' => 'Teléfono', 'columns' => ['Teléfono']],
                'direccion' => ['label' => 'Dirección del local', 'columns' => ['Dirección', 'direccion']],
                'horario' => ['label' => 'Horario', 'columns' => ['Horario']],
            ],
            'repartidor' => [
                'nombre' => ['label' => 'Nombre', 'columns' => ['Nombre']],
                'apellido' => ['label' => 'Apellido', 'columns' => ['Apellido']],
                'telefono' => ['label' => 'Teléfono', 'columns' => ['Teléfono']],
            ],
            default => [],
        };

        $fields = [];
        foreach ($fieldDefinitions as $name => $definition) {
            $column = $definition['columns'][0];
            if ($name === 'direccion' && $profileType === 'cliente' && isset($usuario->direccion)) {
                $column = 'direccion';
            }

            $fields[] = [
                'name' => $name,
                'label' => $definition['label'],
                'type' => 'text',
                'value' => $usuario->{$column} ?? '',
                'required' => false,
            ];
        }

        return view('profile-edit', [
            'type' => $profileType,
            'email' => $usuario->Email_Usuario ?? 'Sin correo',
            'fields' => $fields,
            'vehicle' => $vehicle,
            'driverLicenseFile' => $driverLicenseFile,
            'identityFile' => $identityFile,
            'canEdit' => false,
            'adminView' => true,
            'backRoute' => route('dashboard.admin', ['seccion' => 'usuarios']),
            'backLabel' => 'Volver al panel',
        ]);
    }

    public function updateUserStatus(Request $request, string $tipo, string $email)
    {
        abort_unless(session('tipo_usuario') === 'administrador' && session('usuario_id'), 403);

        $tabla = match ($tipo) {
            'cliente' => 'cliente',
            'local' => 'comercio',
            'repartidor' => 'repartidor',
            default => abort(404),
        };
        $validated = $request->validate(['accion' => 'required|in:suspender,activar']);

        $cuenta = DB::table($tabla)->where('Email_Usuario', $email);
        abort_unless($cuenta->exists(), 404, 'No se encontró la cuenta.');
        $cuenta->update(['Estado_Cuenta' => $validated['accion'] === 'suspender' ? 'Suspendida' : 'Activa']);

        return redirect()
            ->route('dashboard.admin', ['seccion' => 'usuarios'])
            ->with('success', $validated['accion'] === 'suspender' ? 'Cuenta suspendida.' : 'Cuenta reactivada.');
    }

    public function updateSubscriptionSpotlight(Request $request, string $email)
    {
        abort_unless(session('tipo_usuario') === 'administrador' && session('usuario_id'), 403);

        $validated = $request->validate(['accion' => 'required|in:activar,desactivar']);
        $local = DB::table('comercio')->where('Email_Usuario', $email)->first();
        abort_unless($local, 404, 'No se encontró el local.');

        if ($validated['accion'] === 'activar') {
            abort_unless(
                strtolower((string) ($local->Estado_Adhesion ?? 'aceptada')) === 'aceptada'
                    && strtolower((string) ($local->Estado_Cuenta ?? 'activa')) === 'activa',
                409,
                'Solo se pueden destacar locales aprobados y activos.'
            );
        }

        DB::table('comercio')
            ->where('Email_Usuario', $email)
            ->update(['Suscripcion_Activa' => $validated['accion'] === 'activar']);

        return redirect()
            ->route('dashboard.admin', ['seccion' => 'suscripciones'])
            ->with('success', $validated['accion'] === 'activar'
                ? 'Suscripción confirmada y local agregado a KRYMS recomienda.'
                : 'Se quitó el local de KRYMS recomienda.');
    }

    public function updateReport(Request $request, int $report)
    {
        abort_unless(session('tipo_usuario') === 'administrador' && session('usuario_id'), 403);

        $validated = $request->validate([
            'estado' => 'required|in:pendiente,en_revision,informacion_solicitada,resuelto',
            'resolucion' => 'required_if:estado,informacion_solicitada,resuelto|nullable|string|max:2000',
        ]);
        $estados = [
            'pendiente' => 'Pendiente',
            'en_revision' => 'En revisión',
            'informacion_solicitada' => 'Información solicitada',
            'resuelto' => 'Resuelto',
        ];

        $reporte = DB::table('reporte')->where('ID_Reporte', $report);
        abort_unless($reporte->exists(), 404, 'No se encontró el reporte.');
        $reporte->update([
            'Estado_Gestion' => $estados[$validated['estado']],
            'Resolucion' => $validated['resolucion'] ?? null,
        ]);

        return redirect()
            ->route('dashboard.admin', ['seccion' => 'reportes'])
            ->with('success', 'Reporte actualizado.');
    }

    public function moderateReview(Request $request, int $review)
    {
        abort_unless(session('tipo_usuario') === 'administrador' && session('usuario_id'), 403);

        $validated = $request->validate([
            'decision' => 'required|in:mantener,retirar',
            'motivo' => 'required_if:decision,retirar|nullable|string|max:1000',
        ]);
        $visible = $validated['decision'] === 'mantener';

        $resena = DB::table('reseña')->where('ID', $review);
        abort_unless($resena->exists(), 404, 'No se encontró la reseña.');
        $resena->update([
            'Estado_Moderacion' => $visible ? 'Visible' : 'Retirada',
            'Motivo_Retiro' => $visible ? null : trim($validated['motivo']),
        ]);

        return redirect()
            ->route('dashboard.admin', ['seccion' => 'resenas'])
            ->with('success', $visible ? 'Reseña conservada y visible.' : 'Reseña retirada de la plataforma.');
    }

    public function reviewVehicle(Request $request, string $ci, InAppNotificationService $notifications)
    {
        abort_unless(session('tipo_usuario') === 'administrador' && session('usuario_id'), 403);

        $validated = $request->validate([
            'decision' => 'required|in:aprobar,rechazar',
            'motivo' => 'required_if:decision,rechazar|nullable|string|max:1000',
        ]);

        $vehiculoTable = null;
        foreach (['vehículo', 'Vehículo'] as $table) {
            if (Schema::hasTable($table)) {
                $vehiculoTable = $table;
                break;
            }
        }

        abort_unless(
            $vehiculoTable && Schema::hasColumn($vehiculoTable, 'Estado_Aprobacion'),
            409,
            'La aprobación de vehículos aún no está habilitada en la base de datos.'
        );

        $vehiculo = DB::table($vehiculoTable)->where('CI_Repartidor', $ci)->first();
        abort_unless($vehiculo, 404, 'No se encontró el vehículo del repartidor.');

        $esAprobado = $validated['decision'] === 'aprobar';
        $motivo = $esAprobado ? null : trim((string) ($validated['motivo'] ?? ''));

        if ($esAprobado) {
            $updated = DB::table($vehiculoTable)
                ->where('CI_Repartidor', $ci)
                ->update([
                    'Estado_Aprobacion' => 'Aprobado',
                    'Motivo_Rechazo' => null,
                    'Fecha_Revision' => now(),
                ]);

            abort_unless($updated === 1, 409, 'El vehículo ya fue revisado o no existe.');
        } else {
            foreach (['Foto', 'Libreta_de_propiedad', 'Seguro'] as $campo) {
                $path = $vehiculo->{$campo} ?? null;

                if ($path && Storage::disk('public')->exists($path)) {
                    Storage::disk('public')->delete($path);
                }
            }

            $deleted = DB::table($vehiculoTable)
                ->where('CI_Repartidor', $ci)
                ->delete();

            abort_unless($deleted === 1, 409, 'El vehículo ya fue revisado o no existe.');
        }

        $repartidor = DB::table('repartidor')->where('CI', $ci)->first(['Email_Usuario', 'Nombre', 'Apellido']);
        if ($repartidor) {
            $payload = [
                'ci_repartidor' => $ci,
                'decision' => $esAprobado ? 'aprobado' : 'rechazado',
            ];

            $mensaje = $esAprobado
                ? 'Tu vehículo fue aprobado por el administrador y ya puede ser usado en la plataforma.'
                : 'Tu vehículo fue rechazado por el administrador. Motivo: '.$motivo;

            $notifications->sendTo(
                'repartidor',
                (string) $ci,
                $esAprobado ? 'vehiculo_aprobado' : 'vehiculo_rechazado',
                $mensaje,
                null,
                $payload,
            );
        }

        return redirect()
            ->route('dashboard.admin', ['seccion' => 'vehiculos'])
            ->with('success', $esAprobado ? 'Vehículo aprobado y repartidor notificado.' : 'Vehículo rechazado y repartidor notificado.');
    }

    public function updateAdhesion(Request $request, string $tipo, string $email)
    {
        abort_unless(session('tipo_usuario') === 'administrador' && session('usuario_id'), 403);

        $tabla = match ($tipo) {
            'local' => 'comercio',
            'repartidor' => 'repartidor',
            default => abort(404),
        };
        $validated = $request->validate([
            'decision' => 'required|in:aceptar,rechazar',
            'motivo' => 'required_if:decision,rechazar|nullable|string|max:1000',
        ]);

        $aceptada = $validated['decision'] === 'aceptar';
        $estado = $aceptada ? 'Aceptada' : 'Rechazada';
        $motivo = $aceptada ? null : trim($validated['motivo']);
        $actualizada = DB::table($tabla)
            ->where('Email_Usuario', $email)
            ->where('Estado_Adhesion', 'Pendiente')
            ->update([
                'Estado_Adhesion' => $estado,
                'Motivo_Rechazo' => $motivo,
            ]);

        abort_unless($actualizada === 1, 409, 'La solicitud ya fue gestionada o no existe.');

        $mensaje = $aceptada
            ? 'Tu solicitud de adhesión fue aprobada. Ya puedes iniciar sesión.'
            : 'Tu solicitud de adhesión fue rechazada. Motivo: '.$motivo;

        try {
            Mail::raw($mensaje, function ($mail) use ($email, $aceptada): void {
                $mail->to($email)->subject($aceptada ? 'Adhesión aprobada' : 'Adhesión rechazada');
            });
        } catch (\Throwable) {
            return redirect()
                ->route('dashboard.admin', ['seccion' => 'adhesiones'])
                ->with('warning', 'La decisión se guardó, pero no se pudo enviar el correo al solicitante.');
        }

        return redirect()
            ->route('dashboard.admin', ['seccion' => 'adhesiones'])
            ->with('success', $aceptada ? 'Adhesión aprobada y solicitante notificado.' : 'Adhesión rechazada y solicitante notificado.');
    }

    public function approvePasswordRecovery(int $recovery)
    {
        abort_unless(session('tipo_usuario') === 'administrador' && session('usuario_id'), 403);

        $solicitud = DB::table('password_recovery_requests')->where('id', $recovery)->first();
        abort_unless($solicitud && $solicitud->status === 'pending', 409, 'La solicitud ya fue gestionada.');

        $token = Str::random(64);
        DB::table('password_recovery_requests')->where('id', $recovery)->update([
            'status' => 'approved',
            'token_hash' => hash('sha256', $token),
            'token_expires_at' => now()->addMinutes(30),
            'reviewed_by' => session('email'),
            'reviewed_at' => now(),
            'updated_at' => now(),
        ]);

        try {
            Mail::to($solicitud->email)->send(new PasswordResetLink(
                route('password-recovery.reset.form', [$recovery, $token])
            ));
        } catch (Throwable $exception) {
            DB::table('password_recovery_requests')->where('id', $recovery)->update([
                'status' => 'pending',
                'token_hash' => null,
                'token_expires_at' => null,
                'reviewed_by' => null,
                'reviewed_at' => null,
                'updated_at' => now(),
            ]);
            Log::error('No se pudo enviar el enlace aprobado para recuperar una contraseña.', [
                'request_id' => $recovery,
                'exception' => $exception::class,
            ]);

            return redirect()
                ->route('dashboard.admin', ['seccion' => 'recuperaciones'])
                ->with('warning', 'La solicitud sigue pendiente porque no se pudo enviar el correo.');
        }

        return redirect()
            ->route('dashboard.admin', ['seccion' => 'recuperaciones'])
            ->with('success', 'Solicitud aprobada. Se envió un enlace de restablecimiento al correo de la cuenta.');
    }

    public function rejectPasswordRecovery(int $recovery)
    {
        abort_unless(session('tipo_usuario') === 'administrador' && session('usuario_id'), 403);

        $updated = DB::table('password_recovery_requests')
            ->where('id', $recovery)
            ->where('status', 'pending')
            ->update([
                'status' => 'rejected',
                'reviewed_by' => session('email'),
                'reviewed_at' => now(),
                'updated_at' => now(),
            ]);

        abort_unless($updated === 1, 409, 'La solicitud ya fue gestionada o no existe.');

        return redirect()
            ->route('dashboard.admin', ['seccion' => 'recuperaciones'])
            ->with('success', 'Solicitud rechazada. No se enviará un enlace de restablecimiento.');
    }
}
