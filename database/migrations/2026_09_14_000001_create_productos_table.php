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
                $table->string('RUT_Comercio');
                $table->string('Nombre_Producto');
                $table->string('Categoria');
                $table->decimal('Precio', 10, 2);
                $table->decimal('Descuento_Porcentaje', 5, 2)->default(0);
                $table->string('Foto_Producto');
                $table->text('Descripcion');
                $table->boolean('Disponible')->default(true);
            });

            return;
        }

        Schema::table('Producto', function (Blueprint $table) {
            if (! Schema::hasColumn('Producto', 'Descripcion')) {
                $table->text('Descripcion')->nullable();
            }
            if (! Schema::hasColumn('Producto', 'Disponible')) {
                $table->boolean('Disponible')->default(true);
            }
        });
    }

    public function down(): void
    {
        if (Schema::hasTable('Producto')) {
            Schema::table('Producto', function (Blueprint $table) {
                if (Schema::hasColumn('Producto', 'Descripcion')) {
                    $table->dropColumn('Descripcion');
                }
                if (Schema::hasColumn('Producto', 'Disponible')) {
                    $table->dropColumn('Disponible');
                }
            });
        }
    }
};