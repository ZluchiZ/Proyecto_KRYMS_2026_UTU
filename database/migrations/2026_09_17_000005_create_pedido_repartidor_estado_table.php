<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('pedido_repartidor_estado')) {
            Schema::create('pedido_repartidor_estado', function (Blueprint $table) {
                $table->unsignedBigInteger('N_Pedido');
                $table->string('CI_Repartidor', 20);
                $table->string('Estado', 20);
                $table->primary(['N_Pedido', 'CI_Repartidor']);
                $table->index('CI_Repartidor');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('pedido_repartidor_estado');
    }
};
