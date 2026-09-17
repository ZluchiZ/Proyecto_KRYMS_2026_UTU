<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('cliente')) {
            return;
        }

        if (! Schema::hasColumn('cliente', 'direccion')) {
            Schema::table('cliente', function (Blueprint $table): void {
                $table->string('direccion', 500)->nullable();
            });
        }

        if (Schema::hasColumn('cliente', 'Direccion_Entrega')) {
            DB::table('cliente')
                ->whereNull('direccion')
                ->update(['direccion' => DB::raw('`Direccion_Entrega`')]);
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('cliente') && Schema::hasColumn('cliente', 'direccion')) {
            Schema::table('cliente', function (Blueprint $table): void {
                $table->dropColumn('direccion');
            });
        }
    }
};