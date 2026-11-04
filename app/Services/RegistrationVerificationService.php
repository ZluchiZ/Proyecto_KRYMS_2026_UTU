<?php

namespace App\Services;

use App\Exceptions\RegistrationMailDeliveryException;
use App\Mail\RegistrationVerificationCode;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Schema;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Throwable;

class RegistrationVerificationService
{
    private const CODE_TTL_MINUTES = 15;

    private const MAX_ATTEMPTS = 5;

    private const RESEND_COOLDOWN_SECONDS = 60;

    public function start(string $type, string $email, array $payload): bool
    {
        if (isset($payload['password'])) {
            $payload['password'] = $this->hashPasswordIfNeeded((string) $payload['password']);
        }

        $code = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);
        $now = now();

        $created = DB::transaction(function () use ($type, $email, $payload, $code, $now): bool {
            DB::table('registration_verifications')
                ->where('expires_at', '<', $now)
                ->delete();

            $existing = DB::table('registration_verifications')
                ->where('email', $email)
                ->lockForUpdate()
                ->first();

            if ($existing) {
                return false;
            }

            DB::table('registration_verifications')->insert([
                'email' => $email,
                'type' => $type,
                'payload' => json_encode($payload, JSON_THROW_ON_ERROR),
                'code_hash' => Hash::make($code),
                'attempts' => 0,
                'expires_at' => $now->copy()->addMinutes(self::CODE_TTL_MINUTES),
                'created_at' => $now,
                'updated_at' => $now,
            ]);

            return true;
        });

        if (! $created) {
            return false;
        }

        try {
            Mail::to($email)->send(new RegistrationVerificationCode($code));
        } catch (Throwable $exception) {
            DB::table('registration_verifications')->where('email', $email)->delete();
            throw new RegistrationMailDeliveryException($exception);
        }

