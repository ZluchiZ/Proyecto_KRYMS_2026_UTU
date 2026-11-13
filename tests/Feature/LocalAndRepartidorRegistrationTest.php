<?php

namespace Tests\Feature;

use App\Mail\RegistrationVerificationCode;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Tests\Concerns\CreatesRegistrationTables;
use Tests\TestCase;

class LocalAndRepartidorRegistrationTest extends TestCase
{
    use CreatesRegistrationTables;

    protected function setUp(): void
    {
        parent::setUp();
        $this->createRegistrationTables();
        Mail::fake();
        Http::fake();
    }

    public function test_local_registration_form_uses_native_submit_for_file_uploads(): void
    {
        $response = $this->get(route('registerLocal'));

        $response->assertOk();
        $response->assertSee('data-native-submit="true"', false);
    }

    public function test_local_registration_creates_a_local_record(): void
    {
        $response = $this->post(route('local.store'), [
            'rut' => '12345678',
            'cedula' => '12345678',
            'nombre' => 'Mi Local',
            'nombre_dueno' => 'Juan Pérez',
            'cedula_dueno' => '12345678',
            'horario' => '08:00 hrs - 18:00 hrs',
            'direccion' => 'Calle 123',
            'logo' => 'https://example.com/logo.png',
            'numero_cuenta' => '1234567890',
            'correo' => 'local@example.com',
            'contrasena' => 'Password123!',
            'contrasena_confirmation' => 'Password123!',
        ]);

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
        $this->assertTrue(Hash::check(
            'Password123!',
            DB::table('comercio')->where('Email_Usuario', 'local@example.com')->value('Contraseña')
        ));
        $this->assertTrue(Hash::check(
            'Password123!',
            DB::table('usuario')->where('Email', 'local@example.com')->value('Contraseña')
        ));
    }

    public function test_local_registration_accepts_night_hours(): void
    {
        $response = $this->post(route('local.store'), [
            'rut' => '12345679',
            'nombre' => 'Mi Local nocturno',
            'nombre_dueno' => 'Lucía Gómez',
            'cedula_dueno' => '23456789',
            'horario_inicio' => '20:00',
            'horario_fin' => '02:00',
            'direccion' => 'Calle 789',
            'correo' => 'nocturno@example.com',
            'contrasena' => 'Password123!',
            'contrasena_confirmation' => 'Password123!',
        ]);

        $response->assertRedirect(route('registration.verify.form', ['email' => 'nocturno@example.com']));
    }

    public function test_local_registration_accepts_a_logo_file(): void
    {
        Storage::fake('public');

        $response = $this->post(route('local.store'), [
            'rut' => '12345678',
            'cedula' => '12345678',
            'nombre' => 'Mi Local con logo',
            'nombre_dueno' => 'Ana López',
            'cedula_dueno' => '23456789',
            'horario' => '08:00 hrs - 18:00 hrs',
            'direccion' => 'Calle 456',
            'logo' => UploadedFile::fake()->image('logo-local.png', 200, 200),
            'numero_cuenta' => '1234567890',
            'correo' => 'local-file@example.com',
            'contrasena' => 'Password123!',
            'contrasena_confirmation' => 'Password123!',
        ]);

        $response->assertRedirect(route('registration.verify.form', ['email' => 'local-file@example.com']));

        $code = null;
        Mail::assertSent(RegistrationVerificationCode::class, function (RegistrationVerificationCode $mail) use (&$code): bool {
            $code = $mail->code;

            return true;
        });

        $this->post(route('registration.verify'), [
            'email' => 'local-file@example.com',
            'code' => $code,
        ])->assertRedirect(route('login'));

        $logoUrl = DB::table('comercio')->where('Email_Usuario', 'local-file@example.com')->value('Logo');
        $this->assertNotNull($logoUrl);
        $this->assertStringContainsString('/storage/comercios/logos/', $logoUrl);
    }

    public function test_product_form_uses_native_submit_for_file_uploads(): void
    {
        $response = $this->withSession([
            'tipo_usuario' => 'comercio',
            'usuario_id' => 'sinrut@example.com',
            'email' => 'sinrut@example.com',
        ])->get(route('dashboard.local'));

        $response->assertOk();
        $response->assertSee('data-native-submit="true"', false);
    }

    public function test_commerce_can_login_without_rut(): void
    {
        DB::table('comercio')->insert([
            'Email_Usuario' => 'sinrut@example.com',
            'Nombre_Comercio' => 'Local sin RUT',
            'RUT' => null,
            'Contraseña' => Hash::make('Password123!'),
            'Estado_Adhesion' => 'Aceptada',
            'Estado_Cuenta' => 'Activa',
        ]);

        $response = $this->from(route('login'))->post(route('login.post'), [
            'email' => 'sinrut@example.com',
            'password' => 'Password123!',
        ]);

        $response->assertRedirect(route('dashboard.local'));
        $this->assertEquals('comercio', session('tipo_usuario'));
        $this->assertEquals('sinrut@example.com', session('usuario_id'));
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
            'contrasena' => 'Password123!',
            'contrasena_confirmation' => 'Password123!',
        ]);

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
        $this->assertTrue(Hash::check(
            'Password123!',
            DB::table('repartidor')->where('Email_Usuario', 'repartidor@example.com')->value('Contraseña')
        ));
        $this->assertTrue(Hash::check(
            'Password123!',
            DB::table('usuario')->where('Email', 'repartidor@example.com')->value('Contraseña')
        ));
    }
}
