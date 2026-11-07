<?php

namespace Tests\Concerns;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

trait CreatesRegistrationTables
{
    protected function createRegistrationTables(): void
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

        Schema::connection('sqlite')->create('usuario', function (Blueprint $table): void {
            $table->string('Email')->primary();
            $table->string('Nombre_de_Usuario');
            $table->string('Contraseña');
            $table->string('Tipo_Usuario');
        });

        Schema::connection('sqlite')->create('cliente', function (Blueprint $table): void {
            $table->string('CI')->primary();
            $table->string('Email_Usuario')->unique();
            $table->string('Contraseña');
            $table->date('Fecha_nacimiento');
            $table->string('Nombre');
            $table->string('Apellido');
            $table->string('Teléfono');
        });

        Schema::connection('sqlite')->create('comercio', function (Blueprint $table): void {
            $table->increments('id');
            $table->string('Email_Usuario')->unique();
            $table->string('Contraseña');
            $table->string('RUT')->nullable()->unique();
            $table->string('Nombre_Comercio');
            $table->string('Nombre_dueño');
            $table->string('CI_Dueño');
            $table->string('Horario');
            $table->string('Dirección')->nullable();
            $table->string('Logo')->nullable();
            $table->string('Estado_Adhesion')->nullable();
            $table->string('Motivo_Rechazo')->nullable();
            $table->decimal('latitud', 10, 7)->nullable();
            $table->decimal('longitud', 10, 7)->nullable();
        });

        Schema::connection('sqlite')->create('repartidor', function (Blueprint $table): void {
            $table->string('CI')->primary();
            $table->string('Email_Usuario')->unique();
            $table->string('Contraseña');
            $table->string('Nombre');
            $table->string('Apellido');
            $table->string('Teléfono');
            $table->string('Estado_Adhesion')->nullable();
            $table->string('Motivo_Rechazo')->nullable();
        });

        Schema::connection('sqlite')->create('registration_verifications', function (Blueprint $table): void {
            $table->string('email')->primary();
            $table->string('type');
            $table->text('payload');
            $table->string('code_hash');
            $table->unsignedTinyInteger('attempts')->default(0);
            $table->timestamp('expires_at');
            $table->timestamps();
        });
    }
}
