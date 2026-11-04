<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('reporte')) {
            return;
        }

        Schema::table('reporte', function (Blueprint $table): void {
            if (! Schema::hasColumn('reporte', 'Categoria')) {
                $table->string('Categoria', 40)->default('local');
            }

            if (Schema::hasColumn('reporte', 'RUT_Comercio')) {
                $table->string('RUT_Comercio', 20)->nullable()->change();
            }
        });
    }

    public function down(): void
    {
        if (Schema::hasTable('reporte') && Schema::hasColumn('reporte', 'Categoria')) {
            Schema::table('reporte', function (Blueprint $table): void {
                $table->dropColumn('Categoria');
            });
        }
    }
};