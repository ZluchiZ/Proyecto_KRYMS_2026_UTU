<?php

namespace Tests\Feature;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class ClienteRegistrationTest extends TestCase
{
    public function test_registration_creates_a_cliente_record(): void
    {
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
            $table->string('cedula');
            $table->string('nombre');
            $table->string('apellido');
            $table->string('telefono');
            $table->date('fecha_nacimiento');
            $table->string('correo')->unique();
            $table->string('contrasena');
            $table->timestamps();
        });

        foreach (['comercio', 'repartidor'] as $tableName) {
            Schema::connection('sqlite')->create($tableName, function (Blueprint $table) {
                $table->id();
                $table->string('correo')->unique();
            });
        }

        $response = $this->post(route('cliente.store'), [
            'cedula' => '12345678',
            'nombre' => 'Ana',
            'apellido' => 'Pérez',
            'email' => 'ana@example.com',
            'Numero' => '099123456',
            'password' => 'password123',
            'password2' => 'password123',
            'nacimiento' => '1990-01-02',
            'opciones' => 'opcion1',
        ]);

        $response->assertRedirect(route('login'));

        $this->assertTrue(
            DB::connection('sqlite')->table('cliente')->where('correo', 'ana@example.com')->exists()
        );
    }
}
