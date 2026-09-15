<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('Producto')) {
            Schema::create('Producto', function (Blueprint $table) {
                $table->id('ID_Producto');
                $table->string('RUT_Comercio', 20);
                $table->string('Nombre_Producto', 150)->nullable();
                $table->string('Categoria', 100)->nullable();
                $table->decimal('Precio', 10, 2)->nullable();
                $table->decimal('Descuento_Porcentaje', 5, 2)->nullable();
                $table->string('Foto_Producto', 255)->nullable();
                $table->boolean('Disponible')->default(true);
            });

            return;
        }

        Schema::table('Producto', function (Blueprint $table) {
            if (! Schema::hasColumn('Producto', 'Disponible')) {
                $table->boolean('Disponible')->default(true);
            }
        });
    }

    public function down(): void
    {
        if (Schema::hasTable('Producto')) {
            Schema::table('Producto', function (Blueprint $table) {
                if (Schema::hasColumn('Producto', 'Disponible')) {
                    $table->dropColumn('Disponible');
                }
            });
        }
    }
};