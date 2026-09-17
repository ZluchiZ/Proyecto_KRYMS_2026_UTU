<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('cliente') && ! Schema::hasColumn('cliente', 'Direccion_Entrega')) {
            Schema::table('cliente', function (Blueprint $table): void {
                $table->string('Direccion_Entrega', 500)->nullable();
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('cliente') && Schema::hasColumn('cliente', 'Direccion_Entrega')) {
            Schema::table('cliente', function (Blueprint $table): void {
                $table->dropColumn('Direccion_Entrega');
            });
        }
    }
};
