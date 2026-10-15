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
                $table->id('N_Pedido');
                $table->string('CI_Cliente', 20);
                $table->string('CI_Repartidor', 20)->nullable();
                $table->unsignedInteger('ID_Tarjeta');
                $table->date('Fecha')->nullable();
                $table->time('Hora')->nullable();
                $table->string('Estado', 50)->nullable();
                $table->string('Ubicacion', 255)->nullable();
                $table->decimal('Costo_de_envio', 10, 2)->nullable();
                $table->decimal('Monto_Total', 10, 2)->nullable();
                $table->boolean('Confirmacion_entrega')->nullable();
                $table->integer('Tiempo_estimado')->nullable();
                $table->string('Metodo_de_pago', 50)->nullable();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('Pedido');
    }
};
