<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('Carrito')) {
            Schema::create('Carrito', function (Blueprint $table) {
                $table->id('ID_Carrito');
                $table->string('CI_Cliente', 20);
                $table->unsignedBigInteger('ID_Producto');
                $table->unsignedInteger('Cantidad');
                $table->timestamps();
                $table->unique(['CI_Cliente', 'ID_Producto']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('Carrito');
    }
};
