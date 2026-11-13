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
        if (! Schema::hasTable('repartidor')) {
            return;
        }

        Schema::table('repartidor', function (Blueprint $table) {
            if (! Schema::hasColumn('repartidor', 'Cedula_Archivo')) {
                $table->string('Cedula_Archivo', 255)->nullable()->after('Foto_Libreta');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('repartidor', function (Blueprint $table) {
            if (Schema::hasColumn('repartidor', 'Cedula_Archivo')) {
                $table->dropColumn('Cedula_Archivo');
            }
        });
    }
};
