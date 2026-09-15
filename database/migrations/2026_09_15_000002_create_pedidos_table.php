<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('Pedido')) {
            Schema::create('Pedido', function (Blueprint $table) {
                $table->id('ID_Pedido');
                $table->unsignedBigInteger('ID_Cliente');
                $table->unsignedBigInteger('ID_Producto');
                $table->unsignedInteger('Cantidad');
                $table->decimal('Total', 10, 2);
                $table->string('Direccion_Envio', 500);
                $table->string('Telefono_Contacto', 30)->nullable();
                $table->text('Referencias')->nullable();
                $table->string('Metodo_Pago', 30);
                $table->string('Estado', 30)->default('pendiente');
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('Pedido');
    }
};
