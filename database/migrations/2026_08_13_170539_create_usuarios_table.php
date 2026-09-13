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
    Schema::create('cliente', function (Blueprint $table) {
        $table->id();
        $table->string('cedula');
        $table->string('nombre');
        $table->string('apellido');
        $table->string('telefono');
        $table->date('fecha_nacimiento');
        $table->string('correo')->unique();
        $table->string('contrasena');
        $table->rememberToken();
        $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cliente');
    }
};
