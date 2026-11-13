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

            if (! Schema::hasColumn($tableName, 'Estado_Aprobacion')) {
                $table->string('Estado_Aprobacion', 30)->nullable()->default('Pendiente')->after('Foto');
            }

            if (! Schema::hasColumn($tableName, 'Motivo_Rechazo')) {
                $table->text('Motivo_Rechazo')->nullable()->after('Estado_Aprobacion');
            }

            if (! Schema::hasColumn($tableName, 'Fecha_Revision')) {
                $table->timestamp('Fecha_Revision')->nullable()->after('Motivo_Rechazo');
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
            foreach (['Fecha_Revision', 'Motivo_Rechazo', 'Estado_Aprobacion', 'Tipo_Vehiculo'] as $column) {
                if (Schema::hasColumn($tableName, $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
