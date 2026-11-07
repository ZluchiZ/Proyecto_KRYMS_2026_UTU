<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Mail;
use Tests\Concerns\CreatesRegistrationTables;
use Tests\TestCase;

class RegistrationRequirementsTest extends TestCase
{
    use CreatesRegistrationTables;

    protected function setUp(): void
    {
        parent::setUp();
        $this->createRegistrationTables();
        Mail::fake();
    }

    public function test_cliente_registration_requires_a_valid_email_strong_password_and_adult_birth_date(): void
    {
        $this->from(route('register'))
            ->post(route('cliente.store'), [
                'cedula' => '12345678',
                'nombre' => 'Ana',
                'apellido' => 'Pérez',
                'email' => 'ana@gmail',
                'Numero' => '099123456',
                'password' => 'password123',
                'password2' => 'password123',
                'nacimiento' => now()->subYears(17)->toDateString(),
            ])
            ->assertRedirect(route('register'))
            ->assertSessionHasErrors(['email', 'password', 'nacimiento']);
    }

    public function test_local_registration_requires_a_valid_email_and_strong_password(): void
    {
        $this->from(route('registerLocal'))
            ->post(route('local.store'), [
                'nombre' => 'Mi Local',
                'nombre_dueno' => 'Juan Pérez',
                'cedula_dueno' => '12345678',
                'horario' => '08:00 hrs - 18:00 hrs',
                'correo' => 'local@gmail',
                'contrasena' => 'password123',
                'contrasena_confirmation' => 'password123',
            ])
            ->assertRedirect(route('registerLocal'))
            ->assertSessionHasErrors(['correo', 'contrasena']);
    }

    public function test_repartidor_registration_requires_a_valid_email_strong_password_and_adult_birth_date(): void
    {
        $this->from(route('registerRepartidor'))
            ->post(route('repartidor.store'), [
                'cedula' => '87654321',
                'correo' => 'repartidor@gmail',
                'nombre' => 'Luis',
                'apellido' => 'Pérez',
                'telefono' => '099123456',
                'fecha_nacimiento' => now()->subYears(17)->toDateString(),
                'contrasena' => 'password123',
                'contrasena_confirmation' => 'password123',
            ])
            ->assertRedirect(route('registerRepartidor'))
            ->assertSessionHasErrors(['correo', 'contrasena', 'fecha_nacimiento']);
    }

    public function test_google_registration_requires_an_adult_birth_date_and_valid_email(): void
    {
        $this->withSession(['google_email' => 'usuario@gmail'])
            ->from(route('google.complete.form'))
            ->post(route('google.complete.register'), [
                'cedula' => '12345678',
                'nombre' => 'Ana',
                'apellido' => 'Pérez',
                'telefono' => '099123456',
                'nacimiento' => now()->subYears(17)->toDateString(),
            ])
            ->assertRedirect(route('google.complete.form'))
            ->assertSessionHasErrors(['email', 'nacimiento']);
    }
}
