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
            if (Schema::hasColumn($tableName, 'Fecha_Revision')) {
                $table->dropColumn('Fecha_Revision');
            }

            if (Schema::hasColumn($tableName, 'Motivo_Rechazo')) {
                $table->dropColumn('Motivo_Rechazo');
            }

            if (Schema::hasColumn($tableName, 'Estado_Aprobacion')) {
                $table->dropColumn('Estado_Aprobacion');
            }
        });
    }
};
