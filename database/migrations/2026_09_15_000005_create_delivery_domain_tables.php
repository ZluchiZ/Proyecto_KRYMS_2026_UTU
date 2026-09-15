<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('Tarjeta', function (Blueprint $table) {
            $table->id('ID');
            $table->string('CI_Cliente', 20);
            $table->string('Banco', 100)->nullable();
            $table->index('CI_Cliente');
        });

        Schema::create('Subpedido', function (Blueprint $table) {
            $table->id('N_Subpedido');
            $table->unsignedBigInteger('N_Pedido');
            $table->string('RUT_Comercio', 20);
            $table->date('Fecha')->nullable();
            $table->time('Hora')->nullable();
            $table->integer('Tiempo_estimado')->nullable();
            $table->decimal('Monto_Total', 10, 2)->nullable();
            $table->string('Estado', 50)->nullable();
            $table->index(['N_Pedido', 'RUT_Comercio']);
        });

        Schema::create('Detalle_de_pedido', function (Blueprint $table) {
            $table->unsignedBigInteger('N_Subpedido');
            $table->unsignedBigInteger('ID_Producto');
            $table->integer('Cantidad');
            $table->primary(['N_Subpedido', 'ID_Producto']);
            $table->index('ID_Producto');
        });

        Schema::create('Reporte', function (Blueprint $table) {
            $table->id('ID_Reporte');
            $table->string('CI_Cliente', 20);
            $table->string('RUT_Comercio', 20);
            $table->text('Comentario')->nullable();
            $table->index(['CI_Cliente', 'RUT_Comercio']);
        });

        Schema::create('Reseña', function (Blueprint $table) {
            $table->id('ID');
            $table->string('CI_Cliente', 20);
            $table->string('RUT_Comercio', 20)->nullable();
            $table->string('CI_Repartidor', 20)->nullable();
            $table->integer('Puntuacion')->nullable();
            $table->text('Comentario')->nullable();
            $table->index(['CI_Cliente', 'RUT_Comercio', 'CI_Repartidor']);
        });

        Schema::create('Vehículo', function (Blueprint $table) {
            $table->string('Matricula', 20)->primary();
            $table->string('CI_Repartidor', 20)->unique();
            $table->string('Modelo', 100)->nullable();
            $table->string('Color', 50)->nullable();
            $table->string('Libreta_de_propiedad', 255)->nullable();
            $table->string('Seguro', 255)->nullable();
            $table->string('Foto', 255)->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('Vehículo');
        Schema::dropIfExists('Reseña');
        Schema::dropIfExists('Reporte');
        Schema::dropIfExists('Detalle_de_pedido');
        Schema::dropIfExists('Subpedido');
        Schema::dropIfExists('Tarjeta');
    }
};
