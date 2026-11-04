<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        foreach (['comercio', 'repartidor'] as $tableName) {
            if (! Schema::hasTable($tableName)) {
                continue;
            }

            Schema::table($tableName, function (Blueprint $table) use ($tableName): void {
                if (! Schema::hasColumn($tableName, 'Estado_Adhesion')) {
                    $table->string('Estado_Adhesion', 20)->nullable();
                }
                if (! Schema::hasColumn($tableName, 'Motivo_Rechazo')) {
                    $table->text('Motivo_Rechazo')->nullable();
                }
            });

            DB::table($tableName)
                ->whereNull('Estado_Adhesion')
                ->update(['Estado_Adhesion' => 'Aceptada']);
        }
    }

    public function down(): void
    {
        foreach (['comercio', 'repartidor'] as $tableName) {
            if (! Schema::hasTable($tableName)) {
                continue;
            }

            Schema::table($tableName, function (Blueprint $table) use ($tableName): void {
                $columns = [];
                if (Schema::hasColumn($tableName, 'Estado_Adhesion')) {
                    $columns[] = 'Estado_Adhesion';
                }
                if (Schema::hasColumn($tableName, 'Motivo_Rechazo')) {
                    $columns[] = 'Motivo_Rechazo';
                }
                if ($columns !== []) {
                    $table->dropColumn($columns);
                }
            });
        }
    }
};