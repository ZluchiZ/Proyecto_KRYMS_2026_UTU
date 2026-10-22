<?php

namespace Tests\Feature;

<<<<<<< HEAD
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
=======
use App\Mail\RegistrationVerificationCode;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Tests\Concerns\CreatesRegistrationTables;
>>>>>>> 2454183e5acfdf3e4c0d75ce3f8717a428fe5a8f
use Tests\TestCase;

class LocalAndRepartidorRegistrationTest extends TestCase
{
<<<<<<< HEAD
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

        Schema::connection('sqlite')->create('cliente', function (Blueprint $table) {
            $table->id();
            $table->string('correo')->unique();
        });

        Schema::connection('sqlite')->create('comercio', function (Blueprint $table) {
            $table->id();
            $table->string('rut')->nullable();
            $table->string('cedula');
            $table->string('nombre');
            $table->string('direccion');
            $table->string('logo');
            $table->string('numero_cuenta');
            $table->string('correo')->unique();
            $table->string('contrasena');
            $table->timestamps();
        });

        Schema::connection('sqlite')->create('repartidor', function (Blueprint $table) {
            $table->id();
            $table->string('cedula');
            $table->string('correo')->unique();
            $table->string('nombre');
            $table->string('apellido');
            $table->string('telefono');
            $table->date('fecha_nacimiento');
            $table->string('contrasena');
            $table->timestamps();
        });
=======
    use CreatesRegistrationTables;

    protected function setUp(): void
    {
        parent::setUp();
        $this->createRegistrationTables();
        Mail::fake();
        Http::fake();
>>>>>>> 2454183e5acfdf3e4c0d75ce3f8717a428fe5a8f
    }

    public function test_local_registration_creates_a_local_record(): void
    {
        $response = $this->post(route('local.store'), [
            'rut' => '12345678',
            'cedula' => '12345678',
            'nombre' => 'Mi Local',
<<<<<<< HEAD
=======
            'nombre_dueno' => 'Juan Pérez',
            'cedula_dueno' => '12345678',
            'horario' => '08:00 hrs - 18:00 hrs',
>>>>>>> 2454183e5acfdf3e4c0d75ce3f8717a428fe5a8f
            'direccion' => 'Calle 123',
            'logo' => 'https://example.com/logo.png',
            'numero_cuenta' => '1234567890',
            'correo' => 'local@example.com',
            'contrasena' => 'password123',
            'contrasena_confirmation' => 'password123',
        ]);

<<<<<<< HEAD
        $response->assertRedirect(route('login'));
        $this->assertDatabaseHas('comercio', ['correo' => 'local@example.com']);
=======
        $response->assertRedirect(route('registration.verify.form', ['email' => 'local@example.com']));
        $this->assertDatabaseMissing('comercio', ['Email_Usuario' => 'local@example.com']);

        $code = null;
        Mail::assertSent(RegistrationVerificationCode::class, function (RegistrationVerificationCode $mail) use (&$code): bool {
            $code = $mail->code;

            return true;
        });
        $this->post(route('registration.verify'), [
            'email' => 'local@example.com',
            'code' => $code,
        ])->assertRedirect(route('login'));
        $this->assertDatabaseHas('comercio', [
            'Email_Usuario' => 'local@example.com',
            'Estado_Adhesion' => 'Pendiente',
        ]);
>>>>>>> 2454183e5acfdf3e4c0d75ce3f8717a428fe5a8f
    }

    public function test_repartidor_registration_creates_a_repartidor_record(): void
    {
        $response = $this->post(route('repartidor.store'), [
            'cedula' => '87654321',
            'correo' => 'repartidor@example.com',
            'nombre' => 'Luis',
            'apellido' => 'Pérez',
            'telefono' => '099123456',
            'fecha_nacimiento' => '1995-05-10',
            'contrasena' => 'password123',
            'contrasena_confirmation' => 'password123',
        ]);

<<<<<<< HEAD
        $response->assertRedirect(route('login'));
        $this->assertDatabaseHas('repartidor', ['correo' => 'repartidor@example.com']);
=======
        $response->assertRedirect(route('registration.verify.form', ['email' => 'repartidor@example.com']));
        $this->assertDatabaseMissing('repartidor', ['Email_Usuario' => 'repartidor@example.com']);

        $code = null;
        Mail::assertSent(RegistrationVerificationCode::class, function (RegistrationVerificationCode $mail) use (&$code): bool {
            $code = $mail->code;

            return true;
        });
        $this->post(route('registration.verify'), [
            'email' => 'repartidor@example.com',
            'code' => $code,
        ])->assertRedirect(route('login'));
        $this->assertDatabaseHas('repartidor', [
            'Email_Usuario' => 'repartidor@example.com',
            'Estado_Adhesion' => 'Pendiente',
        ]);
>>>>>>> 2454183e5acfdf3e4c0d75ce3f8717a428fe5a8f
    }
}
