<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        foreach (['cliente', 'comercio', 'repartidor'] as $tableName) {
            if (Schema::hasTable($tableName) && ! Schema::hasColumn($tableName, 'Estado_Cuenta')) {
                Schema::table($tableName, function (Blueprint $table): void {
                    $table->string('Estado_Cuenta', 20)->default('Activa');
                });
            }
        }

        if (Schema::hasTable('reporte')) {
            Schema::table('reporte', function (Blueprint $table): void {
                if (! Schema::hasColumn('reporte', 'Estado_Gestion')) {
                    $table->string('Estado_Gestion', 30)->default('Pendiente');
                }
                if (! Schema::hasColumn('reporte', 'Resolucion')) {
                    $table->text('Resolucion')->nullable();
                }
            });
        }

        if (Schema::hasTable('reseña')) {
            Schema::table('reseña', function (Blueprint $table): void {
                if (! Schema::hasColumn('reseña', 'Estado_Moderacion')) {
                    $table->string('Estado_Moderacion', 20)->default('Visible');
                }
                if (! Schema::hasColumn('reseña', 'Motivo_Retiro')) {
                    $table->text('Motivo_Retiro')->nullable();
                }
            });
        }

        if (Schema::hasTable('reporte') && Schema::hasColumn('reporte', 'Estado_Gestion')) {
            DB::table('reporte')->whereNull('Estado_Gestion')->update(['Estado_Gestion' => 'Pendiente']);
        }
        if (Schema::hasTable('reseña') && Schema::hasColumn('reseña', 'Estado_Moderacion')) {
            DB::table('reseña')->whereNull('Estado_Moderacion')->update(['Estado_Moderacion' => 'Visible']);
        }
    }

    public function down(): void
    {
        foreach ([
            'cliente' => ['Estado_Cuenta'],
            'comercio' => ['Estado_Cuenta'],
            'repartidor' => ['Estado_Cuenta'],
            'reporte' => ['Estado_Gestion', 'Resolucion'],
            'reseña' => ['Estado_Moderacion', 'Motivo_Retiro'],
        ] as $tableName => $columns) {
            if (! Schema::hasTable($tableName)) {
                continue;
            }

            $existingColumns = array_values(array_filter(
                $columns,
                fn (string $column): bool => Schema::hasColumn($tableName, $column)
            ));
            if ($existingColumns !== []) {
                Schema::table($tableName, function (Blueprint $table) use ($existingColumns): void {
                    $table->dropColumn($existingColumns);
                });
            }
        }
    }
};