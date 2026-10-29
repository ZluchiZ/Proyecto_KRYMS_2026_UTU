<?php

namespace Tests\Feature;

use App\Mail\VerificacionCorreo;
use App\Services\EmailVerificationService;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class EmailVerificationTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        config([
            'database.default' => 'sqlite',
            'database.connections.sqlite' => [
                'driver' => 'sqlite',
                'database' => ':memory:',
                'prefix' => '',
            ],
        ]);

        DB::purge('sqlite');

        Schema::connection('sqlite')->create('email_verifications', function (Blueprint $table) {
            $table->string('email')->primary();
            $table->string('code_hash')->nullable();
            $table->longText('pending_registration')->nullable();
            $table->timestamp('expires_at');
            $table->timestamp('last_sent_at')->nullable();
            $table->timestamp('verified_at')->nullable();
            $table->unsignedTinyInteger('attempts')->default(0);
            $table->timestamps();
        });

        Schema::connection('sqlite')->create('usuario', function (Blueprint $table) {
            $table->string('Email')->primary();
            $table->string('Nombre_de_Usuario');
            $table->string('Contraseña');
            $table->string('Tipo_Usuario');
        });

        Schema::connection('sqlite')->create('cliente', function (Blueprint $table) {
            $table->string('CI')->primary();
            $table->string('Email_Usuario')->unique();
            $table->string('Contraseña');
            $table->date('Fecha_nacimiento');
            $table->string('Nombre');
            $table->string('Apellido');
            $table->string('Teléfono');
        });

        foreach (['comercio', 'repartidor'] as $tableName) {
            Schema::connection('sqlite')->create($tableName, function (Blueprint $table) {
                $table->string('Email_Usuario')->primary();
                $table->string('CI')->nullable();
                $table->string('RUT')->nullable();
                $table->string('Contraseña')->nullable();
                $table->string('Nombre_Comercio')->nullable();
                $table->string('Nombre_dueño')->nullable();
                $table->string('CI_Dueño')->nullable();
                $table->string('Horario')->nullable();
                $table->string('Dirección')->nullable();
                $table->string('Logo')->nullable();
                $table->string('latitud')->nullable();
                $table->string('longitud')->nullable();
                $table->string('Nombre')->nullable();
                $table->string('Apellido')->nullable();
                $table->string('Teléfono')->nullable();
                $table->string('Estado_Adhesion')->nullable();
                $table->string('Motivo_Rechazo')->nullable();
            });
        }

        Mail::fake();
    }

    public function test_code_is_six_digits_and_expires_after_five_minutes(): void
    {
        $service = app(EmailVerificationService::class);
        $service->start('cliente@example.com');

        $verification = DB::table('email_verifications')->where('email', 'cliente@example.com')->first();

        $this->assertSame(now()->addMinutes(5)->toDateTimeString(), $verification->expires_at);
        Mail::assertSent(VerificacionCorreo::class, fn (VerificacionCorreo $mail) => preg_match('/^[0-9]{6}$/', $mail->code) === 1);

        $this->travel(5)->minutes();
        $this->assertSame('expired', $service->verify('cliente@example.com', '000000'));
    }

    public function test_resend_waits_two_hundred_seconds_and_replaces_the_previous_code(): void
    {
        $service = app(EmailVerificationService::class);
        $service->start('local@example.com');

        $this->assertGreaterThan(0, $service->resend('local@example.com'));
        Mail::assertSent(VerificacionCorreo::class, 1);

        $this->travel(200)->seconds();
        $this->assertSame(0, $service->resend('local@example.com'));
        Mail::assertSent(VerificacionCorreo::class, 2);
    }

    public function test_valid_code_can_only_be_used_once_and_wrong_codes_are_limited(): void
    {
        $service = app(EmailVerificationService::class);
        $service->start('repartidor@example.com');
        $code = null;

        Mail::assertSent(VerificacionCorreo::class, function (VerificacionCorreo $mail) use (&$code) {
            $code = $mail->code;

            return true;
        });

        $this->assertSame('verified', $service->verify('repartidor@example.com', $code));
        $this->assertSame('verified', $service->verify('repartidor@example.com', $code));

        $service->start('otro@example.com');
        foreach (range(1, 5) as $attempt) {
            $result = $service->verify('otro@example.com', '000000');
        }

        $this->assertSame('locked', $result);
    }

    public function test_cliente_is_saved_only_after_email_code_is_verified(): void
    {
        $service = app(EmailVerificationService::class);
        $service->start('cliente@example.com', [
            'type' => 'cliente',
            'data' => [
                'cedula' => '12345678',
                'nombre' => 'Ana',
                'apellido' => 'Pérez',
                'telefono' => '099123456',
                'nacimiento' => '1990-01-02',
                'password' => 'password123',
            ],
        ]);

        $this->assertDatabaseMissing('usuario', ['Email' => 'cliente@example.com']);
        $this->assertDatabaseMissing('cliente', ['Email_Usuario' => 'cliente@example.com']);

        $code = null;
        Mail::assertSent(VerificacionCorreo::class, function (VerificacionCorreo $mail) use (&$code) {
            $code = $mail->code;

            return true;
        });

        $this->assertSame('verified', $service->verify('cliente@example.com', $code));
        $this->assertDatabaseHas('usuario', ['Email' => 'cliente@example.com']);
        $this->assertDatabaseHas('cliente', [
            'CI' => '12345678',
            'Email_Usuario' => 'cliente@example.com',
        ]);
        $this->assertTrue(Hash::check(
            'password123',
            DB::table('usuario')->where('Email', 'cliente@example.com')->value('Contraseña')
        ));
        $this->assertTrue(Hash::check(
            'password123',
            DB::table('cliente')->where('Email_Usuario', 'cliente@example.com')->value('Contraseña')
        ));
    }

    public function test_local_and_repartidor_are_saved_only_after_email_code_is_verified(): void
    {
        $registrations = [
            [
                'email' => 'local@example.com',
                'type' => 'comercio',
                'table' => 'comercio',
                'data' => [
                    'rut' => '12345678',
                    'nombre' => 'Mi Local',
                    'nombre_dueno' => 'Ana',
                    'cedula_dueno' => '12345678',
                    'horario' => '08:00 hrs - 18:00 hrs',
                    'direccion' => 'Calle 123',
                    'logo' => 'https://example.com/logo.png',
                    'latitud' => -34.9,
                    'longitud' => -56.2,
                    'password' => 'password123',
                ],
            ],
            [
                'email' => 'repartidor@example.com',
                'type' => 'repartidor',
                'table' => 'repartidor',
                'data' => [
                    'cedula' => '87654321',
                    'nombre' => 'Luis',
                    'apellido' => 'Pérez',
                    'telefono' => '099123456',
                    'password' => 'password123',
                ],
            ],
        ];

        foreach ($registrations as $registration) {
            $service = app(EmailVerificationService::class);
            $service->start($registration['email'], [
                'type' => $registration['type'],
                'data' => $registration['data'],
            ]);

            $this->assertDatabaseMissing('usuario', ['Email' => $registration['email']]);
            $this->assertDatabaseMissing($registration['table'], ['Email_Usuario' => $registration['email']]);

            $code = null;
            Mail::assertSent(VerificacionCorreo::class, function (VerificacionCorreo $mail) use (&$code, $registration) {
                if (($mail->to[0]['address'] ?? null) !== $registration['email']) {
                    return false;
                }

                $code = $mail->code;

                return true;
            });

            $this->assertSame('verified', $service->verify($registration['email'], $code));
            $this->assertDatabaseHas('usuario', ['Email' => $registration['email']]);
            $this->assertDatabaseHas($registration['table'], ['Email_Usuario' => $registration['email']]);
            $this->assertTrue(Hash::check(
                'password123',
                DB::table('usuario')->where('Email', $registration['email'])->value('Contraseña')
            ));
            $this->assertTrue(Hash::check(
                'password123',
                DB::table($registration['table'])->where('Email_Usuario', $registration['email'])->value('Contraseña')
            ));
        }
    }
}
