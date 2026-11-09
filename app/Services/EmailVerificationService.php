<?php

namespace App\Services;

use App\Mail\VerificacionCorreo;
use Carbon\Carbon;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;

class EmailVerificationService
{
    private const CODE_TTL_MINUTES = 5;

    private const RESEND_COOLDOWN_SECONDS = 200;

    private const MAX_ATTEMPTS = 5;

    public function start(string $email, array $registration = []): void
    {
        if (isset($registration['data']['password'])) {
            $registration['data']['password'] = $this->hashPasswordIfNeeded(
                (string) $registration['data']['password']
            );
        }

        $this->sendCode($email, $registration);
    }

    public function resend(string $email): int
    {
        $verification = DB::table('email_verifications')->where('email', $email)->first();

        if (! $verification || $verification->verified_at) {
            return -1;
        }

        if ($verification->last_sent_at) {
            $availableAt = Carbon::parse($verification->last_sent_at)->addSeconds(self::RESEND_COOLDOWN_SECONDS);
            $remaining = now()->diffInSeconds($availableAt, false);

            if ($remaining > 0) {
                return (int) $remaining;
            }
        }

        $this->sendCode($email);

        return 0;
    }

    public function verify(string $email, string $code): string
    {
        return DB::transaction(function () use ($email, $code) {
            $verification = DB::table('email_verifications')
                ->where('email', $email)
                ->lockForUpdate()
                ->first();

            if (! $verification) {
                return 'missing';
            }

            if ($verification->verified_at) {
                return 'verified';
            }

            if (Carbon::parse($verification->expires_at)->isPast()) {
                return 'expired';
            }

            if ((int) $verification->attempts >= self::MAX_ATTEMPTS) {
                return 'locked';
            }

            if (! Hash::check($code, $verification->code_hash)) {
                $attempts = (int) $verification->attempts + 1;
                DB::table('email_verifications')->where('email', $email)->update([
                    'attempts' => $attempts,
                    'updated_at' => now(),
                ]);

                return $attempts >= self::MAX_ATTEMPTS ? 'locked' : 'invalid';
            }

            $registration = $verification->pending_registration
                ? json_decode(Crypt::decryptString($verification->pending_registration), true, flags: JSON_THROW_ON_ERROR)
                : null;

            if ($registration && ! $this->completeRegistration($email, $registration)) {
                return 'conflict';
            }

            DB::table('email_verifications')->where('email', $email)->update([
                'verified_at' => now(),
                'code_hash' => null,
                'expires_at' => now(),
                'pending_registration' => null,
                'updated_at' => now(),
            ]);

            return 'verified';
        });
    }

    private function sendCode(string $email, ?array $registration = null): void
    {
        $code = (string) random_int(100000, 999999);
        $now = now();
        $existing = DB::table('email_verifications')->where('email', $email)->first();

        DB::table('email_verifications')->updateOrInsert(
            ['email' => $email],
            [
                'code_hash' => Hash::make($code),
                'expires_at' => $now->copy()->addMinutes(self::CODE_TTL_MINUTES),
                'last_sent_at' => $now,
                'verified_at' => null,
                'attempts' => 0,
                'pending_registration' => $registration
                    ? Crypt::encryptString(json_encode($registration, JSON_THROW_ON_ERROR))
                    : ($existing->pending_registration ?? null),
                'created_at' => $now,
                'updated_at' => $now,
            ]
        );

        try {
            Mail::mailer('gmail')->to($email)->send(new VerificacionCorreo($code));
        } catch (\Throwable $exception) {
            DB::table('email_verifications')->where('email', $email)->update([
                'last_sent_at' => null,
                'updated_at' => now(),
            ]);

            throw $exception;
        }
    }

    private function completeRegistration(string $email, array $registration): bool
    {
        if (! in_array($registration['type'] ?? null, ['cliente', 'comercio', 'repartidor'], true)) {
            return false;
        }

        $data = $registration['data'];
        $password = $this->hashPasswordIfNeeded((string) $data['password']);
        $type = $registration['type'];
        $userAlreadyExists = DB::table('usuario')->where('Email', $email)->exists();
        $isGoogleRegistration = $registration['allow_existing_user'] ?? false;

        if (DB::table('cliente')->where('Email_Usuario', $email)->exists()
            || DB::table('comercio')->where('Email_Usuario', $email)->exists()
            || DB::table('repartidor')->where('Email_Usuario', $email)->exists()) {
            return false;
        }

        if ($userAlreadyExists && ! $isGoogleRegistration) {
            return false;
        }

        if ($type === 'cliente' && DB::table('cliente')->where('CI', $data['cedula'])->exists()) {
            return false;
        }
        if ($type === 'comercio' && ! empty($data['rut']) && DB::table('comercio')->where('RUT', $data['rut'])->exists()) {
            return false;
        }
        if ($type === 'repartidor' && DB::table('repartidor')->where('CI', $data['cedula'])->exists()) {
            return false;
        }

        if (! $userAlreadyExists) {
            DB::table('usuario')->insert([
                'Email' => $email,
                'Nombre_de_Usuario' => $data['nombre'],
                'Contraseña' => $password,
                'Tipo_Usuario' => $type === 'comercio' ? 'comercio' : $type,
            ]);
        }

        if ($type === 'cliente') {
            DB::table('cliente')->insert([
                'CI' => $data['cedula'],
                'Email_Usuario' => $email,
                'Contraseña' => $password,
                'Fecha_nacimiento' => $data['nacimiento'],
                'Nombre' => $data['nombre'],
                'Apellido' => $data['apellido'],
                'Teléfono' => $data['telefono'],
            ]);
        } elseif ($type === 'comercio') {
            DB::table('comercio')->insert([
                'Email_Usuario' => $email,
                'Contraseña' => $password,
                'RUT' => $data['rut'],
                'Nombre_Comercio' => $data['nombre'],
                'Nombre_dueño' => $data['nombre_dueno'],
                'CI_Dueño' => $data['cedula_dueno'],
                'Horario' => $data['horario'],
                'Dirección' => $data['direccion'],
                'Logo' => $data['logo'],
                'latitud' => $data['latitud'],
                'longitud' => $data['longitud'],
                'Estado_Adhesion' => 'Pendiente',
                'Motivo_Rechazo' => null,
            ]);
        } elseif ($type === 'repartidor') {
            DB::table('repartidor')->insert([
                'CI' => $data['cedula'],
                'Email_Usuario' => $email,
                'Contraseña' => $password,
                'Nombre' => $data['nombre'],
                'Apellido' => $data['apellido'],
                'Teléfono' => $data['telefono'],
                'Estado_Adhesion' => 'Pendiente',
                'Motivo_Rechazo' => null,
            ]);
        }

        return true;
    }

    private function hashPasswordIfNeeded(string $password): string
    {
        return Hash::needsRehash($password) ? Hash::make($password) : $password;
    }
}
