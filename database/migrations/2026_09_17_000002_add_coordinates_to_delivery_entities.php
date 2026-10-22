<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        foreach (['comercio', 'cliente', 'pedido'] as $tableName) {
            if (! Schema::hasTable($tableName)) {
                continue;
            }

            Schema::table($tableName, function (Blueprint $table) use ($tableName): void {
                if (! Schema::hasColumn($tableName, 'latitud')) {
                    $table->decimal('latitud', 10, 7)->nullable();
                }
                if (! Schema::hasColumn($tableName, 'longitud')) {
                    $table->decimal('longitud', 10, 7)->nullable();
                }
                if ($tableName === 'pedido' && ! Schema::hasColumn($tableName, 'Distancia_Local_Km')) {
                    $table->decimal('Distancia_Local_Km', 10, 2)->nullable();
                }
            });
        }
    }

    public function down(): void
    {
        foreach (['comercio', 'cliente', 'pedido'] as $tableName) {
            if (! Schema::hasTable($tableName)) {
                continue;
            }

            Schema::table($tableName, function (Blueprint $table) use ($tableName): void {
                if (Schema::hasColumn($tableName, 'latitud')) {
                    $table->dropColumn('latitud');
                }
                if (Schema::hasColumn($tableName, 'longitud')) {
                    $table->dropColumn('longitud');
                }
                if ($tableName === 'pedido' && Schema::hasColumn($tableName, 'Distancia_Local_Km')) {
                    $table->dropColumn('Distancia_Local_Km');
                }
            });
        }
    }
};
