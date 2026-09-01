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
            $table->string('ci')->primary();
            $table->string('mail')->unique();
            $table->string('nombre');
            $table->string('apellido');
            $table->string('telefono')->nullable();
            $table->date('fecha_nacimiento');
            $table->string('contrasena');
            $table->string('repetir_contrasena');
            $table->text('opciones')->nullable();
        });

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
            DB::connection('sqlite')->table('cliente')->where('mail', 'ana@example.com')->exists()
        );
    }
}
