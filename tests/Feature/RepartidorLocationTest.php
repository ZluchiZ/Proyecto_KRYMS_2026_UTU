<?php

namespace Tests\Feature;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class RepartidorLocationTest extends TestCase
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

        Schema::connection('sqlite')->create('repartidor', function (Blueprint $table): void {
            $table->string('CI')->primary();
            $table->string('Email_Usuario')->unique();
            $table->decimal('latitud', 10, 7)->nullable();
            $table->decimal('longitud', 10, 7)->nullable();
        });

        DB::table('repartidor')->insert([
            'CI' => '12345678',
            'Email_Usuario' => 'repartidor@example.com',
        ]);
    }

    public function test_authenticated_repartidor_can_update_own_location(): void
    {
        $response = $this->withSession([
            'tipo_usuario' => 'repartidor',
            'usuario_id' => '12345678',
        ])->patchJson(route('repartidor.ubicacion'), [
            'latitud' => -34.9011,
            'longitud' => -56.1645,
        ]);

        $response->assertOk()
            ->assertJsonPath('ok', true)
            ->assertJsonPath('latitud', -34.9011)
            ->assertJsonPath('longitud', -56.1645);

        $this->assertDatabaseHas('repartidor', [
            'CI' => '12345678',
            'latitud' => -34.9011,
            'longitud' => -56.1645,
        ]);
    }

    public function test_location_update_requires_a_repartidor_session(): void
    {
        $this->patchJson(route('repartidor.ubicacion'), [
            'latitud' => -34.9011,
            'longitud' => -56.1645,
        ])->assertForbidden();
    }

    public function test_location_coordinates_must_be_in_range(): void
    {
        $this->withSession([
            'tipo_usuario' => 'repartidor',
            'usuario_id' => '12345678',
        ])->patchJson(route('repartidor.ubicacion'), [
            'latitud' => 91,
            'longitud' => -56.1645,
        ])->assertUnprocessable();
    }
}