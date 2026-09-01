<?php

namespace Tests\Feature;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class LocalAndRepartidorRegistrationTest extends TestCase
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

        Schema::connection('sqlite')->create('local', function (Blueprint $table) {
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
    }

    public function test_local_registration_creates_a_local_record(): void
    {
        $response = $this->post(route('local.store'), [
            'rut' => '12345678',
            'cedula' => '12345678',
            'nombre' => 'Mi Local',
            'direccion' => 'Calle 123',
            'logo' => 'https://example.com/logo.png',
            'numero_cuenta' => '1234567890',
            'correo' => 'local@example.com',
            'contrasena' => 'password123',
            'contrasena_confirmation' => 'password123',
        ]);

        $response->assertRedirect(route('login'));
        $this->assertDatabaseHas('local', ['correo' => 'local@example.com']);
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

        $response->assertRedirect(route('login'));
        $this->assertDatabaseHas('repartidor', ['correo' => 'repartidor@example.com']);
    }
}
