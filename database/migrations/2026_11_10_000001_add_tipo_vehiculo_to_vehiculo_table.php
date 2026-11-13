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
        $tableName = Schema::hasTable('vehículo') ? 'vehículo' : (Schema::hasTable('Vehículo') ? 'Vehículo' : null);

        if ($tableName === null) {
            return;
        }

        Schema::table($tableName, function (Blueprint $table) use ($tableName) {
            if (! Schema::hasColumn($tableName, 'Tipo_Vehiculo')) {
                $table->string('Tipo_Vehiculo', 30)->nullable()->after('CI_Repartidor');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $tableName = Schema::hasTable('vehículo') ? 'vehículo' : (Schema::hasTable('Vehículo') ? 'Vehículo' : null);

        if ($tableName === null) {
            return;
        }

        Schema::table($tableName, function (Blueprint $table) use ($tableName) {
            if (Schema::hasColumn($tableName, 'Tipo_Vehiculo')) {
                $table->dropColumn('Tipo_Vehiculo');
            }
        });
    }
};
