<?php

namespace App\Http\Controllers;

use App\Mail\PasswordResetLink;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Throwable;

class AdminController extends Controller
{
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
