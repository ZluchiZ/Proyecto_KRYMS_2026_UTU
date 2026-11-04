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
    if (! Schema::hasTable('Usuario')) {
        Schema::create('Usuario', function (Blueprint $table) {
            $table->string('Email', 255)->primary();
            $table->string('Nombre_de_Usuario', 100);
            $table->string('Contraseña', 255);
            $table->string('Tipo_Usuario', 50);
        });
    }
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('Usuario');
    }
};
