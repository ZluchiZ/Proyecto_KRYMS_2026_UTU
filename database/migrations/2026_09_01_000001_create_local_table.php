<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (! Schema::hasTable('Comercio')) {
            Schema::create('Comercio', function (Blueprint $table) {
                $table->string('Email_Usuario', 255)->primary();
                $table->string('Contraseña', 255);
                $table->string('RUT', 20)->nullable()->unique();
                $table->string('Nombre_Comercio', 150)->nullable();
                $table->string('Teléfono', 30)->nullable();
                $table->string('Dirección', 255)->nullable();
                $table->string('Logo', 255)->nullable();
                $table->string('Nombre_dueño', 100)->nullable();
                $table->string('CI_Dueño', 20)->nullable();
                $table->string('Horario', 255)->nullable();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('Comercio');
    }
};