        return true;
    }

    public function resend(string $email): void
    {
        $pending = DB::table('registration_verifications')->where('email', $email)->first();
        abort_unless($pending, 404, 'No hay un registro pendiente para ese correo.');

        if (now()->lessThan(
            Carbon::parse($pending->created_at)->addSeconds(self::RESEND_COOLDOWN_SECONDS)
        )) {
            throw new HttpException(429, 'Esperá un minuto antes de solicitar otro código.');
        }

        $code = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);
        try {
            Mail::to($email)->send(new RegistrationVerificationCode($code));
        } catch (Throwable $exception) {
            throw new RegistrationMailDeliveryException($exception);
        }

        DB::table('registration_verifications')
            ->where('email', $email)
            ->update([
                'code_hash' => Hash::make($code),
                'attempts' => 0,
                'expires_at' => now()->addMinutes(self::CODE_TTL_MINUTES),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
    }

    public function verify(string $email, string $code): array
    {
        $result = DB::transaction(function () use ($email, $code): array {
            $pending = DB::table('registration_verifications')
                ->where('email', $email)
                ->lockForUpdate()
                ->first();

            abort_unless($pending, 404, 'No hay un registro pendiente para ese correo.');

            if (now()->greaterThan($pending->expires_at)) {
                return ['error' => 'El código venció. Solicitá uno nuevo para continuar.', 'status' => 422];
            }

            if ((int) $pending->attempts >= self::MAX_ATTEMPTS) {
                return ['error' => 'Se agotaron los intentos. Solicitá un código nuevo.', 'status' => 429];
            }

            DB::table('registration_verifications')
                ->where('email', $email)
                ->increment('attempts');

            if (! Hash::check($code, $pending->code_hash)) {
                return ['error' => 'El código ingresado no es correcto.', 'status' => 422];
            }

            $payload = json_decode($pending->payload, true, 512, JSON_THROW_ON_ERROR);
            $this->createAccount($pending->type, $email, $payload);

            DB::table('registration_verifications')->where('email', $email)->delete();

            return [
                'type' => $pending->type,
                'id' => $this->accountId($pending->type, $payload),
                'google_registration' => (bool) ($payload['google_registration'] ?? false),
            ];
        });

        if (isset($result['error'])) {
            throw new HttpException($result['status'], $result['error']);
        }

        return $result;
    }

    private function createAccount(string $type, string $email, array $payload): void
    {
        abort_if(DB::table('usuario')->where('Email', $email)->exists(), 409, 'Este correo ya tiene una cuenta.');

        $password = $this->hashPasswordIfNeeded((string) $payload['password']);
        $name = $payload['name'];

        DB::table('usuario')->insert([
            'Email' => $email,
            'Nombre_de_Usuario' => $name,
            'Contraseña' => $password,
            'Tipo_Usuario' => $type,
        ]);

        if ($type === 'cliente') {
            DB::table('cliente')->insert([
                'CI' => $payload['cedula'],
                'Email_Usuario' => $email,
                'Contraseña' => $password,
                'Fecha_nacimiento' => $payload['nacimiento'],
                'Nombre' => $name,
                'Apellido' => $payload['apellido'],
                'Teléfono' => $payload['telefono'],
            ]);

            return;
        }

        if ($type === 'comercio') {
            $coordinates = $this->geocode($payload['direccion'] ?? null);
            $comercio = [
                'Email_Usuario' => $email,
                'Contraseña' => $password,
                'RUT' => $payload['rut'] ?? null,
                'Nombre_Comercio' => $name,
                'Nombre_dueño' => $payload['nombre_dueno'],
                'CI_Dueño' => $payload['cedula_dueno'],
                'Horario' => $payload['horario'],
                'Dirección' => $payload['direccion'] ?? null,
                'Logo' => $payload['logo'] ?? null,
                'Estado_Adhesion' => 'Pendiente',
                'Motivo_Rechazo' => null,
            ];

            if (Schema::hasColumn('comercio', 'latitud')) {
                $comercio['latitud'] = $coordinates['latitud'];
            }
            if (Schema::hasColumn('comercio', 'longitud')) {
                $comercio['longitud'] = $coordinates['longitud'];
            }

            DB::table('comercio')->insert($comercio);

            return;
        }

        if ($type === 'repartidor') {
            DB::table('repartidor')->insert([
                'CI' => $payload['cedula'],
                'Email_Usuario' => $email,
                'Contraseña' => $password,
                'Nombre' => $name,
                'Apellido' => $payload['apellido'],
                'Teléfono' => $payload['telefono'],
                'Estado_Adhesion' => 'Pendiente',
                'Motivo_Rechazo' => null,
            ]);

            return;
        }

        abort(422, 'Tipo de registro no válido.');
    }

    private function hashPasswordIfNeeded(string $password): string
    {
        return Hash::needsRehash($password) ? Hash::make($password) : $password;
    }

    private function accountId(string $type, array $payload): string
    {
        return match ($type) {
            'comercio' => (string) ($payload['rut'] ?? ''),
            default => (string) $payload['cedula'],
        };
    }

    private function geocode(?string $address): array
    {
        if (! $address) {
            return ['latitud' => null, 'longitud' => null];
        }

        try {
            $result = Http::withHeaders([
                'User-Agent' => 'KRYMS Proyecto 2026 / Laravel',
            ])->timeout(5)->get('https://nominatim.openstreetmap.org/search', [
                'q' => $address.', Uruguay',
                'format' => 'json',
                'limit' => 1,
            ])->json();
        } catch (ConnectionException $exception) {
            report($exception);
            Log::warning('No se pudieron obtener las coordenadas del local durante el registro.', [
                'address' => $address,
            ]);

            return ['latitud' => null, 'longitud' => null];
        }

        return [
            'latitud' => isset($result[0]['lat']) ? (float) $result[0]['lat'] : null,
            'longitud' => isset($result[0]['lon']) ? (float) $result[0]['lon'] : null,
        ];
    }
}
