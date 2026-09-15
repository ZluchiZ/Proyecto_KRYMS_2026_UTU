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
                $table->unsignedBigInteger('ID_Cliente');
                $table->unsignedBigInteger('ID_Producto');
                $table->unsignedInteger('Cantidad');
                $table->timestamps();
                $table->unique(['ID_Cliente', 'ID_Producto']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('Carrito');
    }
};
