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
        Schema::create('Repartidor', function (Blueprint $table) {
            $table->string('CI', 20)->primary();
            $table->string('Email_Usuario', 255);
            $table->string('Contraseña', 255);
            $table->string('Nombre', 100)->nullable();
            $table->string('Apellido', 100)->nullable();
            $table->string('Teléfono', 30)->nullable();
            $table->string('Estado_Repartidor', 50)->nullable();
            $table->string('Foto_Libreta', 255)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('Repartidor');
    }
};
