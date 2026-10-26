<?php

namespace App\Http\Controllers;

use App\Mail\PasswordRecoveryRequested;
use App\Mail\PasswordResetLink;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Throwable;

class PasswordRecoveryController extends Controller
{
    public function create(): View
    {
        return view('auth.password-recovery-request');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'email' => 'required|email|max:255',
            'cedula' => 'required|string|max:30',
        ]);
        $email = Str::lower(trim($validated['email']));
        $identifier = trim($validated['cedula']);
        $account = $this->findAccount($email, $identifier);

        if (! $account) {
            $emailExists = $this->emailExistsInAccounts($email);

            return back()
                ->withInput(['email' => $email, 'cedula' => $identifier])
                ->withErrors([
                    ($emailExists ? 'cedula' : 'email') => $emailExists
                        ? 'La cédula no coincide con una cuenta asociada a ese correo.'
                        : 'No existe una cuenta registrada con ese correo electrónico.',
                ]);
        }

        $adminEmails = $this->administratorEmails();

        if ($adminEmails === []) {
            return back()
                ->withInput(['email' => $email, 'cedula' => $identifier])
                ->withErrors(['email' => 'El sistema no tiene configurado un correo administrador para recibir estas solicitudes.']);
        }

        $recoveryId = DB::table('password_recovery_requests')->insertGetId([
            'email' => $email,
            'account_type' => $account['type'],
            'identifier' => $identifier,
            'status' => 'pending',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        try {
            foreach ($adminEmails as $adminEmail) {
                Mail::to($adminEmail)->send(new PasswordRecoveryRequested(
                    accountEmail: $email,
                    accountType: $account['label'],
                    identifier: $identifier,
                    dashboardUrl: route('dashboard.admin', ['seccion' => 'recuperaciones']),
                ));
            }
        } catch (Throwable $exception) {
            DB::table('password_recovery_requests')->where('id', $recoveryId)->delete();
            Log::error('No se pudo notificar una solicitud de recuperación de contraseña.', [
                'request_id' => $recoveryId,
                'exception' => $exception::class,
            ]);

            return back()
                ->withInput(['email' => $email, 'cedula' => $identifier])
                ->withErrors(['email' => 'No se pudo enviar la solicitud. Intentá nuevamente más tarde.']);
        }

        return redirect()
            ->route('password-recovery.request')
            ->with('status', 'Solicitud enviada correctamente. Si el correo y la cédula coinciden con una cuenta, el administrador recibió la notificación para revisarla.');
    }

    public function resetForm(int $recovery, string $token): View
    {
        $this->validApprovedRequest($recovery, $token);

        return view('auth.password-reset', [
            'recovery' => $recovery,
            'token' => $token,
        ]);
    }

    public function reset(Request $request, int $recovery, string $token): RedirectResponse
    {
        $validated = $request->validate([
            'password' => 'required|string|min:8|confirmed',
        ]);

        DB::transaction(function () use ($recovery, $token, $validated): void {
            $record = DB::table('password_recovery_requests')
                ->where('id', $recovery)
                ->lockForUpdate()
                ->first();

            abort_unless($this->matchesApprovedRequest($record, $token), 404);

            $table = match ($record->account_type) {
                'cliente' => 'cliente',
                'local' => 'comercio',
                'repartidor' => 'repartidor',
                default => abort(404),
            };
            abort_unless(Schema::hasTable($table) && Schema::hasColumn($table, 'Contraseña'), 409);

            $passwordHash = Hash::make($validated['password']);
            $accountQuery = DB::table($table)->whereRaw('LOWER(Email_Usuario) = ?', [$record->email]);
            abort_unless($accountQuery->exists(), 404);
            $accountQuery->update(['Contraseña' => $passwordHash]);

            if (Schema::hasTable('usuario') && Schema::hasColumn('usuario', 'Contraseña')) {
                DB::table('usuario')
                    ->whereRaw('LOWER(Email) = ?', [$record->email])
                    ->update(['Contraseña' => $passwordHash]);
            }

            DB::table('password_recovery_requests')
                ->where('id', $recovery)
                ->update([
                    'status' => 'completed',
                    'token_hash' => null,
                    'token_expires_at' => null,
                    'updated_at' => now(),
                ]);
        });

        return redirect()
            ->route('login')
            ->with('success', 'Tu contraseña fue actualizada. Ya podés iniciar sesión.');
    }

    private function validApprovedRequest(int $recovery, string $token): object
    {
        $record = DB::table('password_recovery_requests')->where('id', $recovery)->first();
        abort_unless($this->matchesApprovedRequest($record, $token), 404);

        return $record;
    }

    private function matchesApprovedRequest(?object $record, string $token): bool
    {
        return $record
            && $record->status === 'approved'
            && $record->token_hash
            && $record->token_expires_at
            && now()->lessThanOrEqualTo($record->token_expires_at)
            && hash_equals($record->token_hash, hash('sha256', $token));
    }

    private function findAccount(string $email, string $identifier): ?array
    {
        foreach ([
            'cliente' => ['table' => 'cliente', 'label' => 'Cliente', 'columns' => ['CI', 'cedula']],
            'local' => ['table' => 'comercio', 'label' => 'Local', 'columns' => ['CI_Dueño', 'cedula']],
            'repartidor' => ['table' => 'repartidor', 'label' => 'Repartidor', 'columns' => ['CI', 'cedula']],
        ] as $type => $account) {
            if (! Schema::hasTable($account['table']) || ! Schema::hasColumn($account['table'], 'Email_Usuario')) {
                continue;
            }

            foreach ($account['columns'] as $column) {
                if (Schema::hasColumn($account['table'], $column)
                    && DB::table($account['table'])
                        ->whereRaw('LOWER(Email_Usuario) = ?', [$email])
                        ->where($column, $identifier)
                        ->exists()) {
                    return ['type' => $type, 'label' => $account['label']];
                }
            }
        }

        return null;
    }

    private function emailExistsInAccounts(string $email): bool
    {
        foreach (['cliente', 'comercio', 'repartidor'] as $table) {
            if (Schema::hasTable($table)
                && Schema::hasColumn($table, 'Email_Usuario')
                && DB::table($table)->whereRaw('LOWER(Email_Usuario) = ?', [$email])->exists()) {
                return true;
            }
        }

        return false;
    }

    private function administratorEmails(): array
    {
        $emails = collect([
            config('password-recovery.admin_email'),
        ]);

        if (Schema::hasTable('administrador')) {
            foreach (['Email_Usuario', 'Email'] as $column) {
                if (Schema::hasColumn('administrador', $column)) {
                    $emails = $emails->merge(DB::table('administrador')->pluck($column));
                }
            }
        }

        if (Schema::hasTable('usuario')
            && Schema::hasColumn('usuario', 'Email')
            && Schema::hasColumn('usuario', 'Tipo_Usuario')) {
            $emails = $emails->merge(DB::table('usuario')
                ->whereRaw('LOWER(Tipo_Usuario) IN (?, ?)', ['admin', 'administrador'])
                ->pluck('Email'));
        }

        return $emails
            ->filter(fn ($email): bool => is_string($email) && filter_var($email, FILTER_VALIDATE_EMAIL))
            ->map(fn (string $email): string => Str::lower(trim($email)))
            ->unique()
            ->values()
            ->all();
    }
}